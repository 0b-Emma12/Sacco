<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandingOrdersTables extends Migration
{
    public function up()
    {
        Schema::create('standing_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('member_id')->constrained('members')->cascadeOnDelete();
            $table->string('mandate_reference', 40)->unique();
            $table->string('destination_type', 20)->default('savings'); // savings | loan
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('frequency', 20)->default('MONTHLY'); // WEEKLY | FORTNIGHTLY | MONTHLY | QUARTERLY
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->unsignedInteger('runs_completed')->default(0);
            $table->string('source', 30)->default('SALARY'); // SALARY | BANK_STANDING_ORDER | MOBILE_MONEY | CASH_AT_COUNTER
            $table->string('payer_details', 150)->nullable();
            $table->string('payer_reference', 80)->nullable();
            $table->string('status', 20)->default('PENDING'); // PENDING | ACTIVE | SUSPENDED | CANCELLED | COMPLETED
            $table->string('requested_by');
            $table->string('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->text('member_instructions')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['status', 'next_run_date']);
        });

        Schema::create('standing_order_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standing_order_id')->constrained('standing_orders')->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('POSTED'); // POSTED | FAILED
            $table->string('transaction_reference', 40)->nullable();
            $table->string('journal_reference', 40)->nullable();
            $table->text('note')->nullable();
            $table->dateTime('processed_at');
            $table->timestamps();

            $table->unique(['standing_order_id', 'due_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('standing_order_runs');
        Schema::dropIfExists('standing_orders');
    }
}
