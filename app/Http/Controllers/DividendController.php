<?php

namespace App\Http\Controllers;

use App\Models\MemberDividend;
use App\Models\SurplusDistribution;
use App\Services\DividendService;
use Illuminate\Http\Request;
use RuntimeException;

class DividendController extends Controller
{
    public function index()
    {
        $distributions = SurplusDistribution::orderByDesc('financial_year')->paginate(10);

        $totals = \Illuminate\Support\Facades\DB::table('member_dividends')
            ->selectRaw('distribution_id, COUNT(*) as members, COALESCE(SUM(total_payout), 0) as paid')
            ->groupBy('distribution_id')
            ->pluck('paid', 'distribution_id');

        $aggregates = [
            'count' => SurplusDistribution::count(),
            'netSurplus' => (float) SurplusDistribution::sum('net_surplus'),
            'dividendPool' => (float) SurplusDistribution::sum('dividend_pool'),
            'paid' => (float) \Illuminate\Support\Facades\DB::table('member_dividends')->sum('total_payout'),
        ];

        return view('treasury.dividends.index', compact('distributions', 'totals', 'aggregates'));
    }

    public function store(Request $request)
    {
        if (! ($request->user() && $request->user()->canApproveLoans())) {
            abort(403, 'Only a manager or administrator may compute a surplus distribution.');
        }

        $data = $request->validate([
            'financial_year' => 'required|integer|min:2000|max:'.now()->year,
        ]);

        try {
            $distribution = DividendService::compute((int) $data['financial_year']);
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['financial_year' => $e->getMessage()])->withInput();
        }

        return redirect()->route('treasury.dividends.show', $distribution)
            ->with('success', 'Draft distribution computed from the FY '.$data['financial_year'].' ledger.');
    }

    public function show(SurplusDistribution $dividend)
    {
        $dividends = MemberDividend::with('member')
            ->where('distribution_id', $dividend->id)
            ->orderByDesc('total_payout')
            ->paginate(25);

        $summary = \Illuminate\Support\Facades\DB::table('member_dividends')
            ->where('distribution_id', $dividend->id)
            ->selectRaw('COUNT(*) as members, COALESCE(SUM(dividend_earned), 0) as dividends, COALESCE(SUM(interest_earned), 0) as interest, COALESCE(SUM(total_payout), 0) as total')
            ->first();

        return view('treasury.dividends.show', compact('dividend', 'dividends', 'summary'));
    }

    public function recompute(Request $request, SurplusDistribution $dividend)
    {
        if (! ($request->user() && $request->user()->canApproveLoans())) {
            abort(403, 'Only a manager or administrator may recompute a distribution.');
        }
        if ($dividend->status !== 'DRAFT') {
            return redirect()->back()->withErrors(['dividend' => 'Only a draft distribution can be recomputed.']);
        }

        try {
            DividendService::compute((int) $dividend->financial_year);
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['dividend' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Distribution recomputed from the ledger.');
    }

    public function approve(Request $request, SurplusDistribution $dividend)
    {
        $user = $request->user();
        if (! ($user && $user->canApproveLoans())) {
            abort(403, 'Only a manager or administrator may approve a distribution.');
        }
        if ($dividend->status !== 'DRAFT') {
            return redirect()->back()->withErrors(['dividend' => 'Only a draft distribution can be approved.']);
        }

        $data = $request->validate([
            'approved_by_board' => 'required|accepted',
            'umra_reference' => 'nullable|string|max:100',
            'agm_date' => 'nullable|date|before_or_equal:today',
        ]);

        $dividend->update([
            'status' => 'APPROVED',
            'approved_by_board' => true,
            'approved_by_umra_reference' => $data['umra_reference'] ?? null,
            'agm_date' => $data['agm_date'] ?? now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Distribution approved. Payouts can now be posted.');
    }

    public function post(Request $request, SurplusDistribution $dividend)
    {
        $user = $request->user();
        if (! ($user && $user->canDisburseLoans())) {
            abort(403, 'Only an administrator, manager or finance officer may post payouts.');
        }

        $data = $request->validate([
            'payout_date' => 'nullable|date|before_or_equal:today',
        ]);

        try {
            $paid = DividendService::post($dividend, $data['payout_date'] ?? now()->toDateString());
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['dividend' => $e->getMessage()]);
        }

        $whtRate = (float) \App\Models\Setting::get('sys_wht_rate', 15);

        return redirect()->back()->with('success', 'Net UGX '.number_format($paid, 2).' credited to member savings after '.number_format($whtRate, 2).'% withholding tax on dividends; posted to the ledger.');
    }
}
