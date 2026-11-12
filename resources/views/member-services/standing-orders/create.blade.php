@extends('layouts.app')
@section('title', 'New Standing Order')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-4xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Member Services / S05</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">New Standing Order Mandate</h1>
            <p class="text-sm text-slate-500 mt-1">A recurring authority to collect from the member and apply to savings or a loan.</p>
        </div>
        <a href="{{ route('member-services.standing-orders.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to mandates</a>
    </div>

    @if ($errors->any())
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 p-4 rounded-xl text-sm font-semibold">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
        <form action="{{ route('member-services.standing-orders.create') }}" method="GET" class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Member</label>
                <select name="member" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                    <option value="">— Select an active member —</option>
                    @foreach ($members as $option)
                        <option value="{{ $option->id }}" {{ $member && $member->id === $option->id ? 'selected' : '' }}>
                            {{ $option->membership_number }} — {{ $option->first_name }} {{ $option->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-slate-700 rounded-xl hover:bg-slate-800 transition">Load Member</button>
        </form>
    </div>

    @if ($member)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 p-6 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Member</div>
                    <div class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ $member->first_name }} {{ $member->last_name }}</div>
                    <div class="text-xs text-slate-500">{{ $member->membership_number }} · Savings UGX {{ number_format($member->total_savings, 2) }} · Shares UGX {{ number_format($member->share_capital, 2) }}</div>
                </div>
                <a href="{{ route('member-services.standing-orders.create') }}" class="text-xs font-semibold text-[#38CE3C] hover:underline">Change member</a>
            </div>

            <form action="{{ route('member-services.standing-orders.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="member_id" value="{{ $member->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Destination</label>
                        <select name="destination_type" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                            <option value="savings" {{ old('destination_type', 'savings') === 'savings' ? 'selected' : '' }}>Savings account</option>
                            <option value="loan" {{ old('destination_type') === 'loan' ? 'selected' : '' }}>Loan repayment</option>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Signposted from S05: recurring savings or loan instalments.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Amount per cycle (UGX)</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="1" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Savings account <span class="text-slate-400">(for savings destination)</span></label>
                        <select name="account_id" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select an account —</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" {{ (string) old('account_id') === (string) $account->id ? 'selected' : '' }}>
                                    {{ $account->account_number }} — {{ $account->product->name ?? 'Savings' }} (UGX {{ number_format($account->balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Loan <span class="text-slate-400">(for loan destination)</span></label>
                        <select name="loan_id" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                            <option value="">— Select a loan —</option>
                            @foreach ($loans as $loan)
                                <option value="{{ $loan->id }}" {{ (string) old('loan_id') === (string) $loan->id ? 'selected' : '' }}>
                                    Loan #{{ $loan->id }} — {{ ucfirst(str_replace('_', ' ', $loan->status)) }} — outstanding UGX {{ number_format($loan->outstanding_balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Frequency</label>
                        <select name="frequency" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                            @foreach (\App\Models\StandingOrder::FREQUENCIES as $value => $label)
                                <option value="{{ $value }}" {{ old('frequency', 'MONTHLY') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Source of funds</label>
                        <select name="source" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                            @foreach (\App\Models\StandingOrder::SOURCES as $value => $label)
                                <option value="{{ $value }}" {{ old('source', 'SALARY') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">First collection date</label>
                        <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">End date <span class="text-slate-400">(optional)</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payer / employer <span class="text-slate-400">(optional)</span></label>
                        <input type="text" name="payer_details" value="{{ old('payer_details') }}" maxlength="150" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Payer reference <span class="text-slate-400">(optional)</span></label>
                        <input type="text" name="payer_reference" value="{{ old('payer_reference') }}" maxlength="80" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Member instructions</label>
                    <textarea name="member_instructions" rows="3" maxlength="2000" class="w-full border border-slate-200 dark:border-slate-700 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none dark:bg-slate-900 dark:text-slate-100" placeholder="Any conditions recorded on the mandate">{{ old('member_instructions') }}</textarea>
                </div>

                <label class="flex items-start gap-3 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="authorised" value="1" {{ old('authorised') ? 'checked' : '' }} class="mt-1" required>
                    <span>The member has signed the S05 mandate authorising these recurring deductions, and understands the mandate stays pending until a manager authorises it.</span>
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('member-services.standing-orders.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm">Register Mandate</button>
                </div>
            </form>
        </div>
    @endif

    <div class="text-xs text-slate-500 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
        A registered mandate stays <span class="font-semibold">pending</span> until the Administrator or Manager authorises it. Once active, the daily scheduler posts each due cycle exactly once — the next run date advances inside the same transaction that records the run.
    </div>
</div>
@endsection
