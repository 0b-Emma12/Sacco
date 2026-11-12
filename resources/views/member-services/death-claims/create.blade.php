@extends('layouts.app')
@section('title', 'Register Death Claim')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Member Services / ST01</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Register Death Claim</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $member->first_name }} {{ $member->middle_name }} {{ $member->last_name }} — {{ $member->membership_number }}</p>
        </div>
        <a href="{{ route('members.show', $member) }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to member</a>
    </div>

    @if ($errors->any())
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl text-sm font-semibold">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
            <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4">Claimant particulars</h2>

            <form action="{{ route('member-services.death-claims.store', $member) }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Date of death</label>
                        <input type="date" name="date_of_death" value="{{ old('date_of_death') }}" max="{{ now()->toDateString() }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Death certificate number</label>
                        <input type="text" name="death_certificate_number" value="{{ old('death_certificate_number') }}" maxlength="100" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Claimant full name</label>
                        <input type="text" name="claimant_name" value="{{ old('claimant_name') }}" maxlength="150" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Claimant National ID (NIN)</label>
                        <input type="text" name="claimant_nin" value="{{ old('claimant_nin') }}" maxlength="50" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Relationship to the deceased</label>
                        <input type="text" name="claimant_relationship" value="{{ old('claimant_relationship') }}" maxlength="60" placeholder="Widow, son, administrator…" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Claimant phone</label>
                        <input type="text" name="claimant_phone" value="{{ old('claimant_phone') }}" maxlength="30" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                </div>

                <div>
                    <span class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-2">Documents received</span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($documents as $key => $label)
                            <label class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 rounded-lg px-3 py-2">
                                <input type="checkbox" name="documents[]" value="{{ $key }}" class="mt-0.5 rounded border-slate-300 text-sacco" {{ in_array($key, old('documents', []), true) ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('members.show', $member) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">Register Claim</button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 dark:bg-slate-900 dark:border-slate-800">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Entitlement (live)</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Savings</dt><dd class="font-semibold">{{ number_format($entitlement['savings_balance'], 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Share capital</dt><dd class="font-semibold">{{ number_format($entitlement['share_balance'], 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Loan outstanding</dt><dd class="font-semibold text-[#FF4D6B]">({{ number_format($entitlement['loan_outstanding'], 2) }})</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Guarantees</dt><dd class="font-semibold text-[#FF4D6B]">({{ number_format($entitlement['guarantee_liability'], 2) }})</dd></div>
                    <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-2">
                        <dt class="font-semibold">Net payable</dt>
                        <dd class="font-bold text-[#38CE3C]">UGX {{ number_format($entitlement['net_payable'], 2) }}</dd>
                    </div>
                </dl>
            </div>

            @if ($member->nextOfKins->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 dark:bg-slate-900 dark:border-slate-800">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Next of kin on record</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($member->nextOfKins as $kin)
                        <li class="text-slate-600 dark:text-slate-300">{{ $kin->name }} <span class="text-slate-400">({{ $kin->relationship }})</span></li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
