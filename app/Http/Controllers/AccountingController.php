<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function index()
    {
        // Get paginated journals instead of loading all
        $journals = JournalEntry::with('lineItems')->orderBy('posted_at', 'desc')->paginate(10);
        
        // Generate a mini Trial Balance snapshot from ChartOfAccount balances
        // (Assuming ChartOfAccount doesn't have a built-in balance attribute, we calculate it dynamically)
        $accounts = ChartOfAccount::all();
        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $acc) {
            $debits = JournalEntryLine::where('account_code', $acc->code)->sum('debit');
            $credits = JournalEntryLine::where('account_code', $acc->code)->sum('credit');
            
            $balance = $debits - $credits;
            
            // Adjust balance based on account type (Normal debit vs normal credit balances)
            // Assets & Expenses = Normal Debit
            // Liabilities, Equity, Revenue = Normal Credit
            $isNormalDebit = in_array($acc->type, ['Asset', 'Expense']);
            $finalBalance = $isNormalDebit ? $balance : ($balance * -1);

            if ($debits > 0 || $credits > 0) {
                $trialBalance[] = [
                    'code' => $acc->code,
                    'name' => $acc->name,
                    'type' => $acc->type,
                    'debit_total' => $debits,
                    'credit_total' => $credits,
                    'balance' => $finalBalance
                ];
                $totalDebit += $debits;
                $totalCredit += $credits;
            }
        }

        return view('accounting.index', compact('journals', 'trialBalance', 'totalDebit', 'totalCredit'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::all();
        return view('accounting.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:255|unique:journal_entries',
            'description' => 'required|string',
            'posted_at' => 'required|date',
            'lines' => 'required|array|min:2',
            'lines.*.account_code' => 'required|exists:chart_of_accounts,code',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        // check if balanced
        $totalDebit = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');
        if (abs($totalDebit - $totalCredit) > 0.001) {
            return back()->withErrors(['error' => 'Journal entry must be balanced (Total Debits = Total Credits).'])->withInput();
        }

        DB::transaction(function () use ($request) {
            $journal = JournalEntry::create([
                'reference' => $request->reference,
                'description' => $request->description,
                'posted_at' => $request->posted_at,
                'posted_by_name' => auth()->user() ? (auth()->user()->username ?? auth()->user()->email) : 'System',
            ]);

            foreach ($request->lines as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    $account = ChartOfAccount::where('code', $line['account_code'])->first();
                    $journal->lineItems()->create([
                        'account_code' => $line['account_code'],
                        'account_name' => $account->name,
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                    ]);
                }
            }
        });

        return redirect()->route('accounting.index')->with('success', 'Journal entry created successfully.');
    }

    public function show($id)
    {
        $journal = JournalEntry::with('lineItems')->findOrFail($id);
        return view('accounting.show', compact('journal'));
    }

    public function edit($id)
    {
        return view('accounting.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('accounting.index')->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $journal = JournalEntry::with('lineItems')->findOrFail($id);

        try {
            \App\Services\LedgerService::reverse($journal, 'Reversed by '.((auth()->user()->username ?? 'System')));
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('accounting.index')->with('success', 'Journal entry reversed. The original entry is retained for audit.');
    }
}
