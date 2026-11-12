<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationalIndexes extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->index('status');
            $table->index(['member_id', 'status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['created_at', 'transaction_type']);
            $table->index('account_number');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->index('member_id');
        });

        Schema::table('guarantor_pledges', function (Blueprint $table) {
            $table->index(['guarantor_member_id', 'status']);
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->index('account_code');
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['member_id', 'status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'transaction_type']);
            $table->dropIndex(['account_number']);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['member_id']);
        });

        Schema::table('guarantor_pledges', function (Blueprint $table) {
            $table->dropIndex(['guarantor_member_id', 'status']);
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex(['account_code']);
        });
    }
}
