<?php

namespace App\Http\Controllers;

use App\Models\GovernanceMeeting;
use App\Models\Policy;
use App\Models\Setting;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    public function index()
    {
        $meetings = GovernanceMeeting::orderByDesc('meeting_date')->paginate(10, ['*'], 'meetings');
        $policies = Policy::orderByDesc('created_at')->paginate(10, ['*'], 'policies');

        $stats = [
            'board' => GovernanceMeeting::ofType('BOARD')->count(),
            'agm' => GovernanceMeeting::ofType('AGM')->count(),
            'pending_meetings' => GovernanceMeeting::where('status', 'DRAFT')->count(),
            'pending_policies' => Policy::where('status', 'DRAFT')->count(),
        ];

        return view('governance.index', compact('meetings', 'policies', 'stats'));
    }

    public function createMeeting($type)
    {
        if (! array_key_exists($type, GovernanceMeeting::TYPES)) {
            abort(404);
        }

        return view('governance.create-meeting', ['type' => $type]);
    }

    public function storeMeeting(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:BOARD,AGM',
            'title' => 'required|string|max:150',
            'meeting_date' => 'required|date|before_or_equal:today',
            'venue' => 'nullable|string|max:150',
            'agenda' => 'nullable|string|max:5000',
            'attendance' => 'nullable|string|max:5000',
            'absent_with_apology' => 'nullable|string|max:2000',
            'resolutions' => 'nullable|string|max:5000',
            'action_items' => 'nullable|string|max:5000',
            'quorum_met' => 'nullable|boolean',
            'members_present' => 'nullable|integer|min:0',
            'members_total' => 'nullable|integer|min:0',
            'dividend_on_shares' => 'nullable|numeric|min:0|max:100',
            'interest_on_savings' => 'nullable|numeric|min:0|max:100',
        ]);

        $quorumPercent = (int) Setting::get('agm_quorum_percent', 33);
        $quorum = (bool) ($data['quorum_met'] ?? false);

        if ($data['type'] === 'AGM' && ! empty($data['members_total'])) {
            $present = (int) ($data['members_present'] ?? 0);
            $quorum = ($present / max(1, (int) $data['members_total'])) * 100 >= $quorumPercent;
        }

        $meeting = GovernanceMeeting::create([
            'type' => $data['type'],
            'title' => $data['title'],
            'meeting_date' => $data['meeting_date'],
            'venue' => $data['venue'] ?? null,
            'agenda' => $data['agenda'] ?? null,
            'attendance' => $data['attendance'] ?? null,
            'absent_with_apology' => $data['absent_with_apology'] ?? null,
            'resolutions' => $data['resolutions'] ?? null,
            'action_items' => $data['action_items'] ?? null,
            'quorum_met' => $quorum,
            'members_present' => $data['members_present'] ?? null,
            'members_total' => $data['members_total'] ?? null,
            'dividend_on_shares' => $data['type'] === 'AGM' ? ($data['dividend_on_shares'] ?? null) : null,
            'interest_on_savings' => $data['type'] === 'AGM' ? ($data['interest_on_savings'] ?? null) : null,
            'status' => 'DRAFT',
            'prepared_by' => $request->user()->username ?? $request->user()->email,
        ]);

        return redirect()->route('governance.meetings.show', $meeting)->with('success', 'Minutes recorded as a draft. Record the resolutions, then approve.');
    }

    public function showMeeting(GovernanceMeeting $meeting)
    {
        return view('governance.show-meeting', compact('meeting'));
    }

    public function approveMeeting(Request $request, GovernanceMeeting $meeting)
    {
        if (! ($request->user() && $request->user()->canApproveLoans())) {
            abort(403, 'Only the chairperson, manager or administrator may approve minutes.');
        }
        if ($meeting->isApproved()) {
            return redirect()->back()->withErrors(['meeting' => 'These minutes have already been approved.']);
        }
        if (blank($meeting->resolutions)) {
            return redirect()->back()->withErrors(['meeting' => 'Record the resolutions passed before approving the minutes.']);
        }

        $data = $request->validate(['approved_by' => 'nullable|string|max:100']);

        $meeting->update([
            'status' => 'APPROVED',
            'approved_by' => $data['approved_by'] ?? ($request->user()->username ?? $request->user()->email),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Minutes approved and locked for the record.');
    }

    public function createPolicy()
    {
        return view('governance.create-policy');
    }

    public function storePolicy(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'version' => 'required|string|max:30',
            'effective_date' => 'nullable|date',
            'summary' => 'required|string|max:5000',
            'change_reason' => 'nullable|string|max:2000',
            'impact_assessment' => 'nullable|string|max:2000',
        ]);

        $policy = Policy::create([
            'name' => $data['name'],
            'version' => $data['version'],
            'effective_date' => $data['effective_date'] ?? null,
            'summary' => $data['summary'],
            'change_reason' => $data['change_reason'] ?? null,
            'impact_assessment' => $data['impact_assessment'] ?? null,
            'status' => 'DRAFT',
            'created_by' => $request->user()->username ?? $request->user()->email,
        ]);

        return redirect()->route('governance.policies.show', $policy)->with('success', 'Policy recorded as a draft pending board approval.');
    }

    public function showPolicy(Policy $policy)
    {
        return view('governance.show-policy', compact('policy'));
    }

    public function approvePolicy(Request $request, Policy $policy)
    {
        if (! ($request->user() && $request->user()->canApproveLoans())) {
            abort(403, 'Only an administrator or manager may approve policies.');
        }
        if ($policy->isApproved()) {
            return redirect()->back()->withErrors(['policy' => 'This policy has already been approved.']);
        }

        $data = $request->validate([
            'resolution_number' => 'required|string|max:100',
            'effective_date' => 'nullable|date',
        ]);

        $policy->update([
            'status' => 'APPROVED',
            'resolution_number' => $data['resolution_number'],
            'effective_date' => $data['effective_date'] ?? $policy->effective_date ?? now()->toDateString(),
            'approved_by' => $request->user()->username ?? $request->user()->email,
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Policy approved and effective.');
    }
}
