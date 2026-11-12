@extends('layouts.app')
@section('title', \App\Models\GovernanceMeeting::TYPES[$type].' Minutes')

@section('content')
@php
    $isAgm = $type === 'AGM';
    $quorumPercent = (int) \App\Models\Setting::get('agm_quorum_percent', 33);
    $label = \App\Models\GovernanceMeeting::TYPES[$type];
@endphp
<div class="flex flex-col gap-6 mx-auto w-full max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Governance</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">{{ $label }} Minutes</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $isAgm ? 'G03 — annual general meeting record, attendance and member resolutions.' : 'G01 — board meeting record, attendance and resolutions.' }}
            </p>
        </div>
        <a href="{{ route('governance.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Governance</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        @if ($errors->any())
            <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl mb-6 text-sm font-semibold">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('governance.meetings.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="space-y-4">
                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Meeting title</label>
                            <input type="text" name="title" value="{{ old('title', $label.' — '.now()->format('d M Y')) }}" maxlength="150" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Meeting date</label>
                            <input type="date" name="meeting_date" value="{{ old('meeting_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Venue</label>
                            <input type="text" name="venue" value="{{ old('venue') }}" maxlength="150" placeholder="Board room / SACCO hall" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                </div>

                @if ($isAgm)
                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Quorum</h3>
                        <p class="text-xs text-slate-500 mt-1">Quorum is {{ $quorumPercent }}% of members (Settings). It is calculated automatically from the attendance figures below.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Members present</label>
                            <input type="number" min="0" name="members_present" value="{{ old('members_present') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Total paid-up members</label>
                            <input type="number" min="0" name="members_total" value="{{ old('members_total') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Dividend on shares (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="dividend_on_shares" value="{{ old('dividend_on_shares') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Interest on savings (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="interest_on_savings" value="{{ old('interest_on_savings') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md">
                    <label class="flex items-start gap-3">
                        <input type="hidden" name="quorum_met" value="0">
                        <input type="checkbox" name="quorum_met" value="1" {{ old('quorum_met') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300 text-sacco focus:ring-sacco">
                        <span>
                            <span class="block text-sm font-medium text-slate-700 dark:text-slate-200">Quorum was met</span>
                            <span class="block text-xs text-slate-500">Tick if the minimum number of board members required by the constitution was present.</span>
                        </span>
                    </label>
                </div>
                @endif

                <div class="bg-slate-50 dark:bg-slate-800/50 p-5 rounded-md space-y-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Agenda</label>
                        <textarea name="agenda" rows="4" maxlength="5000" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('agenda') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Attendance</label>
                        <textarea name="attendance" rows="3" maxlength="5000" placeholder="Names and titles of those present" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('attendance') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Absent with apology</label>
                        <textarea name="absent_with_apology" rows="2" maxlength="2000" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('absent_with_apology') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Resolutions</label>
                        <textarea name="resolutions" rows="4" maxlength="5000" placeholder="Resolution number and wording, mover and seconder" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('resolutions') }}</textarea>
                        <p class="text-xs text-slate-500 mt-1">Minutes cannot be approved until the resolutions are recorded.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Action items</label>
                        <textarea name="action_items" rows="3" maxlength="5000" placeholder="Action, owner and due date" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('action_items') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex justify-end gap-3">
                <a href="{{ route('governance.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition">Save Minutes</button>
            </div>
        </form>
    </div>
</div>
@endsection
