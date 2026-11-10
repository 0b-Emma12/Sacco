<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Top-Level KPIs
        $totalMemberSavings = Member::sum('total_savings');
        $totalShareCapital = Member::sum('share_capital');
        $activeMembers = Member::where('status', 'Active')->count();
        $grossLoanPortfolio = Loan::whereIn('status', ['approved', 'disbursed', 'defaulted'])->sum('principal_amount');
        
        // 2. Asset Breakdown (Calculating Cash based on actual transactions)
        // Cash = Total Deposits + Total Shares - Withdrawals - Loans Disbursed + Loan Repayments
        $totalDeposits = Transaction::where('transaction_type', 'deposit')->sum('amount');
        $totalWithdrawals = Transaction::where('transaction_type', 'withdrawal')->sum('amount');
        $cashAndBank = max(0, $totalDeposits - $totalWithdrawals);
        
        // Since we don't have an investments/fixed assets module yet, these are 0
        $investments = 0;
        $fixedAssets = 0;
        $accruedInterest = Loan::whereIn('status', ['disbursed'])->sum('interest_amount');
        
        $totalAssets = $grossLoanPortfolio + $cashAndBank + $investments + $fixedAssets + $accruedInterest;

        // 3. Liabilities & Equity
        // We only have real member savings and share capital.
        $statutoryReserve = 0;
        $externalBorrowings = 0;
        $accountsPayable = 0;
        // Retained earnings balances the accounting equation (Assets = Liabilities + Equity)
        $retainedEarnings = $totalAssets - ($totalMemberSavings + $totalShareCapital + $statutoryReserve + $externalBorrowings + $accountsPayable); 

        // 4. Income & Revenue (YTD)
        $interestIncome = Transaction::where('transaction_type', 'deposit')->where(function($q) {
            $q->where('narration', 'like', '%interest%')->orWhere('narration', 'like', '%loan repayment%');
        })->sum('amount');
        
        $feesIncome = Transaction::where('transaction_type', 'deposit')->where('narration', 'like', '%Fee%')->sum('amount');
        $investmentIncome = 0;
        $otherIncome = 0;
        $grossIncome = $interestIncome + $feesIncome + $investmentIncome + $otherIncome;

        $interestPaid = Transaction::where('transaction_type', 'withdrawal')->where('narration', 'like', '%dividend%')->sum('amount');
        $operationalExpenses = Transaction::where('transaction_type', 'withdrawal')->where('narration', 'like', '%expense%')->sum('amount');
        
        // Defaulted loans minus recovered amounts
        $loanLossProvision = Loan::where('status', 'defaulted')->sum('principal_amount'); 
        
        $netSurplus = $grossIncome - ($interestPaid + $operationalExpenses + $loanLossProvision);

        // 5. Portfolio Quality & Risk
        // Real PAR > 30 days (defaulted loans percentage)
        $nplAmount = Loan::where('status', 'defaulted')->sum('principal_amount');
        $parRatio = $grossLoanPortfolio > 0 ? ($nplAmount / $grossLoanPortfolio) * 100 : 0;
        $parRatio = round($parRatio, 1);
        
        $provisionCoverage = $nplAmount > 0 ? round(($loanLossProvision / $nplAmount) * 100, 1) : 0;
        
        $liquidityRatio = $totalMemberSavings > 0 ? (($cashAndBank + $investments) / $totalMemberSavings) * 100 : 0;
        
        $coreCapital = $totalShareCapital + $statutoryReserve + $retainedEarnings;
        $capitalAdequacy = $totalAssets > 0 ? ($coreCapital / $totalAssets) * 100 : 0;

        return view('dashboard', compact(
            'totalMemberSavings', 'totalShareCapital', 'activeMembers', 'grossLoanPortfolio',
            'cashAndBank', 'investments', 'fixedAssets', 'accruedInterest', 'totalAssets',
            'statutoryReserve', 'externalBorrowings', 'accountsPayable', 'retainedEarnings',
            'interestIncome', 'feesIncome', 'investmentIncome', 'otherIncome', 'grossIncome',
            'interestPaid', 'operationalExpenses', 'loanLossProvision', 'netSurplus',
            'parRatio', 'nplAmount', 'provisionCoverage', 'liquidityRatio', 'capitalAdequacy'
        ));
    }

    public function exportCsv()
    {
        // 1. Recalculate metrics
        $totalMemberSavings = Member::sum('total_savings');
        $totalShareCapital = Member::sum('share_capital');
        $grossLoanPortfolio = Loan::whereIn('status', ['approved', 'disbursed', 'defaulted'])->sum('principal_amount');
        
        $totalDeposits = Transaction::where('transaction_type', 'deposit')->sum('amount');
        $totalWithdrawals = Transaction::where('transaction_type', 'withdrawal')->sum('amount');
        $cashAndBank = max(0, $totalDeposits - $totalWithdrawals);
        $accruedInterest = Loan::whereIn('status', ['disbursed'])->sum('interest_amount');
        $totalAssets = $grossLoanPortfolio + $cashAndBank + $accruedInterest;

        $retainedEarnings = $totalAssets - ($totalMemberSavings + $totalShareCapital); 

        $interestIncome = Transaction::where('transaction_type', 'deposit')->where(function($q) {
            $q->where('narration', 'like', '%interest%')->orWhere('narration', 'like', '%loan repayment%');
        })->sum('amount');
        $feesIncome = Transaction::where('transaction_type', 'deposit')->where('narration', 'like', '%Fee%')->sum('amount');
        $grossIncome = $interestIncome + $feesIncome;

        $interestPaid = Transaction::where('transaction_type', 'withdrawal')->where('narration', 'like', '%dividend%')->sum('amount');
        $operationalExpenses = Transaction::where('transaction_type', 'withdrawal')->where('narration', 'like', '%expense%')->sum('amount');
        $loanLossProvision = Loan::where('status', 'defaulted')->sum('principal_amount'); 
        $netSurplus = $grossIncome - ($interestPaid + $operationalExpenses + $loanLossProvision);

        // 2. Generate CSV
        $filename = "sacco_financial_data_" . date('Y_m_d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Category', 'Metric', 'Amount (UGX)'];
        $callback = function() use($columns, $totalMemberSavings, $totalShareCapital, $grossLoanPortfolio, $cashAndBank, $accruedInterest, $totalAssets, $retainedEarnings, $interestIncome, $feesIncome, $grossIncome, $interestPaid, $operationalExpenses, $loanLossProvision, $netSurplus) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            fputcsv($file, ['ASSETS', 'Gross Loan Portfolio', $grossLoanPortfolio]);
            fputcsv($file, ['ASSETS', 'Cash & Bank Balances', $cashAndBank]);
            fputcsv($file, ['ASSETS', 'Accrued Interest', $accruedInterest]);
            fputcsv($file, ['ASSETS', 'TOTAL ASSETS', $totalAssets]);
            
            fputcsv($file, ['LIABILITIES & EQUITY', 'Total Member Savings', $totalMemberSavings]);
            fputcsv($file, ['LIABILITIES & EQUITY', 'Total Share Capital', $totalShareCapital]);
            fputcsv($file, ['LIABILITIES & EQUITY', 'Retained Earnings', $retainedEarnings]);
            fputcsv($file, ['LIABILITIES & EQUITY', 'TOTAL LIABILITIES & EQUITY', $totalAssets]);

            fputcsv($file, ['INCOME STATEMENT', 'Interest Income', $interestIncome]);
            fputcsv($file, ['INCOME STATEMENT', 'Fees Income', $feesIncome]);
            fputcsv($file, ['INCOME STATEMENT', 'Gross Total Income', $grossIncome]);
            fputcsv($file, ['INCOME STATEMENT', 'Interest Paid (Dividends)', "-$interestPaid"]);
            fputcsv($file, ['INCOME STATEMENT', 'Operational Expenses', "-$operationalExpenses"]);
            fputcsv($file, ['INCOME STATEMENT', 'Loan Loss Provisioning', "-$loanLossProvision"]);
            fputcsv($file, ['INCOME STATEMENT', 'NET SURPLUS (PROFIT)', $netSurplus]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
