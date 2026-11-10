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
    public function create() { return view('reports.create'); }
    public function store(Request $request) { return redirect()->route('reports.index')->with('success', 'Created successfully'); }
    public function show($id) { return view('reports.show', compact('id')); }
    public function edit($id) { return view('reports.edit', compact('id')); }
    public function update(Request $request, $id) { return redirect()->route('reports.index')->with('success', 'Updated successfully'); }
    public function destroy($id) { return redirect()->route('reports.index')->with('success', 'Deleted successfully'); }
}
