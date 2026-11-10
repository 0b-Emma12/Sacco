@extends('layouts.app')
@section('title', 'New Loan Application')

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
            <div class="bg-rose-50 text-rose-600 p-4 rounded-md mb-6 text-sm">
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
                        <label class="block text-xs font-medium text-slate-500 mb-1">Applicant Member</label>
                        <select name="member_id" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none bg-white" required>
                            <option value="">Select an active member...</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ old('member_id', request('member_id')) == $m->id ? 'selected' : '' }}>{{ $m->membership_number }} - {{ $m->first_name }} {{ $m->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Principal Amount (UGX)</label>
                        <input type="number" name="principal_amount" id="principal_amount" value="{{ old('principal_amount') }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required min="1000">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Term (Months)</label>
                        <input type="number" name="term_months" id="term_months" value="{{ old('term_months', 6) }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required min="1" max="60">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Interest Rate (% per month)</label>
                        <input type="number" step="0.1" name="interest_rate" id="interest_rate" value="{{ old('interest_rate', 5.0) }}" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-emerald-50 rounded-lg border border-emerald-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-emerald-800">Expected Total Repayment</h4>
                        <p class="text-xs text-emerald-600 mt-1">Principal + Total Interest over the full term</p>
                    </div>
                    <div class="text-xl font-black text-emerald-700" id="total_repayment_display">
                        UGX 0
                    </div>
                </div>
            </div>

            <!-- Guarantor Matrix -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b pb-2 mb-4">2. Guarantor Pledges (Social Collateral)</h3>
                <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-lg mb-4 dark:bg-blue-900/20 dark:border-blue-900/50">
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                        The selected guarantor's pledged savings will be <strong>frozen</strong> upon submission and cannot be withdrawn until the loan is repaid.
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-lg border border-slate-100 dark:bg-slate-800/50 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Primary Guarantor</label>
                        <select name="guarantor_1_id" class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none bg-white" required>
                            <option value="">Select a guarantor...</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }} (Savings: UGX {{ number_format($member->total_savings ?? 0) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Pledge Amount (UGX)</label>
                        <input type="number" name="guarantor_1_amount" value="{{ old('guarantor_1_amount') }}" placeholder="Amount to freeze..." class="w-full border border-slate-200 rounded px-3 py-2 text-sm focus:ring-1 focus:ring-sacco outline-none" required min="1">
                    </div>
                </div>
                <button type="button" class="mt-3 text-xs font-semibold text-sacco hover:text-sacco-dark flex items-center gap-1">
                    <i data-lucide="plus" class="w-3 h-3"></i> Add another guarantor
                </button>
            </div>

            <div class="pt-6 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('loans.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded hover:bg-slate-200 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-sacco rounded hover:bg-sacco-dark transition flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const principalInput = document.getElementById('principal_amount');
    const termInput = document.getElementById('term_months');
    const rateInput = document.getElementById('interest_rate');
    const display = document.getElementById('total_repayment_display');

    function calculateTotal() {
        const principal = parseFloat(principalInput.value) || 0;
        const term = parseFloat(termInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;

        let total = 0;
        if (principal > 0 && term > 0) {
            if (rate > 0) {
                // SACCO Reducing Balance EMI Calculation
                const r = rate / 100; // Monthly rate
                const emi = principal * r * Math.pow(1 + r, term) / (Math.pow(1 + r, term) - 1);
                total = emi * term;
            } else {
                total = principal;
            }
        }

        display.textContent = 'UGX ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    principalInput.addEventListener('input', calculateTotal);
    termInput.addEventListener('input', calculateTotal);
    rateInput.addEventListener('input', calculateTotal);
    
    // Initial calculation
    calculateTotal();
});
</script>
@endsection
