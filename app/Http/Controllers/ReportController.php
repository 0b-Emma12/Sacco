<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request) 
    { 
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $txQuery = \App\Models\Transaction::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $totalDeposits = (clone $txQuery)->where('transaction_type', 'deposit')->sum('amount');
        $totalWithdrawals = (clone $txQuery)->where('transaction_type', 'withdrawal')->sum('amount');
        
        $netCashFlow = $totalDeposits - $totalWithdrawals;

        $loansQuery = \App\Models\Loan::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        $totalLoansDisbursed = (clone $loansQuery)->whereIn('status', ['disbursed', 'approved'])->sum('principal_amount');
        
        $transactions = $txQuery->latest()->paginate(20)->withQueryString();

        return view('reports.index', compact(
            'startDate', 'endDate', 'totalDeposits', 'totalWithdrawals', 
            'netCashFlow', 'totalLoansDisbursed', 'transactions'
        )); 
    }
    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $transactions = \App\Models\Transaction::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest()
            ->get();

        $filename = 'transactions-' . $startDate . '_to_' . $endDate . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'Account Number', 'Type', 'Amount', 'Narration', 'Performed By', 'Date']);
            foreach ($transactions as $transaction) {
                fputcsv($out, [
                    $transaction->reference,
                    $transaction->account_number,
                    $transaction->transaction_type,
                    $transaction->amount,
                    $transaction->narration,
                    $transaction->performed_by_username,
                    optional($transaction->created_at)->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function umra()
    {
        $par = \Illuminate\Support\Facades\DB::table('v_par')->first();
        $liquidity = \Illuminate\Support\Facades\DB::table('v_liquidity')->first();
        $capital = \Illuminate\Support\Facades\DB::table('v_capital_adequacy')->first();
        $ageing = \Illuminate\Support\Facades\DB::table('v_portfolio_ageing')->first();

        $arrears = \Illuminate\Support\Facades\DB::table('v_loan_arrears as a')
            ->join('loans as l', 'l.id', '=', 'a.loan_id')
            ->join('members as m', 'm.id', '=', 'l.member_id')
            ->where('a.overdue_amount', '>', 0)
            ->orderByDesc('a.days_overdue')
            ->select('a.*', 'm.membership_number', 'm.first_name', 'm.last_name', 'l.principal_amount', 'l.status')
            ->get();

        $membersByStatus = \App\Models\Member::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $capitalTotals = \App\Models\MemberCapital::selectRaw('COALESCE(SUM(savings_balance),0) as savings, COALESCE(SUM(share_balance),0) as shares, COALESCE(SUM(guarantee_liability),0) as guarantees, COALESCE(SUM(lien_amount),0) as liens')->first();

        $loanProducts = \App\Models\LoanProduct::withCount(['loans' => function ($q) {
            $q->whereIn('status', ['disbursed', 'partially_paid', 'defaulted']);
        }])->get();

        $distributions = \App\Models\SurplusDistribution::orderByDesc('financial_year')->take(5)->get();

        return view('reports.umra', compact(
            'par', 'liquidity', 'capital', 'ageing', 'arrears',
            'membersByStatus', 'capitalTotals', 'loanProducts', 'distributions'
        ));
    }

    public function umraExport()
    {
        $par = \Illuminate\Support\Facades\DB::table('v_par')->first();
        $liquidity = \Illuminate\Support\Facades\DB::table('v_liquidity')->first();
        $capital = \Illuminate\Support\Facades\DB::table('v_capital_adequacy')->first();
        $ageing = \Illuminate\Support\Facades\DB::table('v_portfolio_ageing')->first();

        $arrears = \Illuminate\Support\Facades\DB::table('v_loan_arrears as a')
            ->join('loans as l', 'l.id', '=', 'a.loan_id')
            ->join('members as m', 'm.id', '=', 'l.member_id')
            ->where('a.overdue_amount', '>', 0)
            ->orderByDesc('a.days_overdue')
            ->select('a.*', 'm.membership_number', 'm.first_name', 'm.last_name', 'l.principal_amount')
            ->get();

        $filename = 'umra-tier4-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($par, $liquidity, $capital, $ageing, $arrears) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['UMRA TIER 4 PRUDENTIAL REPORT', now()->format('Y-m-d H:i')]);
            fputcsv($out, []);
            fputcsv($out, ['Indicator', 'Value']);
            fputcsv($out, ['Gross Loan Portfolio', $par->gross_portfolio ?? 0]);
            fputcsv($out, ['Total Arrears', $par->total_arrears ?? 0]);
            fputcsv($out, ['PAR > 30 Days Amount', $par->par30_amount ?? 0]);
            fputcsv($out, ['PAR > 30 Days (%)', round($par->par30_ratio ?? 0, 2)]);
            fputcsv($out, ['Liquid Assets', $liquidity->liquid_assets ?? 0]);
            fputcsv($out, ['Member Deposits', $liquidity->total_deposits ?? 0]);
            fputcsv($out, ['Liquidity Ratio (%)', round($liquidity->liquidity_ratio ?? 0, 2)]);
            fputcsv($out, ['Core Capital', $capital->core_capital ?? 0]);
            fputcsv($out, ['Risk Weighted Assets', $capital->risk_weighted_assets ?? 0]);
            fputcsv($out, ['Capital Adequacy (%)', round($capital->capital_adequacy_ratio ?? 0, 2)]);

            fputcsv($out, []);
            fputcsv($out, ['Portfolio Ageing', 'Amount']);
            fputcsv($out, ['1-30 days', $ageing->bucket_1_30 ?? 0]);
            fputcsv($out, ['31-90 days', $ageing->bucket_31_90 ?? 0]);
            fputcsv($out, ['91-180 days', $ageing->bucket_91_180 ?? 0]);
            fputcsv($out, ['181-365 days', $ageing->bucket_181_365 ?? 0]);
            fputcsv($out, ['365+ days', $ageing->bucket_365_plus ?? 0]);

            fputcsv($out, []);
            fputcsv($out, ['Member', 'Loan ID', 'Principal', 'Overdue Amount', 'Days Overdue']);
            foreach ($arrears as $row) {
                fputcsv($out, [
                    $row->membership_number.' '.$row->first_name.' '.$row->last_name,
                    $row->loan_id,
                    $row->principal_amount,
                    $row->overdue_amount,
                    $row->days_overdue,
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create() { return view('reports.create'); }
    public function store(Request $request) { return redirect()->route('reports.index')->with('success', 'Created successfully'); }
    public function show($id) { return view('reports.show', compact('id')); }
    public function edit($id) { return view('reports.edit', compact('id')); }
    public function update(Request $request, $id) { return redirect()->route('reports.index')->with('success', 'Updated successfully'); }
    public function destroy($id) { return redirect()->route('reports.index')->with('success', 'Deleted successfully'); }
}
