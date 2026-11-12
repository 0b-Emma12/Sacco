<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionAuditFields extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_reversal')->default(false);
            $table->unsignedBigInteger('reversal_of_transaction_id')->nullable();
            $table->string('reversal_reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('approved_by_username')->nullable();
            $table->index('reversal_of_transaction_id');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['reversal_of_transaction_id']);
            $table->dropIndex(['reference_type', 'reference_id']);
            $table->dropColumn([
                'is_reversal', 'reversal_of_transaction_id', 'reversal_reason',
                'reference_type', 'reference_id', 'approved_by_username',
            ]);
        });
    }
}
