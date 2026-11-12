<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Transaction;
use App\Services\LedgerService;
use App\Services\RepaymentScheduleService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = $this->metrics();

        $recentTransactions = Transaction::latest()->take(6)->get();
        $recentLoans = Loan::with('member')->latest()->take(5)->get();
        $recentMembers = Member::latest()->take(5)->get();
        $totalMembersCount = Member::count();
        $totalLoansCount = Loan::count();
        $totalTransactionsCount = Transaction::count();
        $pendingLoansCount = Loan::whereIn('status', ['submitted', 'under_review'])->count();

        // Monthly performance series (current calendar year)
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyDeposits = array_fill(0, 12, 0.0);
        $monthlyLoans = array_fill(0, 12, 0.0);
        $monthlyRepayments = array_fill(0, 12, 0.0);
        $currentYear = (int) now()->year;

        $yearTransactions = Transaction::whereYear('created_at', $currentYear)->get(['amount', 'transaction_type', 'created_at']);
        foreach ($yearTransactions as $tx) {
            $month = (int) $tx->created_at->format('n') - 1;
            if ($tx->transaction_type === 'deposit') {
                $monthlyDeposits[$month] += (float) $tx->amount;
            }
        }

        $yearLoans = Loan::whereYear('disbursed_at', $currentYear)
            ->whereNotNull('disbursed_at')
            ->get(['principal_amount', 'disbursed_at']);
        foreach ($yearLoans as $loan) {
            $month = (int) $loan->disbursed_at->format('n') - 1;
            $monthlyLoans[$month] += (float) $loan->principal_amount;
        }

        $yearRepayments = \App\Models\LoanRepayment::whereYear('created_at', $currentYear)->get(['amount_paid', 'created_at']);
        foreach ($yearRepayments as $repayment) {
            $month = (int) $repayment->created_at->format('n') - 1;
            $monthlyRepayments[$month] += (float) $repayment->amount_paid;
        }

        $monthlyPerformance = [];
        foreach ($monthNames as $index => $name) {
            $monthlyPerformance[] = [
                'month' => $name,
                'deposits' => round($monthlyDeposits[$index], 2),
                'loans' => round($monthlyLoans[$index], 2),
                'repayments' => round($monthlyRepayments[$index], 2),
            ];
        }

        return view('dashboard', array_merge($metrics, compact(
            'recentTransactions', 'recentLoans', 'recentMembers', 'totalMembersCount',
            'totalLoansCount', 'totalTransactionsCount', 'pendingLoansCount', 'monthlyPerformance'
        )));
    }

    /**
     * All headline figures are derived from the double-entry ledger and the
     * UMRA reporting views; member balances are only used before the ledger
     * has been populated (fresh installations).
     */
    private function metrics(): array
    {
        $hasLedger = JournalEntry::exists();

        $totalMemberSavings = $hasLedger ? LedgerService::accountBalance('2000') : (float) Member::sum('total_savings');
        $totalShareCapital = $hasLedger ? LedgerService::accountBalance('3000') : (float) Member::sum('share_capital');

        $cashAndBank = LedgerService::accountBalance('1000') + LedgerService::accountBalance('1300');
        $investments = 0.0;
        $fixedAssets = 0.0;
        $accountsPayable = LedgerService::accountBalance('2100');

        $portfolioFromLedger = LedgerService::accountBalance('1200');
        $portfolioFromLoans = (float) Loan::whereIn('status', ['disbursed', 'partially_paid', 'defaulted'])
            ->get()
            ->sum(fn ($loan) => RepaymentScheduleService::principalOutstanding($loan));
        $grossLoanPortfolio = $hasLedger && $portfolioFromLedger > 0 ? $portfolioFromLedger : $portfolioFromLoans;

        $accruedInterest = 0.0;
        $totalAssets = $grossLoanPortfolio + $cashAndBank + $investments + $fixedAssets + $accruedInterest;

        $retainedEarnings = $totalAssets - $totalMemberSavings - $totalShareCapital - $accountsPayable;

        $interestIncome = LedgerService::accountBalance('4000');
        $feesIncome = LedgerService::accountBalance('4100');
        $penaltyIncome = LedgerService::accountBalance('4200');
        $investmentIncome = 0.0;
        $otherIncome = $penaltyIncome;
        $grossIncome = $interestIncome + $feesIncome + $investmentIncome + $otherIncome;

        $interestPaid = LedgerService::accountBalance('5200');
        $operationalExpenses = LedgerService::accountBalance('5000') + LedgerService::accountBalance('5100');
        $loanLossProvision = (float) Loan::where('status', 'defaulted')->sum('principal_amount');
        $netSurplus = $grossIncome - ($interestPaid + $operationalExpenses);

        $activeMembers = Member::where('status', 'Active')->count();

        $par = DB::table('v_par')->first();
        $liquidityView = DB::table('v_liquidity')->first();
        $capitalView = DB::table('v_capital_adequacy')->first();

        $nplAmount = (float) Loan::where('status', 'defaulted')->sum('principal_amount');
        $parRatio = $par ? round((float) $par->par30_ratio, 1) : 0.0;
        $provisionCoverage = $nplAmount > 0 ? round(($loanLossProvision / $nplAmount) * 100, 1) : 0.0;
        $liquidityRatio = $liquidityView
            ? round((float) $liquidityView->liquidity_ratio, 1)
            : ($totalMemberSavings > 0 ? round((($cashAndBank + $investments) / $totalMemberSavings) * 100, 1) : 0.0);
        $capitalAdequacy = $capitalView ? round((float) $capitalView->capital_adequacy_ratio, 1) : 0.0;

        return compact(
            'totalMemberSavings', 'totalShareCapital', 'activeMembers', 'grossLoanPortfolio',
            'cashAndBank', 'investments', 'fixedAssets', 'accruedInterest', 'totalAssets',
            'accountsPayable', 'retainedEarnings',
            'interestIncome', 'feesIncome', 'investmentIncome', 'otherIncome', 'grossIncome',
            'interestPaid', 'operationalExpenses', 'loanLossProvision', 'netSurplus',
            'parRatio', 'nplAmount', 'provisionCoverage', 'liquidityRatio', 'capitalAdequacy'
        );
    }

    public function exportCsv()
    {
        $m = $this->metrics();

        $filename = "sacco_financial_data_" . date('Y_m_d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Category', 'Metric', 'Amount (UGX)'];
        $callback = function() use ($columns, $m) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, ['ASSETS', 'Gross Loan Portfolio', $m['grossLoanPortfolio']]);
            fputcsv($file, ['ASSETS', 'Cash & Bank Balances', $m['cashAndBank']]);
            fputcsv($file, ['ASSETS', 'Accrued Interest', $m['accruedInterest']]);
            fputcsv($file, ['ASSETS', 'TOTAL ASSETS', $m['totalAssets']]);

            fputcsv($file, ['LIABILITIES & EQUITY', 'Total Member Savings', $m['totalMemberSavings']]);
            fputcsv($file, ['LIABILITIES & EQUITY', 'Total Share Capital', $m['totalShareCapital']]);
            fputcsv($file, ['LIABILITIES & EQUITY', 'Retained Earnings', $m['retainedEarnings']]);
            fputcsv($file, ['LIABILITIES & EQUITY', 'TOTAL LIABILITIES & EQUITY', $m['totalAssets']]);

            fputcsv($file, ['INCOME STATEMENT', 'Interest Income', $m['interestIncome']]);
            fputcsv($file, ['INCOME STATEMENT', 'Fees Income', $m['feesIncome']]);
            fputcsv($file, ['INCOME STATEMENT', 'Penalty Income', $m['otherIncome']]);
            fputcsv($file, ['INCOME STATEMENT', 'Gross Total Income', $m['grossIncome']]);
            fputcsv($file, ['INCOME STATEMENT', 'Interest Paid (Savings)', "-{$m['interestPaid']}"]);
            fputcsv($file, ['INCOME STATEMENT', 'Operational Expenses', "-{$m['operationalExpenses']}"]);
            fputcsv($file, ['INCOME STATEMENT', 'NET SURPLUS (PROFIT)', $m['netSurplus']]);

            fputcsv($file, ['PRUDENTIAL', 'PAR > 30 Days (%)', $m['parRatio']]);
            fputcsv($file, ['PRUDENTIAL', 'Liquidity Ratio (%)', $m['liquidityRatio']]);
            fputcsv($file, ['PRUDENTIAL', 'Capital Adequacy (%)', $m['capitalAdequacy']]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
