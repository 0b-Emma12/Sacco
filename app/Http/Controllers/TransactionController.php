<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request) 
    {
        $query = Transaction::latest();
        
        if ($search = $request->input('search')) {
            $query->where('reference', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('transaction_type', 'like', "%{$search}%")
                  ->orWhere('narration', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('performed_by_username', 'like', "%{$search}%");
        }
        
        $transactions = $query->paginate(10)->withQueryString();
        return view('transactions.index', compact('transactions')); 
    }

    public function create() 
    { 
        return view('transactions.create'); 
    }

    public function store(Request $request) 
    { 
        $request->validate([
            'transaction_type' => 'required|string|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:1',
            'account_number' => 'required|string|exists:accounts,account_number',
            'narration' => 'nullable|string',
        ]);

        $account = \App\Models\Account::where('account_number', $request->account_number)->firstOrFail();
        $member = $account->member;

        // Block transactions on inactive accounts or members who have exited/closed
        if (!$account->is_active) {
            return redirect()->back()->withErrors(['account_number' => 'This account is inactive. Transactions are not allowed.'])->withInput();
        }
        if ($member && in_array(strtolower($member->status), ['exited', 'closed'])) {
            return redirect()->back()->withErrors(['account_number' => 'This member has ' . strtolower($member->status) . ' the SACCO. Transactions are not allowed.'])->withInput();
        }

        if ($request->transaction_type === 'withdrawal') {
            if ($account->balance < $request->amount) {
                return redirect()->back()->withErrors(['amount' => 'Insufficient account balance.']);
            }
            
            // SACCO Guarantor Freeze Logic:
            // Ensure withdrawal does not cut into frozen pledges
            if ($member->available_savings < $request->amount) {
                $frozenAmount = $member->total_savings - $member->available_savings;
                return redirect()->back()->withErrors(['amount' => 'Insufficient available savings. UGX ' . number_format($frozenAmount, 2) . ' is frozen as loan collateral.']);
            }

            $account->balance -= $request->amount;
            $member->total_savings -= $request->amount;
        } else {
            $account->balance += $request->amount;
            $member->total_savings += $request->amount;
        }

        $account->save();
        $member->save();

        $reference = strtoupper(Str::random(10));

        Transaction::create([
            'reference' => $reference,
            'account_number' => $request->account_number,
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'narration' => $request->narration,
            'performed_by_username' => auth()->user() ? auth()->user()->username : 'Admin',
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaction completed successfully'); 
    }

    public function show($id) 
    { 
        $transaction = Transaction::findOrFail($id);
        return view('transactions.show', compact('transaction')); 
    }

    public function receipt($id) 
    { 
        $transaction = Transaction::findOrFail($id);
        return view('transactions.receipt', compact('transaction')); 
    }

    public function edit($id) 
    { 
        return view('transactions.edit', compact('id')); 
    }

    public function update(Request $request, $id) 
    { 
        return redirect()->route('transactions.index')->with('success', 'Updated successfully'); 
    }

    public function destroy($id) 
    { 
        return redirect()->route('transactions.index')->with('success', 'Deleted successfully'); 
    }
}
