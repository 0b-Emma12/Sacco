<?php

namespace App\Http\Controllers;

use App\Models\PettyCashReplenishment;
use App\Models\PettyCashVoucher;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PettyCashController extends Controller
{
    public function index()
    {
        $vouchers = PettyCashVoucher::orderByDesc('date')->orderByDesc('id')->paginate(12);
        $replenishments = PettyCashReplenishment::orderByDesc('date')->orderByDesc('id')->take(8)->get();
        $floatBalance = LedgerService::accountBalance('1400');
        $pendingTotal = (float) PettyCashVoucher::where('status', 'PENDING')->sum('amount');
        $pendingCount = PettyCashVoucher::where('status', 'PENDING')->count();
        $approvedTotal = (float) PettyCashVoucher::where('status', 'APPROVED')->sum('amount');
        $replenishmentCount = PettyCashReplenishment::count();

        return view('treasury.petty-cash.index', compact(
            'vouchers',
            'replenishments',
            'floatBalance',
            'pendingTotal',
            'pendingCount',
            'approvedTotal',
            'replenishmentCount'
        ));
    }

    public function create()
    {
        return view('treasury.petty-cash.create', [
            'floatBalance' => LedgerService::accountBalance('1400'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'nullable|date|before_or_equal:today',
            'paid_to' => 'required|string|max:150',
            'amount' => 'required|numeric|min:0.01',
            'purpose' => 'required|string|max:255',
            'receipt_attached' => 'nullable|boolean',
        ]);

        $voucher = DB::transaction(function () use ($data, $request) {
            $next = ((int) PettyCashVoucher::lockForUpdate()->max('id')) + 1;

            return PettyCashVoucher::create([
                'voucher_number' => 'PCV-'.now()->format('Y').'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT),
                'date' => $data['date'] ?? now()->toDateString(),
                'paid_to' => $data['paid_to'],
                'amount' => $data['amount'],
                'purpose' => $data['purpose'],
                'receipt_attached' => (bool) ($data['receipt_attached'] ?? false),
                'status' => 'PENDING',
                'requested_by' => $request->user()->username ?? $request->user()->email,
            ]);
        });

        return redirect()->route('treasury.petty-cash.index')
            ->with('success', 'Petty cash voucher '.$voucher->voucher_number.' recorded and awaiting approval.');
    }

    public function approve(Request $request, PettyCashVoucher $voucher)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only a manager or administrator may approve petty cash.');
        }
        if (! $voucher->isPending()) {
            return redirect()->back()->withErrors(['petty_cash' => 'This voucher has already been decided.']);
        }

        $floatBalance = LedgerService::accountBalance('1400');
        if ((float) $voucher->amount > $floatBalance) {
            throw ValidationException::withMessages([
                'petty_cash' => 'The imprest float holds UGX '.number_format($floatBalance, 2).'. Replenish it before approving UGX '.number_format((float) $voucher->amount, 2).'.',
            ]);
        }

        DB::transaction(function () use ($voucher, $user) {
            LedgerService::post('PCV', 'Petty cash '.$voucher->voucher_number.' - '.$voucher->purpose, [
                ['code' => '5000', 'debit' => (float) $voucher->amount],
                ['code' => '1400', 'credit' => (float) $voucher->amount],
            ], 'petty_cash_voucher', $voucher->id);

            $voucher->update([
                'status' => 'APPROVED',
                'approved_by' => $user->username ?? $user->email,
                'approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Voucher approved and posted to the ledger.');
    }

    public function reject(Request $request, PettyCashVoucher $voucher)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only a manager or administrator may reject petty cash.');
        }
        if (! $voucher->isPending()) {
            return redirect()->back()->withErrors(['petty_cash' => 'This voucher has already been decided.']);
        }

        $data = $request->validate([
            'decision_notes' => 'required|string|max:1000',
        ]);

        $voucher->update([
            'status' => 'REJECTED',
            'approved_by' => $user->username ?? $user->email,
            'approved_at' => now(),
            'decision_notes' => $data['decision_notes'],
        ]);

        return redirect()->back()->with('success', 'Voucher rejected.');
    }

    public function replenish(Request $request)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only a manager or administrator may replenish the float.');
        }

        $data = $request->validate([
            'date' => 'nullable|date|before_or_equal:today',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data, $user) {
            $replenishment = PettyCashReplenishment::create([
                'date' => $data['date'] ?? now()->toDateString(),
                'amount' => $data['amount'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'replenished_by' => $user->username ?? $user->email,
            ]);

            LedgerService::post('PCR', 'Petty cash replenishment'.($data['reference'] ? ' '.$data['reference'] : ''), [
                ['code' => '1400', 'debit' => (float) $data['amount']],
                ['code' => '1000', 'credit' => (float) $data['amount']],
            ], 'petty_cash_replenishment', $replenishment->id);
        });

        return redirect()->back()->with('success', 'Petty cash float replenished from the bank account.');
    }
}
