<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Loan;
use App\Models\Member;
use App\Models\StandingOrder;
use App\Models\StandingOrderRun;
use App\Services\StandingOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StandingOrderController extends Controller
{
    private const REPAYABLE = ['disbursed', 'partially_paid', 'defaulted'];

    public function index()
    {
        $orders = StandingOrder::with(['member', 'account', 'loan'])->orderByDesc('id')->paginate(12);

        $thisMonth = StandingOrderRun::where('status', 'POSTED')
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year);

        $totals = [
            'active' => StandingOrder::where('status', 'ACTIVE')->count(),
            'pending' => StandingOrder::where('status', 'PENDING')->count(),
            'due' => StandingOrder::where('status', 'ACTIVE')->whereDate('next_run_date', '<=', now()->toDateString())->count(),
            'postedCount' => (clone $thisMonth)->count(),
            'postedValue' => (float) (clone $thisMonth)->sum('amount'),
        ];

        return view('member-services.standing-orders.index', compact('orders', 'totals'));
    }

    public function create(Request $request)
    {
        $members = Member::where('status', 'Active')->orderBy('first_name')->get();

        $member = null;
        $accounts = collect();
        $loans = collect();

        if ($request->filled('member')) {
            $member = Member::find($request->query('member'));
            if (! $member) {
                abort(404);
            }

            $accounts = $member->accounts()->where('is_active', true)->orderByDesc('balance')->get();
            $loans = $member->loans()->whereIn('status', self::REPAYABLE)->orderByDesc('id')->get();
        }

        return view('member-services.standing-orders.create', compact('members', 'member', 'accounts', 'loans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => 'required|exists:members,id',
            'destination_type' => 'required|in:savings,loan',
            'account_id' => 'nullable|integer|exists:accounts,id',
            'loan_id' => 'nullable|integer|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'frequency' => 'required|in:'.implode(',', array_keys(StandingOrder::FREQUENCIES)),
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'source' => 'required|in:'.implode(',', array_keys(StandingOrder::SOURCES)),
            'payer_details' => 'nullable|string|max:150',
            'payer_reference' => 'nullable|string|max:80',
            'member_instructions' => 'nullable|string|max:2000',
            'authorised' => 'required|accepted',
        ]);

        $member = Member::findOrFail($data['member_id']);
        if ($member->status !== 'Active') {
            throw ValidationException::withMessages(['member_id' => 'Only an active member may issue a standing order mandate.']);
        }

        if ($data['destination_type'] === 'savings') {
            $account = Account::find($data['account_id'] ?? 0);
            if (! $account || $account->member_id !== $member->id) {
                throw ValidationException::withMessages(['account_id' => 'Select one of the member\'s own savings accounts.']);
            }
            if (! $account->is_active) {
                throw ValidationException::withMessages(['account_id' => 'The selected savings account is inactive.']);
            }
        } else {
            $loan = Loan::find($data['loan_id'] ?? 0);
            if (! $loan || $loan->member_id !== $member->id) {
                throw ValidationException::withMessages(['loan_id' => 'Select one of the member\'s own loans.']);
            }
            if (! in_array($loan->status, self::REPAYABLE, true)) {
                throw ValidationException::withMessages(['loan_id' => 'Only a disbursed or partially paid loan accepts standing order repayments.']);
            }
        }

        $order = DB::transaction(function () use ($data, $member, $request) {
            $next = ((int) StandingOrder::lockForUpdate()->max('id')) + 1;

            return StandingOrder::create([
                'member_id' => $member->id,
                'mandate_reference' => 'SO-'.now()->format('Y').'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT),
                'destination_type' => $data['destination_type'],
                'account_id' => $data['destination_type'] === 'savings' ? $data['account_id'] : null,
                'loan_id' => $data['destination_type'] === 'loan' ? $data['loan_id'] : null,
                'amount' => round((float) $data['amount'], 2),
                'frequency' => $data['frequency'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'next_run_date' => $data['start_date'],
                'runs_completed' => 0,
                'source' => $data['source'],
                'payer_details' => $data['payer_details'] ?? null,
                'payer_reference' => $data['payer_reference'] ?? null,
                'status' => 'PENDING',
                'requested_by' => $this->userName($request),
                'member_instructions' => $data['member_instructions'] ?? null,
            ]);
        });

        return redirect()->route('member-services.standing-orders.show', $order)
            ->with('success', 'Mandate '.$order->mandate_reference.' registered. Authorise it before it can be processed.');
    }

    public function show(StandingOrder $standingOrder)
    {
        $standingOrder->load(['member', 'account', 'loan']);

        $runs = StandingOrderRun::where('standing_order_id', $standingOrder->id)
            ->orderByDesc('due_date')
            ->paginate(12);

        return view('member-services.standing-orders.show', compact('standingOrder', 'runs'));
    }

    public function approve(Request $request, StandingOrder $standingOrder)
    {
        $this->requireManager($request);

        if (! $standingOrder->isPending()) {
            return redirect()->back()->withErrors(['mandate' => 'Only a pending mandate can be authorised.']);
        }

        $data = $request->validate(['decision_notes' => 'nullable|string|max:2000']);

        $nextRun = $standingOrder->next_run_date->copy()->startOfDay()->max(now()->startOfDay());

        $standingOrder->update([
            'status' => 'ACTIVE',
            'approved_by' => $this->userName($request),
            'approved_at' => now(),
            'decision_notes' => $data['decision_notes'] ?? null,
            'next_run_date' => $nextRun->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Mandate authorised. Due cycles will be processed by the daily standing order run.');
    }

    public function suspend(Request $request, StandingOrder $standingOrder)
    {
        $this->requireManager($request);

        if (! $standingOrder->isActive()) {
            return redirect()->back()->withErrors(['mandate' => 'Only an active mandate can be suspended.']);
        }

        $data = $request->validate(['decision_notes' => 'required|string|max:2000']);

        $standingOrder->update([
            'status' => 'SUSPENDED',
            'decision_notes' => $data['decision_notes'],
        ]);

        return redirect()->back()->with('success', 'Mandate suspended. No cycles will be processed until it is resumed.');
    }

    public function resume(Request $request, StandingOrder $standingOrder)
    {
        $this->requireManager($request);

        if (! $standingOrder->isSuspended()) {
            return redirect()->back()->withErrors(['mandate' => 'Only a suspended mandate can be resumed.']);
        }

        $standingOrder->update([
            'status' => 'ACTIVE',
            'next_run_date' => $standingOrder->next_run_date->copy()->startOfDay()->max(now()->startOfDay())->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Mandate resumed.');
    }

    public function cancel(Request $request, StandingOrder $standingOrder)
    {
        $this->requireManager($request);

        if ($standingOrder->isCancelled() || $standingOrder->isCompleted()) {
            return redirect()->back()->withErrors(['mandate' => 'This mandate is already closed.']);
        }

        $data = $request->validate(['decision_notes' => 'required|string|max:2000']);

        $standingOrder->update([
            'status' => 'CANCELLED',
            'decision_notes' => $data['decision_notes'],
        ]);

        return redirect()->back()->with('success', 'Mandate cancelled.');
    }

    public function postDue(Request $request, StandingOrder $standingOrder)
    {
        $user = $request->user();
        if (! ($user && $user->canDisburseLoans())) {
            abort(403, 'Only an administrator, manager or finance officer may process a mandate.');
        }

        if (! $standingOrder->isActive()) {
            return redirect()->back()->withErrors(['mandate' => 'Only an active mandate can be processed.']);
        }

        $posted = 0;
        $amount = 0.0;
        $failures = [];

        for ($cycle = 0; $cycle < StandingOrderService::maxCatchUp(); $cycle++) {
            $standingOrder->refresh();

            if (! $standingOrder->isActive() || $standingOrder->next_run_date->copy()->startOfDay()->gt(now()->startOfDay())) {
                break;
            }

            try {
                $run = StandingOrderService::execute($standingOrder);
            } catch (RuntimeException $e) {
                $failures[] = $e->getMessage();
                break;
            }

            if ($run->isPosted()) {
                $posted++;
                $amount = round($amount + (float) $run->amount, 2);
            } else {
                $failures[] = $run->note ?: 'Cycle '.$run->due_date->format('d M Y').' failed.';
            }
        }

        if ($posted === 0 && $failures === []) {
            return redirect()->back()->withErrors(['mandate' => 'No cycle is due yet (next run '.$standingOrder->fresh()->next_run_date->format('d M Y').').']);
        }

        $message = $posted > 0
            ? $posted.' cycle(s) posted, UGX '.number_format($amount, 2).' collected.'
            : 'No cycle could be posted.';

        if ($failures !== []) {
            $message .= ' Issues: '.implode(' ', $failures);
        }

        if ($posted > 0) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->withErrors(['mandate' => $message]);
    }

    private function requireManager(Request $request): void
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only the Administrator or Manager may authorise, suspend, resume or cancel a mandate.');
        }
    }

    private function userName(Request $request): string
    {
        $user = $request->user();

        return $user ? (string) ($user->username ?? $user->email) : 'System';
    }
}
