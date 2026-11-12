@extends('layouts.app')
@section('title', 'New Loan Application')

@php
    $selectedProduct = $products->firstWhere('id', (int) request('product_id', old('product_id'))) ?? $products->first();
    $previewPrincipal = (float) request('principal_amount', old('principal_amount', 0));
    $previewTerm = (int) request('term_months', old('term_months', 6));
    $previewRate = $selectedProduct ? (float) $selectedProduct->interest_rate_monthly : 0.0;
    $previewTotal = 0.0;
    if ($previewPrincipal > 0 && $previewTerm > 0) {
        if ($previewRate > 0) {
            $monthlyRate = $previewRate / 100;
            $growth = pow(1 + $monthlyRate, $previewTerm);
            $emi = $previewPrincipal * $monthlyRate * $growth / ($growth - 1);
            $previewTotal = $emi * $previewTerm;
        } else {
            $previewTotal = $previewPrincipal;
        }
    }
    $previewMonthly = $previewTerm > 0 ? $previewTotal / $previewTerm : 0;
@endphp

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Loans</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">New Loan Application</h1>
        </div>
        <a href="{{ route('loans.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Loans</a>
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

        <form action="{{ route('loans.store') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Loan Details -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b pb-2 mb-4">1. Loan Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Loan Product</label>
                        <select name="product_id" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none bg-white" required>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ (int) request('product_id', old('product_id')) === (int) $product->id || (! request('product_id') && ! old('product_id') && $loop->first) ? 'selected' : '' }}>
                                    {{ $product->name }} — {{ number_format((float) $product->interest_rate_monthly, 2) }}% / month, up to {{ $product->max_tenure_months }} months, {{ number_format((float) $product->multiplier_factor, 2) }}x savings
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">A non-refundable application fee of UGX {{ number_format($applicationFee, 2) }} is charged on submission.</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Applicant Member</label>
                        <select name="member_id" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none bg-white" required>
                            <option value="">Select an active member...</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ request('member_id', old('member_id')) == $m->id ? 'selected' : '' }}>{{ $m->membership_number }} - {{ $m->first_name }} {{ $m->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Principal Amount (UGX)</label>
                        <input type="number" name="principal_amount" id="principal_amount" value="{{ request('principal_amount', old('principal_amount')) }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required min="{{ (int) ($selectedProduct->min_amount ?? 1000) }}">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Term (Months)</label>
                        <input type="number" name="term_months" id="term_months" value="{{ request('term_months', old('term_months', 6)) }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required min="1" max="{{ (int) ($selectedProduct->max_tenure_months ?? 60) }}">
                    </div>
                </div>

                <div class="mt-6 p-4 bg-[#38CE3C]/10 rounded-xl border border-[#38CE3C]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-[#38CE3C]">Expected Total Repayment</h4>
                        <p class="text-xs text-[#38CE3C]/70 mt-1">Principal + Total Interest over the full term</p>
                    </div>
                    <div class="text-right">
                        <div class="text-xl font-black text-[#38CE3C]">
                            UGX {{ number_format($previewTotal, 2) }}
                        </div>
                        @if($previewMonthly > 0)
                            <div class="text-[11px] font-semibold text-[#38CE3C]/70">
                                ≈ UGX {{ number_format($previewMonthly, 2) }} / month
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-3 flex justify-end">
                    <button type="submit" formaction="{{ route('loans.create') }}" formmethod="get" formnovalidate class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-sacco border border-sacco/30 bg-sacco/5 rounded-lg hover:bg-sacco/10 transition">
                        <x-icon name="refresh-cw" class="w-3.5 h-3.5"/> Calculate repayment
                    </button>
                </div>
            </div>

            <!-- Guarantor Matrix -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b pb-2 mb-4">2. Guarantor Pledges (Social Collateral)</h3>
                <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-lg mb-4 dark:bg-blue-900/20 dark:border-blue-900/50">
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        <x-icon name="info" class="w-4 h-4 inline mr-1"/>
                        The selected guarantor's pledged savings will be <strong>frozen</strong> upon submission and cannot be withdrawn until the loan is repaid.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-100 dark:bg-slate-800/50 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Primary Guarantor</label>
                        <select name="guarantor_1_id" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none bg-white" required>
                            <option value="">Select a guarantor...</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ request('guarantor_1_id', old('guarantor_1_id')) == $member->id ? 'selected' : '' }}>{{ $member->first_name }} {{ $member->last_name }} (Savings: UGX {{ number_format($member->total_savings ?? 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Pledge Amount (UGX)</label>
                        <input type="number" name="guarantor_1_amount" value="{{ request('guarantor_1_amount', old('guarantor_1_amount')) }}" placeholder="Amount to freeze..." class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required min="1">
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-start gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <input type="checkbox" name="guarantor_1_consent" value="1" class="mt-0.5" required {{ old('guarantor_1_consent') ? 'checked' : '' }}>
                            <span>The guarantor has been informed of this pledge. Their signed consent (form L03) must still be recorded on the loan file before approval, and their savings will be frozen until the loan is fully repaid.</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('loans.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition flex items-center gap-2">
                    <x-icon name="check-circle" class="w-4 h-4"/> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
