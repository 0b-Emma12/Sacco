<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Setting;
use App\Models\ShareTransfer;
use App\Services\LedgerService;
use App\Services\MemberCapitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShareTransferController extends Controller
{
    public function index()
    {
        $transfers = ShareTransfer::with(['transferor', 'transferee'])->orderByDesc('id')->paginate(12);

        $totals = [
            'total' => ShareTransfer::count(),
            'pending' => ShareTransfer::where('status', 'PENDING')->count(),
            'approved' => ShareTransfer::where('status', 'APPROVED')->count(),
            'value' => (float) ShareTransfer::where('status', 'APPROVED')->sum('share_value'),
        ];

        return view('member-services.share-transfers.index', compact('transfers', 'totals'));
    }

    public function create(Request $request)
    {
        return view('member-services.share-transfers.create', [
            'members' => Member::where('status', 'Active')->orderBy('first_name')->get(),
            'sharePrice' => (float) Setting::get('sys_share_price', 10000),
            'selectedTransferor' => $request->query('member'),
        ]);
    }

    public function store(Request $request)
    {
        $sharePrice = (float) Setting::get('sys_share_price', 10000);
        if ($sharePrice <= 0) {
            throw ValidationException::withMessages(['shares_count' => 'The share price has not been configured.']);
        }

        $data = $request->validate([
            'transferor_member_id' => 'required|exists:members,id',
            'transferee_member_id' => 'required|exists:members,id|different:transferor_member_id',
            'shares_count' => 'required|numeric|min:0.0001',
            'consideration' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:2000',
        ]);

        $transferor = Member::findOrFail($data['transferor_member_id']);
        $transferee = Member::findOrFail($data['transferee_member_id']);

        if ($transferor->status !== 'Active') {
            throw ValidationException::withMessages(['transferor_member_id' => 'Only an active member may transfer shares.']);
        }
        if ($transferee->status !== 'Active') {
            throw ValidationException::withMessages(['transferee_member_id' => 'Shares can only be transferred to an active member.']);
        }

        $shares = round((float) $data['shares_count'], 4);
        $this->assertSharesAvailable($transferor, $shares, $sharePrice);

        ShareTransfer::create([
            'transferor_member_id' => $transferor->id,
            'transferee_member_id' => $transferee->id,
            'shares_count' => $shares,
            'share_value' => round($shares * $sharePrice, 2),
            'consideration' => isset($data['consideration']) ? round((float) $data['consideration'], 2) : null,
            'reason' => $data['reason'] ?? null,
            'status' => 'PENDING',
            'requested_by' => $this->userName($request),
        ]);

        return redirect()->route('member-services.share-transfers.index')
            ->with('success', 'Share transfer submitted for approval.');
    }

    public function show(ShareTransfer $shareTransfer)
    {
        $shareTransfer->load(['transferor', 'transferee']);

        return view('member-services.share-transfers.show', compact('shareTransfer'));
    }

    public function approve(Request $request, ShareTransfer $shareTransfer)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only the Administrator or Manager may approve a share transfer.');
        }
        if (! $shareTransfer->isPending()) {
            return redirect()->back()->withErrors(['transfer' => 'Only a pending transfer can be approved.']);
        }

        $data = $request->validate([
            'decision_notes' => 'nullable|string|max:2000',
            'transfer_date' => 'nullable|date|before_or_equal:today',
        ]);

        DB::transaction(function () use ($shareTransfer, $data, $request) {
            $transferor = Member::lockForUpdate()->findOrFail($shareTransfer->transferor_member_id);
            $transferee = Member::lockForUpdate()->findOrFail($shareTransfer->transferee_member_id);

            $sharePrice = (float) Setting::get('sys_share_price', 10000);
            $shares = round((float) $shareTransfer->shares_count, 4);
            $value = round($shares * $sharePrice, 2);

            if ($value <= 0) {
                throw ValidationException::withMessages(['transfer' => 'The transfer value cannot be zero.']);
            }
            if ((float) $transferor->share_capital + 0.005 < $value) {
                throw ValidationException::withMessages(['transfer' => 'The transferor no longer holds enough share capital for this transfer.']);
            }

            $transferor->share_capital = round((float) $transferor->share_capital - $value, 2);
            $transferor->save();

            $transferee->share_capital = round((float) $transferee->share_capital + $value, 2);
            $transferee->save();

            LedgerService::post(
                'SHT',
                'Share transfer '.$shares.' shares: '.$transferor->membership_number.' to '.$transferee->membership_number,
                [
                    ['code' => '3000', 'debit' => $value],
                    ['code' => '3000', 'credit' => $value],
                ],
                'share_transfer',
                $shareTransfer->id
            );

            $shareTransfer->update([
                'share_value' => $value,
                'status' => 'APPROVED',
                'approved_by' => $this->userName($request),
                'approved_at' => now(),
                'decision_notes' => $data['decision_notes'] ?? null,
                'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                'certificate_reference' => 'SHT-'.strtoupper(Str::random(8)),
            ]);

            MemberCapitalService::refresh($transferor);
            MemberCapitalService::refresh($transferee);
        });

        return redirect()->back()->with('success', 'Share transfer approved and share capital moved.');
    }

    public function reject(Request $request, ShareTransfer $shareTransfer)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only the Administrator or Manager may reject a share transfer.');
        }
        if (! $shareTransfer->isPending()) {
            return redirect()->back()->withErrors(['transfer' => 'Only a pending transfer can be rejected.']);
        }

        $data = $request->validate([
            'decision_notes' => 'required|string|max:2000',
        ]);

        $shareTransfer->update([
            'status' => 'REJECTED',
            'approved_by' => $this->userName($request),
            'approved_at' => now(),
            'decision_notes' => $data['decision_notes'],
        ]);

        return redirect()->back()->with('success', 'Share transfer rejected.');
    }

    private function assertSharesAvailable(Member $transferor, float $shares, float $sharePrice): void
    {
        $pendingShares = (float) ShareTransfer::where('transferor_member_id', $transferor->id)
            ->where('status', 'PENDING')
            ->sum('shares_count');

        $heldShares = $sharePrice > 0 ? round((float) $transferor->share_capital / $sharePrice, 4) : 0;

        if ($shares + $pendingShares - $heldShares > 0.0001) {
            throw ValidationException::withMessages([
                'shares_count' => 'The transferor only holds '.number_format($heldShares - $pendingShares, 4).' uncommitted shares.',
            ]);
        }
    }

    private function userName(Request $request): string
    {
        $user = $request->user();

        return $user ? (string) ($user->username ?? $user->email) : 'System';
    }
}
