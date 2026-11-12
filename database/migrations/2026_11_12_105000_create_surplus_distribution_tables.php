<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSurplusDistributionTables extends Migration
{
    public function up()
    {
        Schema::create('member_capital_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('share_balance', 15, 2)->default(0);
            $table->decimal('savings_balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['member_id', 'snapshot_date']);
            $table->index('snapshot_date');
        });

        Schema::create('surplus_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('financial_year');
            $table->decimal('total_loan_interest_income', 15, 2)->default(0);
            $table->decimal('total_savings_interest_expense', 15, 2)->default(0);
            $table->decimal('total_operational_expenses', 15, 2)->default(0);
            $table->decimal('net_surplus', 15, 2)->default(0);
            $table->decimal('statutory_reserve_pct', 5, 2)->default(20);
            $table->decimal('statutory_reserve_amount', 15, 2)->default(0);
            $table->decimal('dividend_on_shares_pct', 5, 2)->default(40);
            $table->decimal('interest_on_savings_pct', 5, 2)->default(40);
            $table->decimal('education_welfare_pct', 5, 2)->default(20);
            $table->decimal('dividend_pool', 15, 2)->default(0);
            $table->decimal('savings_interest_pool', 15, 2)->default(0);
            $table->decimal('education_welfare_amount', 15, 2)->default(0);
            $table->date('agm_date')->nullable();
            $table->boolean('approved_by_board')->default(false);
            $table->string('approved_by_umra_reference')->nullable();
            $table->enum('status', ['DRAFT', 'APPROVED', 'POSTING', 'COMPLETED'])->default('DRAFT');
            $table->timestamps();

            $table->unique('financial_year');
        });

        Schema::create('member_dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('surplus_distributions')->cascadeOnDelete();
            $table->foreignUuid('member_id')->constrained('members');
            $table->decimal('avg_share_balance', 15, 2)->default(0);
            $table->decimal('avg_savings_balance', 15, 2)->default(0);
            $table->decimal('dividend_earned', 15, 2)->default(0);
            $table->decimal('interest_earned', 15, 2)->default(0);
            $table->decimal('total_payout', 15, 2)->default(0);
            $table->enum('payout_method', ['REINVEST', 'CASH', 'MOBILE_MONEY', 'BANK_TRANSFER'])->default('REINVEST');
            $table->enum('payout_status', ['PENDING', 'PAID', 'FAILED'])->default('PENDING');
            $table->date('payout_date')->nullable();
            $table->string('payout_reference')->nullable();
            $table->timestamps();

            $table->unique(['distribution_id', 'member_id']);
        });

        DB::table('chart_of_accounts')->updateOrInsert(
            ['code' => '5200'],
            ['name' => 'Interest on Member Savings', 'type' => 'Expense', 'description' => 'Savings interest paid or accrued to members', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down()
    {
        Schema::dropIfExists('member_dividends');
        Schema::dropIfExists('surplus_distributions');
        Schema::dropIfExists('member_capital_snapshots');
        DB::table('chart_of_accounts')->where('code', '5200')->delete();
    }
}
