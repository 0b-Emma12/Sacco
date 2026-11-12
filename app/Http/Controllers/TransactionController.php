<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request) 
    {
        $search = (string) $request->input('search');
        $base = Transaction::with('account.member')->matching(['search' => $search]);

        $creditTypes = ['deposit', 'dividend', 'loan_disbursement'];
        $summary = [
            'count' => (clone $base)->count(),
            'inflow' => (float) (clone $base)->whereIn('transaction_type', $creditTypes)->sum('amount'),
            'outflow' => (float) (clone $base)->whereNotIn('transaction_type', $creditTypes)->sum('amount'),
        ];

        $typeCounts = (clone $base)
            ->reorder()
            ->selectRaw('transaction_type, COUNT(*) as aggregate')
            ->groupBy('transaction_type')
            ->pluck('aggregate', 'transaction_type');

        $type = (string) $request->input('type');
        if ($type === '' || ! $typeCounts->has($type)) {
            $type = '';
        }

        $transactions = (clone $base)->matching(['type' => $type])->latest()->paginate(10)->withQueryString();

        return view('transactions.index', compact('transactions', 'summary', 'typeCounts', 'type')); 
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
            'payment_method' => 'nullable|string|in:CASH,MOBILE_MONEY,BANK_TRANSFER,CHEQUE',
            'external_reference' => 'nullable|string|max:100',
        ]);

        $amount = (float) $request->amount;

        $transaction = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $amount) {
            $account = \App\Models\Account::where('account_number', $request->account_number)->lockForUpdate()->firstOrFail();
            $member = $account->member;

            // Block transactions on inactive accounts or members who have exited/closed
            if (!$account->is_active) {
                throw \Illuminate\Validation\ValidationException::withMessages(['account_number' => 'This account is inactive. Transactions are not allowed.']);
            }
            if ($member && in_array(strtolower($member->status), ['exited', 'closed', 'deceased'], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['account_number' => 'This member has left the SACCO. Transactions are not allowed.']);
            }
            if ($member && $member->transactionsBlockedByKyc()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'account_number' => 'KYC verification is outstanding. Transactions were blocked from '.$member->kycGraceEndsAt()->format('d M Y').' until form M02 is approved.',
                ]);
            }

            if ($request->transaction_type === 'withdrawal') {
                if ($account->maturity_date && \Carbon\Carbon::parse($account->maturity_date)->isFuture()) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'This fixed deposit matures on '.$account->maturity_date.'. Early withdrawal is not permitted.']);
                }

                if ((float) $account->balance < $amount) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Insufficient account balance.']);
                }

                $available = (float) $member->available_savings;
                if ($available < $amount) {
                    $frozen = (float) $member->total_savings - $available;
                    throw \Illuminate\Validation\ValidationException::withMessages(['amount' => 'Insufficient available savings. UGX '.number_format($frozen, 2).' is held as loan security or guarantor collateral.']);
                }

                $account->balance -= $amount;
                $member->total_savings -= $amount;
            } else {
                $account->balance += $amount;
                $member->total_savings += $amount;

                if ($member && strtolower($member->status) === 'dormant') {
                    $member->status = 'Active';
                }
            }

            $account->save();
            $member->save();

            $tx = Transaction::create([
                'reference' => strtoupper(\Illuminate\Support\Str::random(10)),
                'account_number' => $request->account_number,
                'transaction_type' => $request->transaction_type,
                'amount' => $amount,
                'narration' => $request->narration,
                'payment_method' => $request->input('payment_method', 'CASH'),
                'external_reference' => $request->input('external_reference'),
                'performed_by_username' => auth()->user() ? auth()->user()->username : 'System',
            ]);

            $isDeposit = $request->transaction_type === 'deposit';
            \App\Services\LedgerService::post(
                $isDeposit ? 'DEP' : 'WDL',
                ($isDeposit ? 'Member deposit' : 'Member withdrawal').' - '.$member->membership_number,
                $isDeposit
                    ? [['code' => '1000', 'debit' => $amount], ['code' => '2000', 'credit' => $amount]]
                    : [['code' => '2000', 'debit' => $amount], ['code' => '1000', 'credit' => $amount]],
                'transaction',
                $tx->id
            );

            \App\Services\MemberCapitalService::refresh($member);

            return $tx;
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction '.$transaction->reference.' completed successfully'); 
    }

    public function show($id) 
    { 
        $transaction = Transaction::findOrFail($id);
        return view('transactions.show', compact('transaction')); 
    }

    public function receipt($id) 
    { 
        $transaction = Transaction::with('account.member')->findOrFail($id);
        $member = $transaction->account->member ?? null;
        $print = \App\Services\PrintDocumentService::forRequest('DEPOSIT_RECEIPT', $transaction, $member);
        $amountInWords = \App\Services\NumberToWords::shillings((float) $transaction->amount);

        return view('transactions.receipt', compact('transaction', 'member', 'print', 'amountInWords')); 
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
