<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index() {
        $loans = \App\Models\Loan::with('member')->orderBy('created_at', 'desc')->paginate(15);
        return view('loans.index', compact('loans'));
    }
    public function create() { 
        $members = \App\Models\Member::where('status', 'Active')->get();
        return view('loans.create', compact('members')); 
    }

    public function store(Request $request) { 
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'principal_amount' => 'required|numeric|min:1000',
            'interest_rate' => 'required|numeric|min:1',
            'term_months' => 'required|integer|min:1',
            'guarantor_1_id' => 'required|exists:members,id|different:member_id',
            'guarantor_1_amount' => 'required|numeric|min:1',
        ]);

        $borrower = \App\Models\Member::findOrFail($validated['member_id']);
        
        // Rule 1: Max Loan Multiplier (3x available savings)
        $maxLoanAmount = $borrower->available_savings * 3;
        if ($validated['principal_amount'] > $maxLoanAmount) {
            return redirect()->back()
                ->withErrors(['principal_amount' => 'Requested amount exceeds the maximum multiplier (3x available savings). Max allowed: UGX ' . number_format($maxLoanAmount)])
                ->withInput();
        }

        // Rule 2 & 3: Savings Ratio / Application Fee (UGX 5,000)
        if ($borrower->available_savings < 5000) {
            return redirect()->back()
                ->withErrors(['member_id' => 'Member has insufficient savings to pay the UGX 5,000 loan application fee.'])
                ->withInput();
        }

        // Deduct Application fee from primary account
        $borrowerAccount = $borrower->accounts()->first();
        if ($borrowerAccount) {
            $borrowerAccount->balance -= 5000;
            $borrowerAccount->save();
            
            $borrower->total_savings -= 5000;
            $borrower->save();

            \App\Models\Transaction::create([
                'reference' => 'FEE-'.\Illuminate\Support\Str::random(6),
                'account_number' => $borrowerAccount->account_number,
                'transaction_type' => 'withdrawal',
                'amount' => 5000,
                'narration' => 'Non-refundable Loan Application Fee',
                'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
            ]);
        }

        $loan = \App\Models\Loan::create([
            'member_id' => $validated['member_id'],
            'principal_amount' => $validated['principal_amount'],
            'interest_rate' => $validated['interest_rate'],
            'term_months' => $validated['term_months'],
            'status' => 'under_review',
        ]);

        \App\Models\GuarantorPledge::create([
            'loan_id' => $loan->id,
            'guarantor_member_id' => $validated['guarantor_1_id'],
            'pledged_amount' => $validated['guarantor_1_amount'],
            'status' => 'pending',
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan Application submitted successfully. UGX 5,000 fee deducted.'); 
    }
    public function show($id) { 
        $loan = \App\Models\Loan::with(['member', 'guarantorPledges.guarantor', 'repayments'])->findOrFail($id);
        return view('loans.show', compact('loan')); 
    }
    public function edit($id) { return view('loans.edit', compact('id')); }
    
    public function update(Request $request, $id) { 
        $loan = \App\Models\Loan::findOrFail($id);
        $action = $request->input('action');
        
        if ($action === 'approve') {
            $loan->update(['status' => 'approved']);
            \App\Models\GuarantorPledge::where('loan_id', $loan->id)->update(['status' => 'frozen']);
            return redirect()->back()->with('success', 'Loan approved and guarantor funds frozen.');
        } elseif ($action === 'reject') {
            $request->validate(['rejection_reason' => 'required|string|max:500']);
            $loan->update([
                'status' => 'rejected', 
                'rejection_reason' => $request->input('rejection_reason')
            ]);
            \App\Models\GuarantorPledge::where('loan_id', $loan->id)->update(['status' => 'released']);
            return redirect()->back()->with('success', 'Loan rejected successfully.');
        } elseif ($action === 'disburse') {
            $p = $loan->principal_amount;
            $r = $loan->interest_rate / 100;
            $n = $loan->term_months;
            $totalRepayable = ($r > 0 && $n > 0)
                ? round($p * $r * pow(1 + $r, $n) / (pow(1 + $r, $n) - 1) * $n, 2)
                : $p;
            $loan->update(['status' => 'disbursed', 'outstanding_balance' => $totalRepayable]);
            $account = $loan->member->accounts()->first();
            if ($account) {
                $account->balance += $loan->principal_amount;
                $account->save();
                \App\Models\Transaction::create([
                    'reference'             => 'DIS-'.\Illuminate\Support\Str::random(6),
                    'account_number'        => $account->account_number,
                    'transaction_type'      => 'deposit',
                    'amount'                => $loan->principal_amount,
                    'narration'             => 'Loan Disbursement - Loan #'.$loan->id,
                    'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
                ]);
            }
            return redirect()->back()->with('success', 'Loan disbursed. UGX '.number_format($loan->principal_amount).' credited to member account.');
        }
        
        return redirect()->route('loans.index')->with('success', 'Updated successfully'); 
    }
    
    public function destroy($id) { return redirect()->route('loans.index')->with('success', 'Deleted successfully'); }
}
