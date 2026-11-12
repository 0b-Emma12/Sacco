<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Transaction;
use App\Services\RepaymentScheduleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanRepaymentController extends Controller
{
    public function store(Request $request, $loanId)
    {
        $loan = Loan::with('member', 'guarantorPledges')->findOrFail($loanId);

        if (! in_array($loan->status, ['disbursed', 'partially_paid', 'defaulted'], true)) {
            return redirect()->back()->withErrors(['amount' => 'Repayments can only be recorded for disbursed loans.']);
        }

        $outstanding = (float) RepaymentScheduleService::outstanding($loan);

        $request->validate([
            'amount'    => "required|numeric|min:1|max:{$outstanding}",
            'narration' => 'nullable|string|max:255',
        ], [
            'amount.max' => 'Amount cannot exceed the outstanding balance of UGX '.number_format($outstanding, 2),
        ]);

        $amount = (float) $request->input('amount');
        $narration = $request->input('narration') ?: 'Loan Repayment - Loan #'.$loan->id;
        $performedBy = auth()->user() ? (auth()->user()->username ?? 'System') : 'System';

        $result = DB::transaction(function () use ($loan, $amount, $narration, $performedBy) {
            $result = RepaymentScheduleService::applyPayment(
                $loan,
                $amount,
                '1000',
                $narration,
                'loan',
                $loan->id,
                $performedBy
            );

            $account = $loan->member->accounts()->first();
            if ($account && $result['applied'] > 0) {
                Transaction::create([
                    'reference'             => 'RPY-'.strtoupper(Str::random(6)),
                    'account_number'        => $account->account_number,
                    'transaction_type'      => 'loan_repayment',
                    'amount'                => $result['applied'],
                    'narration'             => $narration,
                    'performed_by_username' => $performedBy,
                ]);
            }

            return $result;
        });

        if ($result['applied'] <= 0) {
            return redirect()->back()->withErrors(['amount' => 'No amount could be applied to this loan.']);
        }

        $loan->refresh();

        $msg = $loan->status === 'paid'
            ? 'Loan fully paid. All guarantor pledges have been released.'
            : 'Repayment of UGX '.number_format($result['applied']).' recorded (principal '.number_format($result['principal']).', interest '.number_format($result['interest']).'). Outstanding: UGX '.number_format($result['remaining']);

        return redirect()->back()->with('success', $msg);
    }
}
