<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLoanProductsAndLoanTerms extends Migration
{
    public function up()
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('min_amount', 15, 2)->default(1000);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('interest_rate_monthly', 5, 2)->default(5);
            $table->enum('interest_method', ['REDUCING_BALANCE', 'FLAT'])->default('REDUCING_BALANCE');
            $table->decimal('multiplier_factor', 5, 2)->default(3);
            $table->integer('max_tenure_months')->default(12);
            $table->decimal('processing_fee_pct', 5, 2)->default(0);
            $table->decimal('insurance_fee_pct', 5, 2)->default(0);
            $table->enum('collateral_required', ['SELF_SECURED', 'GUARANTORS', 'PHYSICAL', 'MIXED'])->default('GUARANTORS');
            $table->decimal('min_shares_required', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $productId = DB::table('loan_products')->insertGetId([
            'name' => 'Standard Loan',
            'min_amount' => 1000,
            'max_amount' => null,
            'interest_rate_monthly' => 5,
            'interest_method' => 'REDUCING_BALANCE',
            'multiplier_factor' => 3,
            'max_tenure_months' => 12,
            'processing_fee_pct' => 0,
            'insurance_fee_pct' => 0,
            'collateral_required' => 'GUARANTORS',
            'min_shares_required' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('loans', function (Blueprint $table) use ($productId) {
            $table->foreignId('product_id')->nullable()->constrained('loan_products')->nullOnDelete();
            $table->enum('interest_method', ['REDUCING_BALANCE', 'FLAT'])->default('REDUCING_BALANCE');
            $table->decimal('total_repayable', 15, 2)->nullable();
            $table->decimal('monthly_installment', 15, 2)->nullable();
            $table->decimal('outstanding_principal', 15, 2)->nullable();
            $table->date('disbursed_at')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('applied_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('disbursed_by')->nullable();
            $table->decimal('lien_amount_placed', 15, 2)->default(0);
        });

        DB::table('loans')->update(['product_id' => $productId]);
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn([
                'interest_method', 'total_repayable', 'monthly_installment', 'outstanding_principal',
                'disbursed_at', 'maturity_date', 'applied_by', 'approved_by', 'disbursed_by', 'lien_amount_placed',
            ]);
        });

        Schema::dropIfExists('loan_products');
    }
}
