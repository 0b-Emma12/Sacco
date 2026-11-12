<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDocumentAndWhtColumns extends Migration
{
    public function up()
    {
        Schema::table('member_dividends', function (Blueprint $table) {
            $table->decimal('withholding_tax', 15, 2)->default(0)->after('interest_earned');
            $table->decimal('net_payout', 15, 2)->default(0)->after('total_payout');
            $table->decimal('wht_rate', 5, 2)->default(15)->after('net_payout');
        });

        DB::table('member_dividends')->update(['net_payout' => DB::raw('total_payout')]);

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method', 30)->nullable()->after('narration');
            $table->string('external_reference', 100)->nullable()->after('payment_method');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('processing_fee', 15, 2)->default(0)->after('principal_amount');
            $table->decimal('insurance_fee', 15, 2)->default(0)->after('processing_fee');
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->integer('installment_number')->nullable()->after('loan_id');
            $table->decimal('principal_paid', 15, 2)->default(0)->after('amount_paid');
            $table->decimal('interest_paid', 15, 2)->default(0)->after('principal_paid');
        });

        DB::table('chart_of_accounts')->insertOrIgnore([
            'code' => '2400',
            'name' => 'URA Withholding Tax Payable',
            'type' => 'Liability',
            'description' => '15% withholding tax deducted from member dividends and payable to URA.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $settings = [
            ['key' => 'org_tin', 'value' => '', 'group' => 'organization'],
            ['key' => 'org_umra_reg_no', 'value' => '', 'group' => 'organization'],
            ['key' => 'org_logo', 'value' => '', 'group' => 'organization'],
            ['key' => 'doc_disclaimer', 'value' => 'This is a computer-generated document. Valid only with an official SACCO stamp and authorised signature.', 'group' => 'organization'],
            ['key' => 'sys_wht_rate', 'value' => '15', 'group' => 'system'],
        ];

        foreach ($settings as $setting) {
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
            DB::table('settings')->insertOrIgnore($setting);
        }
    }

    public function down()
    {
        DB::table('settings')->whereIn('key', ['org_tin', 'org_umra_reg_no', 'org_logo', 'doc_disclaimer', 'sys_wht_rate'])->delete();
        DB::table('chart_of_accounts')->where('code', '2400')->delete();

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn(['installment_number', 'principal_paid', 'interest_paid']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['processing_fee', 'insurance_fee']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'external_reference']);
        });

        Schema::table('member_dividends', function (Blueprint $table) {
            $table->dropColumn(['withholding_tax', 'net_payout', 'wht_rate']);
        });
    }
}
