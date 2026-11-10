<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Transaction;

class LoanRepaymentController extends Controller
{
    public function store(Request $request, $loanId)
    {
        $loan = Loan::with('repayments', 'member', 'guarantorPledges')->findOrFail($loanId);

        if (!in_array($loan->status, ['approved', 'disbursed', 'partially_paid'])) {
            return redirect()->back()->withErrors(['amount' => 'Repayments can only be recorded for active/disbursed loans.']);
        }

        $outstanding = $loan->effective_outstanding;

        $request->validate([
            'amount'   => "required|numeric|min:1|max:{$outstanding}",
            'narration'=> 'nullable|string|max:255',
        ], [
            'amount.max' => 'Amount cannot exceed outstanding balance of UGX ' . number_format($outstanding, 2),
        ]);

        $amountPaid   = (float) $request->input('amount');
        $newOutstanding = max(0, $outstanding - $amountPaid);

        LoanRepayment::create([
            'loan_id'     => $loan->id,
            'amount_paid' => $amountPaid,
            'balance_after' => $newOutstanding,
            'narration'   => $request->input('narration') ?: 'Loan Repayment',
            'recorded_by' => auth()->user() ? auth()->user()->name : 'System',
        ]);

        $loan->amount_paid = ($loan->amount_paid ?? 0) + $amountPaid;
        $loan->outstanding_balance = $newOutstanding;

        if ($newOutstanding <= 0) {
            $loan->status = 'paid';
            $loan->guarantorPledges()->update(['status' => 'released']);
            $msg = 'Loan fully paid! All guarantor pledges released.';
        } else {
            $loan->status = 'partially_paid';
            $msg = 'Repayment of UGX ' . number_format($amountPaid) . ' recorded. Outstanding: UGX ' . number_format($newOutstanding);
        }
        $loan->save();

        $account = $loan->member->accounts()->first();
        if ($account) {
            Transaction::create([
                'reference'             => 'RPY-' . \Illuminate\Support\Str::random(6),
                'account_number'        => $account->account_number,
                'transaction_type'      => 'deposit',
                'amount'                => $amountPaid,
                'narration'             => 'Loan Repayment - Loan #' . $loan->id,
                'performed_by_username' => auth()->user() ? (auth()->user()->username ?? auth()->user()->name) : 'System',
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
