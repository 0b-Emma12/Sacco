<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('account_number', 50);
            $table->foreign('account_number')->references('account_number')->on('accounts')->cascadeOnDelete();
            $table->string('transaction_type'); // deposit, withdrawal
            $table->decimal('amount', 15, 2);
            $table->text('narration')->nullable();
            $table->string('performed_by_username')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
