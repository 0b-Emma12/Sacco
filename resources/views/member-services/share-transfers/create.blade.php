@extends('layouts.app')
@section('title', 'New Share Transfer')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Member Services / ST02</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">New Share Transfer</h1>
            <p class="text-sm text-slate-500 mt-1">Share price UGX {{ number_format($sharePrice, 2) }} per share (from settings).</p>
        </div>
        <a href="{{ route('member-services.share-transfers.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to transfers</a>
    </div>

    @if ($errors->any())
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl text-sm font-semibold">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        <form action="{{ route('member-services.share-transfers.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Transferor (member giving shares)</label>
                    <select name="transferor_member_id" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                        <option value="">— Select member —</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ (string) old('transferor_member_id', $selectedTransferor) === (string) $member->id ? 'selected' : '' }}>
                                {{ $member->membership_number }} — {{ $member->first_name }} {{ $member->last_name }} (shares UGX {{ number_format($member->share_capital, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Transferee (member receiving shares)</label>
                    <select name="transferee_member_id" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                        <option value="">— Select member —</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ (string) old('transferee_member_id') === (string) $member->id ? 'selected' : '' }}>
                                {{ $member->membership_number }} — {{ $member->first_name }} {{ $member->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Number of shares</label>
                    <input type="number" name="shares_count" value="{{ old('shares_count') }}" step="0.0001" min="0.0001" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    <p class="text-[11px] text-slate-400 mt-1">Value = shares × UGX {{ number_format($sharePrice, 2) }}.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Consideration paid (UGX, optional)</label>
                    <input type="number" name="consideration" value="{{ old('consideration') }}" step="0.01" min="0" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">
                    <p class="text-[11px] text-slate-400 mt-1">Settled privately between members; the society only moves the shares.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Reason for transfer</label>
                <textarea name="reason" rows="3" maxlength="2000" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100">{{ old('reason') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('member-services.share-transfers.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">Submit Transfer</button>
            </div>
        </form>
    </div>

    <div class="text-xs text-slate-500 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
        Share capital is non-withdrawable equity. Once approved, the transfer is posted to the share capital ledger (3000) and both members' capital positions are refreshed. Pending transfers reserve the shares, so they cannot be transferred twice.
    </div>
</div>
@endsection
