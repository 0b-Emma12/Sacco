<?php

namespace App\Http\Controllers;

use App\Models\DeathClaim;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\MemberDividend;
use App\Models\Policy;
use App\Models\PrintLog;
use App\Models\Setting;
use App\Models\ShareTransfer;
use App\Models\StandingOrder;
use App\Models\SurplusDistribution;
use App\Models\Transaction;
use App\Services\MemberCapitalService;
use App\Services\PrintDocumentService;
use App\Services\RepaymentScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PrintDocumentController extends Controller
{
    private const SAVINGS_TYPES = [
        'deposit', 'withdrawal', 'dividend', 'loan_disbursement', 'fee', 'loan_repayment', 'share_transfer',
    ];

    private const CREDIT_TYPES = ['deposit', 'dividend', 'loan_disbursement'];

    public function welcome(Member $member)
    {
        $capital = MemberCapitalService::calculate($member);
        $policies = Policy::where('status', 'APPROVED')->orderBy('name')->get();
        $sharePrice = (float) Setting::get('sys_share_price', 10000);
        $print = PrintDocumentService::forRequest('MEMBER_WELCOME', $member, $member);

        return view('prints.welcome', compact('member', 'capital', 'policies', 'sharePrice', 'print'));
    }

    public function shareCertificate(Member $member)
    {
        $capital = MemberCapitalService::calculate($member);
        $sharePrice = (float) Setting::get('sys_share_price', 10000);
        $certificateNumber = 'SC-'.$member->membership_number.'-'.now()->format('Y');
        $print = PrintDocumentService::forRequest('SHARE_CERTIFICATE', $member, $member, [
            'certificate_number' => $certificateNumber,
            'shares' => $capital['share_count'],
            'value' => $capital['share_balance'],
        ]);

        return view('prints.share-certificate', compact('member', 'capital', 'sharePrice', 'certificateNumber', 'print'));
    }

    public function disbursementVoucher(Loan $loan)
    {
        if (! $loan->disbursed_at) {
            throw new NotFoundHttpException('This loan has not been disbursed.');
        }

        $loan->load(['member', 'product', 'schedules']);
        $processingFee = (float) $loan->processing_fee;
        $insuranceFee = (float) $loan->insurance_fee;
        $net = round((float) $loan->principal_amount - $processingFee - $insuranceFee, 2);
        $firstSchedule = $loan->schedules->first();
        $account = $loan->member->accounts()->where('is_active', true)->first();
        $print = PrintDocumentService::forRequest('LOAN_DISBURSEMENT', $loan, $loan->member);

        return view('prints.disbursement-voucher', compact(
            'loan', 'processingFee', 'insuranceFee', 'net', 'firstSchedule', 'account', 'print'
        ));
    }

    public function repaymentReceipt(Loan $loan, LoanRepayment $repayment)
    {
        if ((int) $repayment->loan_id !== (int) $loan->id) {
            throw new NotFoundHttpException('This repayment does not belong to the loan.');
        }

        $loan->load('member');
        $print = PrintDocumentService::forRequest('LOAN_REPAYMENT', $repayment, $loan->member, [
            'loan_id' => $loan->id,
            'amount' => (float) $repayment->amount_paid,
        ]);

        return view('prints.repayment-receipt', compact('loan', 'repayment', 'print'));
    }

    public function statement(Request $request, Member $member)
    {
        [$from, $to] = $this->period($request);
        $data = $this->statementData($member, $from, $to);
        $print = PrintDocumentService::forRequest('MEMBER_STATEMENT', $member, $member, [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);

        return view('prints.statement', array_merge($data, compact('member', 'from', 'to', 'print')));
    }

    public function dividendStatement(Member $member, MemberDividend $dividend)
    {
        $this->assertDividendBelongsTo($dividend, $member);
        $dividend->load(['distribution', 'member']);
        $print = PrintDocumentService::forRequest('DIVIDEND_STATEMENT', $dividend, $member, [
            'financial_year' => $dividend->distribution->financial_year,
        ]);

        return view('prints.dividend-statement', compact('member', 'dividend', 'print'));
    }

    public function whtCertificate(Member $member, MemberDividend $dividend)
    {
        $this->assertDividendBelongsTo($dividend, $member);
        $dividend->load(['distribution', 'member']);
        $print = PrintDocumentService::forRequest('WHT_CERTIFICATE', $dividend, $member, [
            'financial_year' => $dividend->distribution->financial_year,
        ]);

        return view('prints.wht-certificate', compact('member', 'dividend', 'print'));
    }

    public function clearance(Loan $loan)
    {
        if ($loan->status !== 'paid') {
            throw new NotFoundHttpException('A clearance certificate is only available for fully paid loans.');
        }

        $loan->load(['member', 'repayments']);
        $lastPayment = $loan->repayments->sortByDesc('created_at')->first();
        $print = PrintDocumentService::forRequest('LOAN_CLEARANCE', $loan, $loan->member, [
            'loan_id' => $loan->id,
        ]);

        return view('prints.clearance', compact('loan', 'lastPayment', 'print'));
    }

    public function guarantorRelease(Loan $loan)
    {
        if ($loan->status !== 'paid') {
            throw new NotFoundHttpException('A guarantor release is only available once the loan is fully paid.');
        }

        $loan->load(['member', 'guarantorPledges.guarantor', 'repayments']);
        $lastPayment = $loan->repayments->sortByDesc('created_at')->first();
        $print = PrintDocumentService::forRequest('GUARANTOR_RELEASE', $loan, $loan->member, [
            'loan_id' => $loan->id,
        ]);

        return view('prints.guarantor-release', compact('loan', 'lastPayment', 'print'));
    }

    public function expenseVoucher(Expense $expense)
    {
        if ($expense->status !== 'approved') {
            throw new NotFoundHttpException('A payment voucher is only available for an approved expense.');
        }

        $expense->load(['creator', 'approver']);
        $print = PrintDocumentService::forRequest('EXPENSE_VOUCHER', $expense);

        return view('prints.expense-voucher', compact('expense', 'print'));
    }

    public function deathSettlement(DeathClaim $claim)
    {
        if (! $claim->isPaid()) {
            throw new NotFoundHttpException('A settlement letter is only available once the death claim is paid.');
        }

        $claim->load('member');
        $print = PrintDocumentService::forRequest('DEATH_SETTLEMENT', $claim, $claim->member);

        return view('prints.death-settlement', compact('claim', 'print'));
    }

    public function shareTransferCertificate(ShareTransfer $shareTransfer)
    {
        if (! $shareTransfer->isApproved()) {
            throw new NotFoundHttpException('A transfer certificate is only available once the transfer is approved.');
        }

        $shareTransfer->load(['transferor', 'transferee']);
        $print = PrintDocumentService::forRequest('SHARE_TRANSFER', $shareTransfer, $shareTransfer->transferor);

        return view('prints.share-transfer', compact('shareTransfer', 'print'));
    }

    public function standingOrderMandate(StandingOrder $standingOrder)
    {
        $standingOrder->load(['member', 'account', 'loan']);
        $print = PrintDocumentService::forRequest('STANDING_ORDER', $standingOrder, $standingOrder->member);

        return view('prints.standing-order', compact('standingOrder', 'print'));
    }

    public function statements(Request $request)
    {
        [$from, $to] = $this->period($request);
        $members = Member::where('status', 'Active')->orderBy('membership_number')->get();

        $pages = $members->map(function (Member $member) use ($from, $to) {
            $data = $this->statementData($member, $from, $to);
            $data['member'] = $member;
            $data['print'] = PrintDocumentService::forRequest('MEMBER_STATEMENT', $member, $member, [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'bulk' => true,
            ]);

            return $data;
        })->all();

        return view('prints.statements-bulk', compact('pages', 'from', 'to'));
    }

    public function transactionLedger(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $type = (string) $request->input('type');

        $knownTypes = Transaction::query()
            ->matching(['search' => $search])
            ->reorder()
            ->selectRaw('transaction_type, COUNT(*) as aggregate')
            ->groupBy('transaction_type')
            ->pluck('aggregate', 'transaction_type');

        if ($type === '' || ! $knownTypes->has($type)) {
            $type = '';
        }

        $transactions = Transaction::with('account.member')
            ->matching(['search' => $search, 'type' => $type])
            ->latest()
            ->get();

        $totals = [
            'count' => $transactions->count(),
            'inflow' => (float) $transactions->whereIn('transaction_type', self::CREDIT_TYPES)->sum('amount'),
            'outflow' => (float) $transactions->whereNotIn('transaction_type', self::CREDIT_TYPES)->sum('amount'),
        ];
        $totals['net'] = $totals['inflow'] - $totals['outflow'];

        $print = PrintDocumentService::forRequest('TRANSACTION_LEDGER', null, null, [
            'search' => $search !== '' ? $search : null,
            'type' => $type !== '' ? $type : null,
            'postings' => $totals['count'],
            'inflow' => $totals['inflow'],
            'outflow' => $totals['outflow'],
        ]);

        return view('prints.transaction-ledger', compact('transactions', 'totals', 'print') + [
            'filters' => ['search' => $search, 'type' => $type],
        ]);
    }

    public function dividendStatements(SurplusDistribution $distribution)
    {
        $distribution->load('dividends.member');

        $pages = $distribution->dividends
            ->sortBy(fn (MemberDividend $dividend) => $dividend->member->membership_number ?? '')
            ->map(function (MemberDividend $dividend) use ($distribution) {
            return [
                'dividend' => $dividend,
                'member' => $dividend->member,
                'print' => PrintDocumentService::forRequest('DIVIDEND_STATEMENT', $dividend, $dividend->member, [
                    'financial_year' => $distribution->financial_year,
                    'bulk' => true,
                ]),
            ];
        })->all();

        return view('prints.dividend-statements-bulk', compact('distribution', 'pages'));
    }

    public function verify(Request $request, string $document, string $token)
    {
        $log = PrintLog::where('document_id', $document)->first();

        if (! $log || ! hash_equals($log->verification_token, $token)) {
            return response()->view('prints.verify', ['log' => null], 404);
        }

        return view('prints.verify', compact('log'));
    }

    private function period(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfYear();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function statementData(Member $member, Carbon $from, Carbon $to): array
    {
        $capital = MemberCapitalService::calculate($member);
        $accountNumbers = $member->accounts()->pluck('account_number');

        $query = Transaction::whereIn('account_number', $accountNumbers)
            ->whereIn('transaction_type', self::SAVINGS_TYPES);

        $transactions = (clone $query)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $netSinceFrom = (float) (clone $query)
            ->where('created_at', '>=', $from->copy()->startOfDay())
            ->get()
            ->sum(fn (Transaction $transaction) => $this->signedAmount($transaction));

        $opening = round((float) $capital['savings_balance'] - $netSinceFrom, 2);
        $running = $opening;
        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($transactions as $transaction) {
            $signed = $this->signedAmount($transaction);
            $running = round($running + $signed, 2);
            $debit = $signed < 0 ? round(abs($signed), 2) : 0.0;
            $credit = $signed > 0 ? $signed : 0.0;
            $totalDebit += $debit;
            $totalCredit += $credit;

            $rows[] = [
                'date' => $transaction->created_at,
                'reference' => $transaction->reference,
                'description' => $transaction->narration ?: Str::title(str_replace('_', ' ', $transaction->transaction_type)),
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $running,
            ];
        }

        $activeLoans = $member->loans()
            ->whereIn('status', ['disbursed', 'partially_paid', 'defaulted'])
            ->get()
            ->map(fn (Loan $loan) => [
                'loan' => $loan,
                'outstanding' => RepaymentScheduleService::outstanding($loan),
            ]);

        return [
            'capital' => $capital,
            'rows' => $rows,
            'opening' => $opening,
            'closing' => round($opening + $totalCredit - $totalDebit, 2),
            'totalDebit' => round($totalDebit, 2),
            'totalCredit' => round($totalCredit, 2),
            'activeLoans' => $activeLoans,
        ];
    }

    private function signedAmount(Transaction $transaction): float
    {
        $amount = (float) $transaction->amount;

        return in_array($transaction->transaction_type, self::CREDIT_TYPES, true) ? $amount : -$amount;
    }

    private function assertDividendBelongsTo(MemberDividend $dividend, Member $member): void
    {
        if ((string) $dividend->member_id !== (string) $member->getKey()) {
            throw new NotFoundHttpException('This dividend record does not belong to the member.');
        }
    }
}
