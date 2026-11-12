@extends('layouts.app')

@section('title', 'Open Account')

@section('content')
<div class="mx-auto w-full max-w-3xl flex flex-col gap-6">

    <div class="flex items-center justify-between gap-4">
        <div>
            <a href="{{ $selectedMemberId ? route('members.show', $selectedMemberId) : route('accounts.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#38CE3C] mb-2">
                <x-icon name="arrow-left" class="w-3.5 h-3.5"/> Back
            </a>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Open a Savings Account</h1>
            <p class="text-sm text-slate-500 mt-1">Choose the member and the product to open a new ledger account.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] p-4 rounded-xl text-xs font-semibold border border-[#FF4D6B]/20">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('accounts.store') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 dark:bg-slate-900 dark:border-slate-800 flex flex-col gap-6">
        @csrf

        <div>
            <label for="member_id" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Member</label>
            <select id="member_id" name="member_id" required class="w-full h-10 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
                <option value="">Select a member...</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" {{ (string) $selectedMemberId === (string) $member->id ? 'selected' : '' }}>
                        {{ $member->membership_number }} — {{ $member->first_name }} {{ $member->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Account Product</span>

            @if($products->isEmpty())
                <p class="text-sm text-slate-500 bg-slate-50 rounded-xl p-4 dark:bg-slate-800/50">
                    This member already holds every available product type.
                </p>
            @else
                <div class="product-list grid gap-3 sm:grid-cols-2">
                    @foreach($products as $product)
                    <label class="flex items-center gap-3 p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800 transition">
                        <input type="radio"
                               name="product_id"
                               value="{{ $product->id }}"
                               class="w-4 h-4 text-sacco border-slate-300 focus:ring-sacco dark:border-slate-600 dark:bg-slate-700"
                               data-fixed="{{ str_contains(strtolower($product->name), 'fixed') ? '1' : '0' }}"
                               required
                               {{ (string) old('product_id') === (string) $product->id ? 'checked' : '' }}>
                        <span class="flex flex-col">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $product->name }}</span>
                            <span class="text-[11px] text-slate-400">{{ str_contains(strtolower($product->name), 'fixed') ? 'Term deposit with maturity date' : 'Instant access savings account' }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>

                <div class="fixed-fields mt-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-700">
                    <p class="text-xs font-semibold text-slate-500 mb-3">Fixed / term deposit settings</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="term_months" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Term (Months)</label>
                            <select id="term_months" name="term_months" class="w-full h-10 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-900 dark:border-slate-700 dark:text-slate-200">
                                <option value="">Select term</option>
                                @foreach([3, 6, 12] as $months)
                                    <option value="{{ $months }}" {{ (string) old('term_months') === (string) $months ? 'selected' : '' }}>{{ $months }} Months</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="interest_rate" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Interest Rate (%)</label>
                            <input id="interest_rate" type="number" step="0.01" name="interest_rate" value="{{ old('interest_rate') }}" placeholder="e.g. 5.5" class="w-full h-10 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] dark:bg-slate-900 dark:border-slate-700 dark:text-slate-200">
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
            <a href="{{ $selectedMemberId ? route('members.show', $selectedMemberId) : route('accounts.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition text-center dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-[#38CE3C] hover:bg-[#2cb030] rounded-xl shadow-sm transition">
                Create Account
            </button>
        </div>
    </form>
</div>
@endsection
