<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTreasuryAndMemberServiceTables extends Migration
{
    public function up()
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('statement_balance', 15, 2)->default(0);
            $table->decimal('deposits_in_transit', 15, 2)->default(0);
            $table->decimal('unpresented_cheques', 15, 2)->default(0);
            $table->decimal('adjusted_bank_balance', 15, 2)->default(0);
            $table->decimal('book_balance', 15, 2)->default(0);
            $table->decimal('bank_credits_not_recorded', 15, 2)->default(0);
            $table->decimal('bank_charges_not_recorded', 15, 2)->default(0);
            $table->decimal('adjusted_book_balance', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);
            $table->text('reconciliation_items')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->string('prepared_by');
            $table->string('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['account_number', 'status']);
            $table->index('period_to');
        });

        Schema::create('petty_cash_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 40)->unique();
            $table->date('date');
            $table->string('paid_to', 150);
            $table->decimal('amount', 15, 2);
            $table->string('purpose', 255);
            $table->boolean('receipt_attached')->default(false);
            $table->string('status', 20)->default('PENDING');
            $table->string('requested_by');
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'date']);
        });

        Schema::create('petty_cash_replenishments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('replenished_by');
            $table->timestamps();
        });

        Schema::create('death_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('date_of_death');
            $table->string('death_certificate_number', 100);
            $table->string('claimant_name', 150);
            $table->string('claimant_nin', 50);
            $table->string('claimant_relationship', 60);
            $table->string('claimant_phone', 30);
            $table->json('documents')->nullable();
            $table->decimal('share_balance', 15, 2)->default(0);
            $table->decimal('savings_balance', 15, 2)->default(0);
            $table->decimal('loan_outstanding', 15, 2)->default(0);
            $table->decimal('guarantee_liability', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2)->default(0);
            $table->string('status', 20)->default('PENDING');
            $table->string('requested_by');
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_reference', 80)->nullable();
            $table->date('payment_date')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('status');
        });

        Schema::create('share_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('transferor_member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignUuid('transferee_member_id')->constrained('members')->cascadeOnDelete();
            $table->decimal('shares_count', 15, 4);
            $table->decimal('share_value', 15, 2);
            $table->decimal('consideration', 15, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->string('requested_by');
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->date('transfer_date')->nullable();
            $table->string('certificate_reference', 40)->nullable()->unique();
            $table->timestamps();

            $table->index(['transferor_member_id', 'status']);
            $table->index(['transferee_member_id', 'status']);
        });

        DB::table('chart_of_accounts')->insertOrIgnore([
            [
                'code' => '1400',
                'name' => 'Petty Cash',
                'type' => 'Asset',
                'description' => 'Imprest float held by the petty cashier.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '2300',
                'name' => 'Guarantee Retentions',
                'type' => 'Liability',
                'description' => 'Amounts withheld from deceased members pending release of guarantees.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('share_transfers');
        Schema::dropIfExists('death_claims');
        Schema::dropIfExists('petty_cash_replenishments');
        Schema::dropIfExists('petty_cash_vouchers');
        Schema::dropIfExists('bank_reconciliations');

        foreach (['1400', '2300'] as $code) {
            $used = DB::table('journal_entry_lines')->where('account_code', $code)->exists();
            if (! $used) {
                DB::table('chart_of_accounts')->where('code', $code)->delete();
            }
        }
    }
}
