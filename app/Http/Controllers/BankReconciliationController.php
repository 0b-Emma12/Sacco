<?php

namespace App\Http\Controllers;

use App\Models\BankReconciliation;
use App\Services\LedgerService;
use Illuminate\Http\Request;

class BankReconciliationController extends Controller
{
    public function index()
    {
        $reconciliations = BankReconciliation::orderByDesc('period_to')->orderByDesc('id')->paginate(12);

        $ledgerCash = LedgerService::accountBalance('1000');

        $counts = [
            'total' => BankReconciliation::count(),
            'draft' => BankReconciliation::where('status', 'DRAFT')->count(),
            'reconciled' => BankReconciliation::where('status', 'RECONCILED')->count(),
        ];

        return view('treasury.bank-reconciliations.index', compact('reconciliations', 'ledgerCash', 'counts'));
    }

    public function create()
    {
        return view('treasury.bank-reconciliations.create', [
            'bookBalance' => LedgerService::accountBalance('1000'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'period_from' => 'required|date|before_or_equal:period_to',
            'period_to' => 'required|date|before_or_equal:today',
            'statement_balance' => 'required|numeric',
            'deposits_in_transit' => 'nullable|numeric|min:0',
            'unpresented_cheques' => 'nullable|numeric|min:0',
            'book_balance' => 'required|numeric',
            'bank_credits_not_recorded' => 'nullable|numeric|min:0',
            'bank_charges_not_recorded' => 'nullable|numeric|min:0',
            'reconciliation_items' => 'nullable|string|max:5000',
        ]);

        $adjustedBank = round((float) $data['statement_balance']
            + (float) ($data['deposits_in_transit'] ?? 0)
            - (float) ($data['unpresented_cheques'] ?? 0), 2);

        $adjustedBook = round((float) $data['book_balance']
            + (float) ($data['bank_credits_not_recorded'] ?? 0)
            - (float) ($data['bank_charges_not_recorded'] ?? 0), 2);

        $reconciliation = BankReconciliation::create([
            'bank_name' => $data['bank_name'],
            'account_number' => $data['account_number'],
            'period_from' => $data['period_from'],
            'period_to' => $data['period_to'],
            'statement_balance' => $data['statement_balance'],
            'deposits_in_transit' => $data['deposits_in_transit'] ?? 0,
            'unpresented_cheques' => $data['unpresented_cheques'] ?? 0,
            'adjusted_bank_balance' => $adjustedBank,
            'book_balance' => $data['book_balance'],
            'bank_credits_not_recorded' => $data['bank_credits_not_recorded'] ?? 0,
            'bank_charges_not_recorded' => $data['bank_charges_not_recorded'] ?? 0,
            'adjusted_book_balance' => $adjustedBook,
            'difference' => round($adjustedBank - $adjustedBook, 2),
            'reconciliation_items' => $data['reconciliation_items'] ?? null,
            'status' => 'DRAFT',
            'prepared_by' => $request->user()->username ?? $request->user()->email,
        ]);

        return redirect()->route('treasury.bank-reconciliations.show', $reconciliation)
            ->with('success', $reconciliation->isBalanced()
                ? 'Reconciliation saved and balanced. Submit for review.'
                : 'Reconciliation saved with a difference of UGX '.number_format(abs((float) $reconciliation->difference), 2).'. Investigate before review.');
    }

    public function show(BankReconciliation $bankReconciliation)
    {
        return view('treasury.bank-reconciliations.show', compact('bankReconciliation'));
    }

    public function review(Request $request, BankReconciliation $bankReconciliation)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only a manager or administrator may review a reconciliation.');
        }
        if ($bankReconciliation->isReconciled()) {
            return redirect()->back()->withErrors(['reconciliation' => 'This reconciliation has already been reviewed.']);
        }
        if (! $bankReconciliation->isBalanced()) {
            return redirect()->back()->withErrors(['reconciliation' => 'The adjusted bank and book balances differ by UGX '.number_format(abs((float) $bankReconciliation->difference), 2).'. Resolve the difference first.']);
        }

        $bankReconciliation->update([
            'status' => 'RECONCILED',
            'reviewed_by' => $user->username ?? $user->email,
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Reconciliation reviewed and locked.');
    }
}
