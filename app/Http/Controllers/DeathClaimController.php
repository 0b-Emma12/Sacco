<?php

namespace App\Http\Controllers;

use App\Models\DeathClaim;
use App\Models\Member;
use App\Models\Transaction;
use App\Services\LedgerService;
use App\Services\MemberCapitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeathClaimController extends Controller
{
    public function index()
    {
        $claims = DeathClaim::with('member')->orderByDesc('id')->paginate(12);

        $totals = [
            'total' => DeathClaim::count(),
            'pending' => DeathClaim::where('status', 'PENDING')->count(),
            'approved' => DeathClaim::where('status', 'APPROVED')->count(),
            'paid' => (float) DeathClaim::where('status', 'PAID')->sum('net_payable'),
        ];

        return view('member-services.death-claims.index', compact('claims', 'totals'));
    }

    public function create(Member $member)
    {
        $member->load('nextOfKins');

        return view('member-services.death-claims.create', [
            'member' => $member,
            'entitlement' => $this->entitlement($member),
            'documents' => DeathClaim::DOCUMENTS,
        ]);
    }

    public function store(Request $request, Member $member)
    {
        $data = $request->validate([
            'date_of_death' => 'required|date|before_or_equal:today',
            'death_certificate_number' => 'required|string|max:100',
            'claimant_name' => 'required|string|max:150',
            'claimant_nin' => 'required|string|max:50',
            'claimant_relationship' => 'required|string|max:60',
            'claimant_phone' => 'required|string|max:30',
            'documents' => 'nullable|array',
            'documents.*' => 'in:'.implode(',', array_keys(DeathClaim::DOCUMENTS)),
        ]);

        $open = DeathClaim::where('member_id', $member->id)->whereIn('status', ['PENDING', 'APPROVED'])->exists();
        if ($open) {
            throw ValidationException::withMessages([
                'claim' => 'This member already has an open death claim.',
            ]);
        }

        $entitlement = $this->entitlement($member);

        $claim = DeathClaim::create([
            'member_id' => $member->id,
            'date_of_death' => $data['date_of_death'],
            'death_certificate_number' => $data['death_certificate_number'],
            'claimant_name' => $data['claimant_name'],
            'claimant_nin' => $data['claimant_nin'],
            'claimant_relationship' => $data['claimant_relationship'],
            'claimant_phone' => $data['claimant_phone'],
            'documents' => array_values($data['documents'] ?? []),
            'share_balance' => $entitlement['share_balance'],
            'savings_balance' => $entitlement['savings_balance'],
            'loan_outstanding' => $entitlement['loan_outstanding'],
            'guarantee_liability' => $entitlement['guarantee_liability'],
            'net_payable' => $entitlement['net_payable'],
            'status' => 'PENDING',
            'requested_by' => $this->userName($request),
        ]);

        return redirect()->route('member-services.death-claims.show', $claim)
            ->with('success', 'Death claim registered for board approval.');
    }

    public function show(DeathClaim $deathClaim)
    {
        $deathClaim->load('member');

        return view('member-services.death-claims.show', [
            'claim' => $deathClaim,
            'documents' => DeathClaim::DOCUMENTS,
            'journalEntry' => \App\Models\JournalEntry::with('lineItems')
                ->where('reference_type', 'death_claim')
                ->where('reference_id', $deathClaim->id)
                ->latest('id')
                ->first(),
        ]);
    }

    public function approve(Request $request, DeathClaim $deathClaim)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only the Administrator or Manager may approve a death claim.');
        }
        if (! $deathClaim->isPending()) {
            return redirect()->back()->withErrors(['claim' => 'Only a pending claim can be approved.']);
        }

        $data = $request->validate([
            'decision_notes' => 'nullable|string|max:2000',
        ]);

        $entitlement = $this->entitlement($deathClaim->member);
        $missing = array_diff(array_keys(DeathClaim::DOCUMENTS), $deathClaim->checkedDocuments());
        if (in_array('death_certificate', $missing, true)) {
            return redirect()->back()->withErrors(['claim' => 'The death certificate must be on file before approval.']);
        }

        $deathClaim->update([
            'share_balance' => $entitlement['share_balance'],
            'savings_balance' => $entitlement['savings_balance'],
            'loan_outstanding' => $entitlement['loan_outstanding'],
            'guarantee_liability' => $entitlement['guarantee_liability'],
            'net_payable' => $entitlement['net_payable'],
            'status' => 'APPROVED',
            'approved_by' => $this->userName($request),
            'approved_at' => now(),
            'decision_notes' => $data['decision_notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Claim approved. Record the payout to settle the member account.');
    }

    public function settle(Request $request, DeathClaim $deathClaim)
    {
        $user = $request->user();
        if (! ($user && $user->canDisburseLoans())) {
            abort(403, 'Only the Administrator, Manager or Finance Officer may settle a claim.');
        }
        if (! $deathClaim->isApproved()) {
            return redirect()->back()->withErrors(['claim' => 'The claim must be approved before settlement.']);
        }

        $data = $request->validate([
            'payment_method' => 'required|in:CASH,BANK_TRANSFER,MOBILE_MONEY',
            'payment_reference' => 'nullable|string|max:80',
            'payment_date' => 'required|date|before_or_equal:today',
        ]);

        if ($data['payment_method'] !== 'CASH' && empty($data['payment_reference'])) {
            return redirect()->back()->withErrors(['payment_reference' => 'A payment reference is required for non-cash payouts.']);
        }

        DB::transaction(function () use ($deathClaim, $data, $request) {
            $member = Member::lockForUpdate()->findOrFail($deathClaim->member_id);
            $entitlement = $this->entitlement($member);

            $savings = $entitlement['savings_balance'];
            $shares = $entitlement['share_balance'];
            $loan = $entitlement['loan_outstanding'];
            $guarantee = $entitlement['guarantee_liability'];
            $net = $entitlement['net_payable'];
            $writeOff = round(max(0, $loan + $guarantee - $savings - $shares), 2);

            $lines = [
                ['code' => '2000', 'debit' => $savings],
                ['code' => '3000', 'debit' => $shares],
                ['code' => '5300', 'debit' => $writeOff],
                ['code' => '1200', 'credit' => $loan],
                ['code' => '2300', 'credit' => $guarantee],
                ['code' => '1000', 'credit' => $net],
            ];

            $nonZero = count(array_filter($lines, fn ($line) => ($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0));
            $debitTotal = round($savings + $shares + $writeOff, 2);
            $creditTotal = round($loan + $guarantee + $net, 2);

            if ($nonZero >= 2 && abs($debitTotal - $creditTotal) < 0.005) {
                LedgerService::post(
                    'DCL',
                    'Death claim settlement - '.$member->membership_number,
                    $lines,
                    'death_claim',
                    $deathClaim->id
                );
            }

            if ($net > 0) {
                $account = $member->accounts()->lockForUpdate()->orderByDesc('balance')->first();
                if ($account) {
                    Transaction::create([
                        'reference' => 'DCL-'.strtoupper(Str::random(6)),
                        'account_number' => $account->account_number,
                        'transaction_type' => 'withdrawal',
                        'amount' => $net,
                        'narration' => 'Death benefit payout to '.$deathClaim->claimant_name.' ('.$deathClaim->claimant_relationship.')',
                        'performed_by_username' => $this->userName($request),
                    ]);
                }
                $member->accounts()->update(['balance' => 0, 'is_active' => false]);
            }

            $member->total_savings = 0;
            $member->share_capital = 0;
            $member->status = 'Deceased';
            $member->save();

            \App\Models\GuarantorPledge::where('guarantor_member_id', $member->id)
                ->whereIn('status', ['frozen', 'active'])
                ->update(['status' => 'released']);

            $deathClaim->update([
                'share_balance' => $shares,
                'savings_balance' => $savings,
                'loan_outstanding' => $loan,
                'guarantee_liability' => $guarantee,
                'net_payable' => $net,
                'status' => 'PAID',
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_date' => $data['payment_date'],
            ]);

            MemberCapitalService::refresh($member);
        });

        return redirect()->back()->with('success', 'Death claim settled and member account closed.');
    }

    private function entitlement(Member $member): array
    {
        $capital = MemberCapitalService::calculate($member);

        $loan = 0.0;
        foreach ($member->loans()->whereIn('status', ['disbursed', 'partially_paid', 'defaulted'])->get() as $row) {
            $loan += MemberCapitalService::outstandingPrincipal($row);
        }

        $savings = round((float) $capital['savings_balance'], 2);
        $shares = round((float) $capital['share_balance'], 2);
        $loan = round($loan, 2);
        $guarantee = round((float) $capital['guarantee_liability'], 2);

        return [
            'savings_balance' => $savings,
            'share_balance' => $shares,
            'loan_outstanding' => $loan,
            'guarantee_liability' => $guarantee,
            'net_payable' => round(max(0, $savings + $shares - $loan - $guarantee), 2),
        ];
    }

    private function userName(Request $request): string
    {
        $user = $request->user();

        return $user ? (string) ($user->username ?? $user->email) : 'System';
    }
}
