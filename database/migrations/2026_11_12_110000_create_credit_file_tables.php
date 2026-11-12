<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditFileTables extends Migration
{
    public function up()
    {
        Schema::create('loan_appraisals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id')->unique();
            $table->string('appraised_by');
            $table->decimal('monthly_income', 15, 2);
            $table->decimal('monthly_expenses', 15, 2)->default(0);
            $table->decimal('existing_monthly_obligations', 15, 2)->default(0);
            $table->decimal('debt_service_ratio', 5, 2)->default(0);
            $table->decimal('collateral_value', 15, 2)->default(0);
            $table->decimal('guarantor_coverage', 15, 2)->default(0);
            $table->string('risk_rating', 10)->default('MEDIUM');
            $table->string('recommendation', 30);
            $table->decimal('recommended_amount', 15, 2)->nullable();
            $table->unsignedInteger('recommended_tenure_months')->nullable();
            $table->text('conditions')->nullable();
            $table->text('remarks')->nullable();
            $table->dateTime('appraised_at');
            $table->timestamps();

            $table->foreign('loan_id')->references('id')->on('loans')->cascadeOnDelete();
            $table->index('risk_rating');
        });

        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();
            $table->string('member_id');
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->string('asset_type', 50);
            $table->text('description');
            $table->string('registration_number', 100)->unique();
            $table->decimal('estimated_value', 15, 2);
            $table->decimal('forced_sale_value', 15, 2)->nullable();
            $table->string('valuer_name')->nullable();
            $table->date('valuation_date')->nullable();
            $table->string('location')->nullable();
            $table->string('status', 20)->default('REGISTERED');
            $table->string('registered_by');
            $table->dateTime('released_at')->nullable();
            $table->string('released_by')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members');
            $table->foreign('loan_id')->references('id')->on('loans');
            $table->index(['member_id', 'status']);
            $table->index(['loan_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('collaterals');
        Schema::dropIfExists('loan_appraisals');
    }
}
