@extends('layouts.app')
@section('title', 'Loan Details')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <x-page-header
        eyebrow="Loans"
        title="Loan APP-{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }}"
        subtitle="Application, appraisal, guarantees and repayment record.">
        <a href="{{ route('loans.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2">
            <x-icon name="arrow-left" class="w-4 h-4"/> Back to loans
        </a>
    </x-page-header>

    @if($loan->disbursed_at || $loan->status === 'paid')
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 dark:bg-[#181824] dark:border-[#272738] flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-2">Loan documents</span>
            @if($loan->disbursed_at)
                <a href="{{ route('prints.loans.disbursement', $loan) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Disbursement voucher</a>
            @endif
            @if($loan->status === 'paid')
                <a href="{{ route('prints.loans.clearance', $loan) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Clearance certificate</a>
                <a href="{{ route('prints.loans.guarantor-release', $loan) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Guarantor release</a>
            @endif
        </div>
    @endif

    @if (session('success'))
        <div class="bg-[#38CE3C]/10 text-[#38CE3C] p-4 rounded-xl text-sm border border-[#38CE3C]/20 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('action'))
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] p-4 rounded-xl text-sm border border-[#FF4D6B]/20 font-semibold">
            {{ $errors->first('action') }}
        </div>
    @endif

    @php
        $restructureKeys = ['restructuring', 'write_off', 'reason', 'new_tenure_months', 'grace_period_months', 'notes', 'decision_notes', 'recovery_efforts', 'amount_recoverable'];
    @endphp
    @if (collect($restructureKeys)->contains(fn ($k) => $errors->has($k)))
        <div class="bg-[#FF4D6B]/10 text-[#FF4D6B] p-4 rounded-xl text-sm border border-[#FF4D6B]/20">
            @foreach ($restructureKeys as $k)
                @if ($errors->has($k))<div>{{ $errors->first($k) }}</div>@endif
            @endforeach
        </div>
    @endif

    @php
        $kpiTotalRepayable = (float) $loan->total_repayable;
        $kpiTotalPaid = (float) $loan->repayments->sum('amount_paid');
        $kpiOutstanding = \App\Services\RepaymentScheduleService::outstanding($loan);
    @endphp

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Principal" :value="'UGX '.number_format($loan->principal_amount, 2)" meta="Amount requested" icon="credit-card" tone="blue"/>
        <x-stat-card label="Total repayable" :value="'UGX '.number_format($kpiTotalRepayable, 2)" meta="{{ $loan->term_months }} months at {{ $loan->interest_rate }}%" icon="receipt" tone="purple"/>
        <x-stat-card label="Paid to date" :value="'UGX '.number_format($kpiTotalPaid, 2)" meta="Repayments received" icon="check-circle" tone="green"/>
        <x-stat-card label="Outstanding" :value="'UGX '.number_format($kpiOutstanding, 2)" meta="Balance remaining" icon="clock" tone="coral"/>
    </div>

    <!-- Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 dark:bg-[#181824] dark:border-[#272738]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <div>
                <div class="text-xs text-slate-500 mb-1">Applicant</div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">
                    <a href="{{ route('members.show', $loan->member->id) }}" class="text-[#38CE3C] hover:underline">
                        {{ $loan->member->first_name }} {{ $loan->member->last_name }}
                    </a>
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Principal Amount</div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">UGX {{ number_format($loan->principal_amount, 2) }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Term & Rate</div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $loan->term_months }} Months @ {{ $loan->interest_rate }}%</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Total Repayment</div>
                <div class="font-semibold text-[#38CE3C]">UGX {{ number_format($loan->total_repayable, 2) }}</div>
            </div>
                    <div>
                <div class="text-xs text-slate-500 mb-1">Status</div>
                @php
                    $statusClass = match($loan->status) {
                        'approved', 'disbursed' => 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20',
                        'rejected', 'defaulted', 'written_off' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20',
                        'under_review', 'submitted' => 'bg-[#FFDE73]/30 text-amber-700 dark:text-amber-300 border border-amber-300/30',
                        default => 'bg-[#8E32E9]/15 text-[#8E32E9] border border-[#8E32E9]/20'
                    };
                @endphp
                <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                </span>
            </div>
        </div>
        
        <!-- Action Buttons (Admin/Manager) -->
        @if($loan->status === 'under_review')
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-4">
            @if (Auth::user()->canApproveLoans())
            @if ($errors->has('rejection_reason'))
                <div class="text-[#FF4D6B] text-sm font-medium">{{ $errors->first('rejection_reason') }}</div>
            @endif
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="px-4 py-2.5 bg-[#38CE3C] text-white text-sm font-semibold rounded-xl hover:bg-[#2cb030] transition flex items-center gap-2 shadow-sm">
                        <x-icon name="check" class="w-4 h-4"/> Approve Loan
                    </button>
                </form>
                <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="reject">
                    <input type="text" name="rejection_reason" placeholder="Reason for rejection (Required)" class="px-3 py-2 text-sm border border-slate-200 rounded-xl outline-none focus:border-[#FF4D6B] focus:ring-2 focus:ring-[#FF4D6B]/20 w-64 transition" required>
                    <button type="submit" class="px-4 py-2.5 bg-[#FF4D6B] text-white text-sm font-semibold rounded-xl hover:bg-[#FF4D6B]/90 transition flex items-center gap-2">
                        <x-icon name="x" class="w-4 h-4"/> Reject
                    </button>
                </form>
            </div>
            @else
            <p class="text-sm text-slate-500">This application is awaiting a credit decision by an administrator or manager.</p>
            @endif
        </div>
        @elseif($loan->status === 'approved')
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
            @if (Auth::user()->canDisburseLoans())
            <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="disburse">
                <button type="submit" class="px-4 py-2.5 bg-[#8E32E9] text-white text-sm font-semibold rounded-xl hover:bg-purple-700 transition flex items-center gap-2 shadow-sm">
                    <x-icon name="send" class="w-4 h-4"/> Disburse Loan to Member
                </button>
            </form>
            @else
            <p class="text-sm text-slate-500">This loan is approved and awaiting disbursement by finance.</p>
            @endif
        </div>
        @elseif($loan->status === 'rejected' && $loan->rejection_reason)
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="p-4 bg-[#FF4D6B]/10 rounded-xl border border-[#FF4D6B]/20">
                <h4 class="text-sm font-bold text-[#FF4D6B] mb-1">Rejection Reason</h4>
                <p class="text-sm text-[#FF4D6B]/80">{{ $loan->rejection_reason }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Credit Appraisal (L02) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="w-9 h-9 rounded-lg bg-[#8E32E9]/10 text-[#8E32E9] flex items-center justify-center shrink-0">
                    <x-icon name="bar-chart-3" class="w-4 h-4"/>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Credit Appraisal</h3>
                    <p class="text-xs text-slate-500 mt-1">L02 — financial analysis, risk rating and recommendation from the credit officer.</p>
                </div>
            </div>
            @if($loan->appraisal)
                <div class="text-xs text-slate-500 sm:text-right">
                    Appraised {{ $loan->appraisal->appraised_at->format('d M Y') }}<br>by {{ $loan->appraisal->appraised_by }}
                </div>
            @endif
        </div>

        @php
            $appraisalKeys = ['appraisal', 'monthly_income', 'monthly_expenses', 'existing_monthly_obligations', 'risk_rating', 'recommendation', 'recommended_amount', 'recommended_tenure_months', 'conditions', 'remarks'];
        @endphp
        @if(collect($appraisalKeys)->contains(fn ($k) => $errors->has($k)))
            <div class="mx-6 mt-6 p-3 bg-[#FF4D6B]/10 text-[#FF4D6B] rounded-xl text-sm border border-[#FF4D6B]/20">
                @foreach($appraisalKeys as $k)
                    @if($errors->has($k))<div>{{ $errors->first($k) }}</div>@endif
                @endforeach
            </div>
        @endif

        @if($loan->appraisal)
            @php $a = $loan->appraisal; @endphp
            <div class="p-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                    <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Monthly Income</div>
                    <div class="text-base font-bold text-slate-700 dark:text-slate-200 mt-1">UGX {{ number_format($a->monthly_income) }}</div>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                    <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Monthly Expenses</div>
                    <div class="text-base font-bold text-slate-700 dark:text-slate-200 mt-1">UGX {{ number_format($a->monthly_expenses) }}</div>
                </div>
                <div class="p-4 {{ (float) $a->debt_service_ratio > 40 ? 'bg-[#FF4D6B]/10' : 'bg-[#38CE3C]/10' }} rounded-xl">
                    <div class="text-xs font-bold tracking-wider uppercase {{ (float) $a->debt_service_ratio > 40 ? 'text-[#FF4D6B]' : 'text-[#38CE3C]' }}">Debt Service Ratio</div>
                    <div class="text-base font-bold mt-1 {{ (float) $a->debt_service_ratio > 40 ? 'text-[#FF4D6B]' : 'text-[#38CE3C]' }}">{{ number_format($a->debt_service_ratio, 2) }}%</div>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                    <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Guarantor Coverage</div>
                    <div class="text-base font-bold text-slate-700 dark:text-slate-200 mt-1">UGX {{ number_format($a->guarantor_coverage) }}</div>
                </div>
            </div>
            <div class="px-6 pb-6 flex flex-col gap-4">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $a->risk_rating === 'LOW' ? 'bg-[#38CE3C]/15 text-[#38CE3C]' : ($a->risk_rating === 'HIGH' ? 'bg-[#FF4D6B]/15 text-[#FF4D6B]' : 'bg-[#FFDE73]/30 text-amber-700') }}">
                        Risk: {{ $a->risk_rating }}
                    </span>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $a->allowsApproval() ? 'bg-[#38CE3C]/15 text-[#38CE3C]' : 'bg-[#FF4D6B]/15 text-[#FF4D6B]' }}">
                        {{ str_replace('_', ' ', $a->recommendation) }}
                    </span>
                    @if($a->recommended_amount)
                        <span class="text-slate-600 dark:text-slate-400">Recommended: <strong>UGX {{ number_format($a->recommended_amount) }}</strong> over {{ $a->recommended_tenure_months }} months</span>
                    @endif
                    <span class="text-slate-500">Collateral held: UGX {{ number_format($a->collateral_value) }}</span>
                </div>
                @if($a->conditions)
                    <div class="p-3 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                        <div class="text-xs font-bold text-slate-500 uppercase mb-1">Conditions</div>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ $a->conditions }}</p>
                    </div>
                @endif
                @if($a->remarks)
                    <p class="text-sm text-slate-500"><span class="font-semibold">Remarks:</span> {{ $a->remarks }}</p>
                @endif
            </div>
        @endif

        @if(in_array($loan->status, ['submitted', 'under_review']) && Auth::user()->canAppraiseLoans())
            <form method="POST" action="{{ route('loans.appraise', $loan->id) }}" class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                @csrf
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">{{ $loan->appraisal ? 'Revise Appraisal' : 'Record Appraisal' }}</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Monthly Income (UGX) *</label>
                        <input type="number" name="monthly_income" step="1" min="1" value="{{ old('monthly_income', $loan->appraisal->monthly_income ?? '') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Monthly Expenses (UGX) *</label>
                        <input type="number" name="monthly_expenses" step="1" min="0" value="{{ old('monthly_expenses', $loan->appraisal->monthly_expenses ?? 0) }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Other Monthly Obligations (UGX)</label>
                        <input type="number" name="existing_monthly_obligations" step="1" min="0" value="{{ old('existing_monthly_obligations', $loan->appraisal->existing_monthly_obligations ?? 0) }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Risk Rating *</label>
                        <select name="risk_rating" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                            @foreach(['LOW' => 'Low', 'MEDIUM' => 'Medium', 'HIGH' => 'High'] as $value => $label)
                                <option value="{{ $value }}" {{ old('risk_rating', $loan->appraisal->risk_rating ?? 'MEDIUM') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Recommendation *</label>
                        <select name="recommendation" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                            @foreach(['APPROVE' => 'Approve', 'APPROVE_WITH_CONDITIONS' => 'Approve with conditions', 'REJECT' => 'Reject'] as $value => $label)
                                <option value="{{ $value }}" {{ old('recommendation', $loan->appraisal->recommendation ?? 'APPROVE') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Recommended Amount (UGX)</label>
                        <input type="number" name="recommended_amount" step="1" min="1" max="{{ $loan->principal_amount }}" value="{{ old('recommended_amount', $loan->appraisal->recommended_amount ?? $loan->principal_amount) }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Recommended Tenure (months)</label>
                        <input type="number" name="recommended_tenure_months" step="1" min="1" max="{{ $loan->term_months }}" value="{{ old('recommended_tenure_months', $loan->appraisal->recommended_tenure_months ?? $loan->term_months) }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Conditions (required when approving with conditions)</label>
                        <input type="text" name="conditions" maxlength="1000" value="{{ old('conditions', $loan->appraisal->conditions ?? '') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Remarks</label>
                        <textarea name="remarks" rows="2" maxlength="1000" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">{{ old('remarks', $loan->appraisal->remarks ?? '') }}</textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="h-10 px-6 bg-[#8E32E9] text-white rounded-xl hover:bg-purple-700 transition font-semibold text-sm shadow-sm">
                        {{ $loan->appraisal ? 'Update Appraisal' : 'Submit Appraisal' }}
                    </button>
                </div>
            </form>
        @endif
    </div>

    <!-- Guarantors Matrix -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                <x-icon name="users" class="w-4 h-4"/>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Guarantor Pledges</h3>
                <p class="text-xs text-slate-500 mt-1">Savings frozen as social collateral for this loan. Consent must be evidenced on form L03 before approval.</p>
            </div>
        </div>
        @php $consentKeys = ['consent', 'consent_method', 'guarantor_signature', 'consent_confirmed_at']; @endphp
        @if(collect($consentKeys)->contains(fn ($k) => $errors->has($k)))
            <div class="mx-6 mt-6 p-3 bg-[#FF4D6B]/10 text-[#FF4D6B] rounded-xl text-sm border border-[#FF4D6B]/20">
                @foreach($consentKeys as $k)
                    @if($errors->has($k))<div>{{ $errors->first($k) }}</div>@endif
                @endforeach
            </div>
        @endif
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/50">
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Guarantor Member</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pledged Amount</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Consent (L03)</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Record Consent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($loan->guarantorPledges as $pledge)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-5 py-4 font-semibold text-slate-800 dark:text-slate-200">
                            <a href="{{ route('members.show', $pledge->guarantor->id) }}" class="text-[#38CE3C] hover:underline">
                                {{ $pledge->guarantor->first_name }} {{ $pledge->guarantor->last_name }}
                            </a>
                            <div class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $pledge->guarantor->membership_number }}</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-400">UGX {{ number_format($pledge->pledged_amount, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#8E32E9]/15 text-[#8E32E9] border border-[#8E32E9]/20">
                                {{ ucfirst(str_replace('_', ' ', $pledge->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            @if($pledge->consent_given)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20">Consented</span>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    {{ optional($pledge->consent_confirmed_at)->format('d M Y H:i') }} · {{ $pledge->consent_sms_sent ? 'SMS confirmation' : 'Signed form' }}
                                    @if($pledge->guarantor_signature)<br>{{ $pledge->guarantor_signature }}@endif
                                </div>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30">Pending</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if(! $pledge->consent_given && in_array($loan->status, ['submitted', 'under_review'], true) && (Auth::user()->canAppraiseLoans() || Auth::user()->canDisburseLoans()))
                                <form method="POST" action="{{ route('loans.guarantors.consent', [$loan->id, $pledge->id]) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    <select name="consent_method" class="h-8 px-2 text-xs rounded-lg border border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                                        <option value="IN_PERSON">Signed form</option>
                                        <option value="SMS">SMS confirmation</option>
                                    </select>
                                    <input type="text" name="guarantor_signature" maxlength="100" placeholder="Guarantor name" required
                                           class="h-8 px-2 text-xs rounded-lg border border-slate-200 w-36 dark:bg-slate-800 dark:border-slate-700">
                                    <button type="submit" class="h-8 px-3 bg-[#38CE3C] text-white rounded-lg text-xs font-semibold hover:bg-[#2cb030] transition">Record</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="5" icon="users" title="No guarantors" message="No guarantors attached to this loan."/>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Collateral Register (C01 / C03 / C04) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-[#8E32E9]/10 text-[#8E32E9] flex items-center justify-center shrink-0">
                <x-icon name="folder" class="w-4 h-4"/>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Collateral Register</h3>
                <p class="text-xs text-slate-500 mt-1">C01 registration, C04 valuation and C03 release. A registration number can only be pledged once.</p>
            </div>
        </div>

        @php
            $collateralKeys = ['collateral', 'asset_type', 'description', 'registration_number', 'estimated_value', 'forced_sale_value', 'valuer_name', 'valuation_date', 'location'];
            $held = $loan->collaterals->filter(fn ($c) => $c->isHeld());
            $heldValue = (float) $held->sum(fn ($c) => $c->securityValue());
            $ltv = $heldValue > 0 ? ((float) $loan->principal_amount / $heldValue) * 100 : null;
        @endphp
        @if(collect($collateralKeys)->contains(fn ($k) => $errors->has($k)))
            <div class="mx-6 mt-6 p-3 bg-[#FF4D6B]/10 text-[#FF4D6B] rounded-xl text-sm border border-[#FF4D6B]/20">
                @foreach($collateralKeys as $k)
                    @if($errors->has($k))<div>{{ $errors->first($k) }}</div>@endif
                @endforeach
            </div>
        @endif

        <div class="px-5 py-4 flex flex-wrap gap-6 text-sm border-b border-slate-100 dark:border-slate-800">
            <div><span class="text-slate-500">Assets held:</span> <strong class="text-slate-700 dark:text-slate-200">{{ $held->count() }}</strong></div>
            <div><span class="text-slate-500">Security value (forced sale):</span> <strong class="text-slate-700 dark:text-slate-200">UGX {{ number_format($heldValue, 2) }}</strong></div>
            <div><span class="text-slate-500">Loan-to-value:</span> <strong class="{{ $ltv !== null && $ltv > 80 ? 'text-[#FF4D6B]' : 'text-slate-700 dark:text-slate-200' }}">{{ $ltv === null ? 'Unsecured' : number_format($ltv, 2).'%' }}</strong></div>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/50">
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Asset</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Registration No.</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Estimated</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Forced Sale</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Valuation</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($loan->collaterals as $collateral)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $collateral->asset_type }}</div>
                            <div class="text-xs text-slate-500">{{ $collateral->description }}</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-400">{{ $collateral->registration_number }}</td>
                        <td class="px-5 py-4 text-right">{{ number_format($collateral->estimated_value, 2) }}</td>
                        <td class="px-5 py-4 text-right">{{ $collateral->forced_sale_value !== null ? number_format($collateral->forced_sale_value, 2) : '—' }}</td>
                        <td class="px-5 py-4 text-slate-500 text-xs">
                            {{ $collateral->valuer_name ?: '—' }}<br>
                            {{ optional($collateral->valuation_date)->format('d M Y') ?: 'No date' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $collateral->status === 'RELEASED' ? 'bg-slate-100 text-slate-500' : ($collateral->status === 'LIENED' ? 'bg-[#8E32E9]/15 text-[#8E32E9]' : 'bg-[#38CE3C]/15 text-[#38CE3C]') }}">{{ ucfirst(strtolower($collateral->status)) }}</span>
                        </td>
                        <td class="px-5 py-4">
                            @if($collateral->isHeld() && in_array($loan->status, ['paid', 'rejected'], true) && Auth::user()->canApproveLoans())
                                <form method="POST" action="{{ route('loans.collateral.release', [$loan->id, $collateral->id]) }}">
                                    @csrf
                                    <button type="submit" class="h-8 px-3 bg-[#38CE3C] text-white rounded-lg text-xs font-semibold hover:bg-[#2cb030] transition">Release (C03)</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="7" icon="folder" title="No collateral" message="No collateral registered for this loan."/>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(! in_array($loan->status, ['paid', 'rejected'], true) && (Auth::user()->canAppraiseLoans() || Auth::user()->canDisburseLoans()))
            <form method="POST" action="{{ route('loans.collateral.store', $loan->id) }}" class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                @csrf
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">Register Collateral</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Asset Type *</label>
                        <select name="asset_type" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                            @foreach(['Land Title', 'Motor Vehicle', 'Logbook', 'Equipment', 'Livestock', 'Household Goods', 'Other'] as $type)
                                <option value="{{ $type }}" {{ old('asset_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Description *</label>
                        <input type="text" name="description" maxlength="1000" value="{{ old('description') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Registration / Title No. *</label>
                        <input type="text" name="registration_number" maxlength="100" value="{{ old('registration_number') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Estimated Value (UGX) *</label>
                        <input type="number" name="estimated_value" step="1" min="1" value="{{ old('estimated_value') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Forced Sale Value (UGX)</label>
                        <input type="number" name="forced_sale_value" step="1" min="0" value="{{ old('forced_sale_value') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Valuer Name</label>
                        <input type="text" name="valuer_name" maxlength="255" value="{{ old('valuer_name') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Valuation Date</label>
                        <input type="date" name="valuation_date" max="{{ now()->toDateString() }}" value="{{ old('valuation_date') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Location</label>
                        <input type="text" name="location" maxlength="255" value="{{ old('location') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="h-10 px-6 bg-[#8E32E9] text-white rounded-xl hover:bg-purple-700 transition font-semibold text-sm shadow-sm">Register Collateral</button>
                </div>
            </form>
        @endif
    </div>

    @php
        $activeLoan = in_array($loan->status, ['disbursed', 'partially_paid', 'defaulted'], true);
        $pendingRestructuring = $loan->restructurings->firstWhere('status', 'PENDING');
        $pendingWriteOff = $loan->writeOffs->firstWhere('status', 'PENDING');
        $maxOverdueDays = (int) $loan->schedules->max('days_overdue');
    @endphp

    <!-- Loan Restructuring (L07) -->
    @if($activeLoan || $loan->restructurings->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                <x-icon name="refresh-cw" class="w-4 h-4"/>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Loan Restructuring</h3>
                <p class="text-xs text-slate-500 mt-1">L07 — reschedule the outstanding principal after hardship. Approving replaces all unpaid installments.</p>
            </div>
        </div>

        @if($loan->restructurings->isNotEmpty())
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950/50">
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Requested</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reason</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Outstanding</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">New Terms</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Decided By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($loan->restructurings as $restructuring)
                        <tr>
                            <td class="px-5 py-3 text-slate-500">{{ $restructuring->created_at->format('d M Y') }}<div class="text-[11px] text-slate-400">{{ $restructuring->requested_by }}</div></td>
                            <td class="px-5 py-3 max-w-xs"><span class="text-slate-700 dark:text-slate-300">{{ $restructuring->reason }}</span></td>
                            <td class="px-5 py-3 text-right">{{ number_format($restructuring->outstanding_at_request, 2) }}</td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ $restructuring->new_tenure_months }} months @if($restructuring->grace_period_months) (+{{ $restructuring->grace_period_months }} grace) @endif
                                @if($restructuring->new_installment)<div class="text-xs text-slate-500">UGX {{ number_format($restructuring->new_installment, 2) }}/month</div>@endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $restructuring->status === 'APPROVED' ? 'bg-[#38CE3C]/15 text-[#38CE3C]' : ($restructuring->status === 'REJECTED' ? 'bg-[#FF4D6B]/15 text-[#FF4D6B]' : 'bg-[#FFDE73]/30 text-amber-700') }}">{{ ucfirst(strtolower($restructuring->status)) }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ $restructuring->approved_by ?: '—' }}@if($restructuring->decision_notes)<div class="text-slate-400">{{ $restructuring->decision_notes }}</div>@endif</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($pendingRestructuring && Auth::user()->canApproveLoans())
            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Decision on pending request</h4>
                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('loans.restructuring.decide', [$loan->id, $pendingRestructuring->id]) }}" class="flex flex-1 gap-2">
                        @csrf
                        <input type="hidden" name="decision" value="APPROVE">
                        <input type="text" name="decision_notes" maxlength="1000" placeholder="Approval notes (optional)" class="flex-1 h-9 px-3 rounded-lg border border-slate-200 text-xs dark:bg-slate-800 dark:border-slate-700">
                        <button type="submit" class="h-9 px-4 bg-[#38CE3C] text-white rounded-lg text-xs font-semibold hover:bg-[#2cb030] transition">Approve &amp; reschedule</button>
                    </form>
                    <form method="POST" action="{{ route('loans.restructuring.decide', [$loan->id, $pendingRestructuring->id]) }}" class="flex flex-1 gap-2">
                        @csrf
                        <input type="hidden" name="decision" value="REJECT">
                        <input type="text" name="decision_notes" maxlength="1000" placeholder="Rejection reason (required)" required class="flex-1 h-9 px-3 rounded-lg border border-slate-200 text-xs dark:bg-slate-800 dark:border-slate-700">
                        <button type="submit" class="h-9 px-4 bg-[#FF4D6B] text-white rounded-lg text-xs font-semibold hover:bg-[#FF4D6B]/90 transition">Reject</button>
                    </form>
                </div>
            </div>
        @endif

        @if($activeLoan && ! $pendingRestructuring && Auth::user()->canAppraiseLoans())
            <form method="POST" action="{{ route('loans.restructuring.store', $loan->id) }}" class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                @csrf
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Request restructuring</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Reason (hardship) *</label>
                        <input type="text" name="reason" maxlength="1000" value="{{ old('reason') }}" class="w-full h-9 px-3 rounded-lg border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">New Tenure (months) *</label>
                        <input type="number" name="new_tenure_months" min="1" max="120" value="{{ old('new_tenure_months', $loan->term_months) }}" class="w-full h-9 px-3 rounded-lg border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Grace Period (months)</label>
                        <input type="number" name="grace_period_months" min="0" max="12" value="{{ old('grace_period_months', 0) }}" class="w-full h-9 px-3 rounded-lg border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Supporting notes</label>
                        <textarea name="notes" rows="2" maxlength="1000" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="h-9 px-5 bg-[#8E32E9] text-white rounded-lg text-xs font-semibold hover:bg-purple-700 transition">Submit Request</button>
                </div>
            </form>
        @endif
    </div>
    @endif

    <!-- Loan Write-off (L08) -->
    @if($loan->status === 'written_off' || $loan->writeOffs->isNotEmpty() || ($activeLoan && ($loan->status === 'defaulted' || $maxOverdueDays >= 90)))
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-[#FF4D6B]/10 text-[#FF4D6B] flex items-center justify-center shrink-0">
                <x-icon name="alert-triangle" class="w-4 h-4"/>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Loan Write-off</h3>
                <p class="text-xs text-slate-500 mt-1">L08 — write off the unrecoverable principal after recovery and guarantee action. Requires board-level approval.</p>
            </div>
        </div>

        @if($loan->writeOffs->isNotEmpty())
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950/50">
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Requested</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Reason</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Recovery Efforts</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Written Off</th>
                            <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($loan->writeOffs as $writeOff)
                        <tr>
                            <td class="px-5 py-3 text-slate-500">{{ $writeOff->created_at->format('d M Y') }}<div class="text-[11px] text-slate-400">{{ $writeOff->requested_by }}</div></td>
                            <td class="px-5 py-3 max-w-xs text-slate-700 dark:text-slate-300">{{ $writeOff->reason }}</td>
                            <td class="px-5 py-3 text-xs text-slate-500">
                                @foreach(($writeOff->recovery_efforts ?? []) as $effort)
                                    <div>• {{ \App\Models\LoanWriteOff::RECOVERY_EFFORTS[$effort] ?? $effort }}</div>
                                @endforeach
                            </td>
                            <td class="px-5 py-3 text-right font-semibold">{{ $writeOff->write_off_amount !== null ? number_format($writeOff->write_off_amount, 2) : '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $writeOff->status === 'APPROVED' ? 'bg-[#FF4D6B]/15 text-[#FF4D6B]' : ($writeOff->status === 'REJECTED' ? 'bg-slate-100 text-slate-600' : 'bg-[#FFDE73]/30 text-amber-700') }}">{{ ucfirst(strtolower($writeOff->status)) }}</span>
                                @if($writeOff->approved_by)<div class="text-[11px] text-slate-400 mt-1">{{ $writeOff->approved_by }}</div>@endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($pendingWriteOff && Auth::user()->canApproveLoans())
            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Board decision on pending request</h4>
                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('loans.writeoff.decide', [$loan->id, $pendingWriteOff->id]) }}" class="flex flex-1 gap-2">
                        @csrf
                        <input type="hidden" name="decision" value="APPROVE">
                        <input type="text" name="decision_notes" maxlength="1000" placeholder="Resolution reference / notes" class="flex-1 h-9 px-3 rounded-lg border border-slate-200 text-xs dark:bg-slate-800 dark:border-slate-700">
                        <button type="submit" class="h-9 px-4 bg-[#FF4D6B] text-white rounded-lg text-xs font-semibold hover:bg-[#FF4D6B]/90 transition">Approve write-off</button>
                    </form>
                    <form method="POST" action="{{ route('loans.writeoff.decide', [$loan->id, $pendingWriteOff->id]) }}" class="flex flex-1 gap-2">
                        @csrf
                        <input type="hidden" name="decision" value="REJECT">
                        <input type="text" name="decision_notes" maxlength="1000" placeholder="Rejection reason (required)" required class="flex-1 h-9 px-3 rounded-lg border border-slate-200 text-xs dark:bg-slate-800 dark:border-slate-700">
                        <button type="submit" class="h-9 px-4 bg-slate-600 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 transition">Reject</button>
                    </form>
                </div>
            </div>
        @endif

        @if($activeLoan && ! $pendingWriteOff && ($loan->status === 'defaulted' || $maxOverdueDays >= 90) && Auth::user()->canAppraiseLoans())
            <form method="POST" action="{{ route('loans.writeoff.store', $loan->id) }}" class="p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
                @csrf
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Request write-off ({{ $maxOverdueDays }} days in arrears)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Reason for write-off *</label>
                        <input type="text" name="reason" maxlength="1000" value="{{ old('reason') }}" class="w-full h-9 px-3 rounded-lg border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-slate-500 block mb-2">Recovery efforts undertaken *</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach(\App\Models\LoanWriteOff::RECOVERY_EFFORTS as $key => $label)
                                <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                                    <input type="checkbox" name="recovery_efforts[]" value="{{ $key }}" {{ in_array($key, old('recovery_efforts', []), true) ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Amount recoverable (UGX)</label>
                        <input type="number" name="amount_recoverable" min="0" step="1" value="{{ old('amount_recoverable', 0) }}" class="w-full h-9 px-3 rounded-lg border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 block mb-1">Supporting notes</label>
                        <input type="text" name="notes" maxlength="1000" value="{{ old('notes') }}" class="w-full h-9 px-3 rounded-lg border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="h-9 px-5 bg-[#FF4D6B] text-white rounded-lg text-xs font-semibold hover:bg-[#FF4D6B]/90 transition">Submit for Board Approval</button>
                </div>
            </form>
        @endif
    </div>
    @endif

    <!-- Repayment Tracker -->
    @if(in_array($loan->status, ['disbursed', 'partially_paid', 'defaulted', 'paid']))
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                <x-icon name="wallet" class="w-4 h-4"/>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Loan Repayment Tracker</h3>
                <p class="text-xs text-slate-500 mt-1">Record payments and track outstanding balance.</p>
            </div>
        </div>
        @php
            $totalRepayable = (float) $loan->total_repayable;
            $totalPaid = (float) $loan->repayments->sum('amount_paid');
            $outstanding = \App\Services\RepaymentScheduleService::outstanding($loan);
            $paidPct = $totalRepayable > 0 ? min(100, ($totalPaid / $totalRepayable) * 100) : 0;
        @endphp
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-800">
            <div class="text-center p-4 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Total Repayable</div>
                <div class="text-xl font-bold text-slate-700 dark:text-slate-200 mt-1">UGX {{ number_format($totalRepayable) }}</div>
            </div>
            <div class="text-center p-4 bg-[#38CE3C]/10 rounded-xl">
                <div class="text-xs font-bold tracking-wider text-[#38CE3C] uppercase">Total Paid</div>
                <div class="text-xl font-bold text-[#38CE3C] mt-1">UGX {{ number_format($totalPaid) }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ number_format($paidPct, 1) }}% cleared</div>
            </div>
            <div class="text-center p-4 {{ $outstanding <= 0 ? 'bg-[#38CE3C]/10' : 'bg-[#FF4D6B]/10' }} rounded-xl">
                <div class="text-xs font-bold tracking-wider {{ $outstanding <= 0 ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }} uppercase">Outstanding Balance</div>
                <div class="text-xl font-bold {{ $outstanding <= 0 ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }} mt-1">UGX {{ number_format($outstanding) }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $outstanding <= 0 ? '✓ Fully Paid' : 'Remaining' }}</div>
            </div>
        </div>
        <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex justify-between text-xs text-slate-500 mb-1"><span>Progress</span><span>{{ number_format($paidPct, 1) }}%</span></div>
            <div class="w-full bg-slate-200 rounded-full h-2 dark:bg-slate-700">
                <div class="h-2 rounded-full {{ $paidPct >= 100 ? 'bg-[#38CE3C]' : 'bg-[#38CE3C]' }}" style="width: {{ $paidPct }}%"></div>
            </div>
        </div>
        @if($loan->status !== 'paid')
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Record a Repayment</h4>
            @if($errors->any())
                <div class="mb-3 p-3 bg-[#FF4D6B]/10 text-[#FF4D6B] rounded-xl text-sm border border-[#FF4D6B]/20">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('loans.repayments.store', $loan->id) }}" class="flex flex-col sm:flex-row gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-500 block mb-1">Amount Paid (UGX) *</label>
                    <input type="number" name="amount" step="1" min="1" max="{{ $outstanding }}"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] text-sm dark:bg-slate-800 dark:border-slate-700 transition" required>
                </div>
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-500 block mb-1">Narration</label>
                    <input type="text" name="narration" placeholder="e.g. Mobile money, Bank transfer"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] text-sm dark:bg-slate-800 dark:border-slate-700 transition">
                </div>
                <button type="submit" class="h-10 px-6 bg-[#38CE3C] text-white rounded-xl hover:bg-[#2cb030] transition font-semibold text-sm whitespace-nowrap shadow-sm">
                    Record Payment
                </button>
            </form>
        </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/50">
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Narration</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Amount Paid</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Balance After</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Recorded By</th>
                        <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($loan->repayments->sortByDesc('created_at') as $repayment)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-slate-500">{{ $loop->iteration }}</td>
                        <td class="px-5 py-4">{{ $repayment->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-5 py-4 text-slate-500">{{ $repayment->narration }}</td>
                        <td class="px-5 py-4 text-right font-semibold text-[#38CE3C]">+ UGX {{ number_format($repayment->amount_paid) }}</td>
                        <td class="px-5 py-4 text-right font-semibold {{ $repayment->balance_after <= 0 ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">UGX {{ number_format($repayment->balance_after) }}</td>
                        <td class="px-5 py-4 text-slate-500">{{ $repayment->recorded_by }}</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('prints.loans.repayment', [$loan, $repayment]) }}" class="text-xs font-semibold text-[#38CE3C] hover:underline">Receipt</a></td>
                    </tr>
                    @empty
                    <x-empty-state colspan="7" icon="receipt" title="No repayments" message="No repayments recorded yet."/>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($loan->schedules->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-[#8E32E9]/10 text-[#8E32E9] flex items-center justify-center shrink-0">
                <x-icon name="file-text" class="w-4 h-4"/>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Repayment Schedule</h3>
                <p class="text-xs text-slate-500 mt-1">{{ $loan->interest_method === 'FLAT' ? 'Flat interest' : 'Reducing balance' }} plan generated at disbursement.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-950/50">
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Principal</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Interest</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Total Due</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Paid</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider text-right">Days Overdue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($loan->schedules as $schedule)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-3 text-slate-500">{{ $schedule->installment_number }}</td>
                        <td class="px-5 py-3">{{ $schedule->due_date->format('d M Y') }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format((float) $schedule->principal_due, 2) }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format((float) $schedule->interest_due, 2) }}</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ number_format((float) $schedule->total_due, 2) }}</td>
                        <td class="px-5 py-3 text-right text-[#38CE3C]">{{ number_format((float) $schedule->total_paid, 2) }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $schedule->payment_status === 'PAID' ? 'bg-[#38CE3C]/15 text-[#38CE3C]' : ($schedule->payment_status === 'OVERDUE' ? 'bg-[#FF4D6B]/15 text-[#FF4D6B]' : 'bg-slate-100 text-slate-600') }}">{{ $schedule->payment_status }}</span>
                        </td>
                        <td class="px-5 py-3 text-right {{ (int) $schedule->days_overdue > 0 ? 'text-[#FF4D6B] font-semibold' : 'text-slate-400' }}">{{ (int) $schedule->days_overdue }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
