<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('narration')->nullable();
            $table->string('recorded_by')->default('System');
            $table->timestamps();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('amount_paid', 15, 2)->default(0)->after('principal_amount');
            $table->decimal('outstanding_balance', 15, 2)->nullable()->after('amount_paid');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_repayments');
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'outstanding_balance']);
        });
    }
}
