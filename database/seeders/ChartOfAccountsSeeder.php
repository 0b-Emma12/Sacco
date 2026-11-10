<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Cash in Bank', 'type' => 'Asset', 'description' => 'Main SACCO bank account for holding liquidity'],
            ['code' => '1200', 'name' => 'Loans Receivable', 'type' => 'Asset', 'description' => 'Principal amount of loans issued to members and awaiting repayment'],
            ['code' => '1300', 'name' => 'Mobile Money Float', 'type' => 'Asset', 'description' => 'Float used for mobile money disbursements'],
            
            // Liabilities
            ['code' => '2000', 'name' => 'Member Savings', 'type' => 'Liability', 'description' => 'Members regular savings deposits'],
            ['code' => '2100', 'name' => 'Dividend Payables', 'type' => 'Liability', 'description' => 'Dividends declared but not yet paid to members'],
            
            // Equity
            ['code' => '3000', 'name' => 'Share Capital', 'type' => 'Equity', 'description' => 'Non-withdrawable equity shares purchased by members'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'Equity', 'description' => 'Historical surplus retained in the SACCO'],
            
            // Income
            ['code' => '4000', 'name' => 'Interest Income from Loans', 'type' => 'Income', 'description' => 'Interest earned on member loans'],
            ['code' => '4100', 'name' => 'Membership Fees', 'type' => 'Income', 'description' => 'Non-refundable registration fees'],
            ['code' => '4200', 'name' => 'Penalty Income', 'type' => 'Income', 'description' => 'Fines collected from late repayments'],
            
            // Expenses
            ['code' => '5000', 'name' => 'Operational Expenses', 'type' => 'Expense', 'description' => 'Day-to-day running costs (rent, salaries, etc)'],
            ['code' => '5100', 'name' => 'Mobile Money API Fees', 'type' => 'Expense', 'description' => 'Transaction fees levied by telecom providers'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(['code' => $account['code']], $account);
        }
    }
}
