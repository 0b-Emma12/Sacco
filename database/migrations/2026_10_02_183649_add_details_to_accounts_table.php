<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_default')->default(false);
            $table->integer('term_months')->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->date('maturity_date')->nullable();
        });

        $products = [
            ['name' => 'Share Capital'],
            ['name' => 'Savings / Deposit'],
            ['name' => 'Fixed / Term Deposit']
        ];
        
        foreach ($products as $product) {
            \Illuminate\Support\Facades\DB::table('products')->updateOrInsert(
                ['name' => $product['name']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['is_default', 'term_months', 'interest_rate', 'maturity_date']);
        });
    }
}
