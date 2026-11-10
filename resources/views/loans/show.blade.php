@extends('layouts.app')
@section('title', 'Loan Details')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Loans</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">
                Loan APP-{{ str_pad($loan->id, 4, '0', STR_PAD_LEFT) }}
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('loans.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Back to Loans</a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-emerald-50 text-emerald-700 p-4 rounded-md text-sm border border-emerald-100">
            {{ session('success') }}
        </div>
    @endif

    <!-- Overview Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <div>
                <div class="text-xs text-slate-500 mb-1">Applicant</div>
                <div class="font-semibold text-slate-800 dark:text-slate-200">
                    <a href="{{ route('members.show', $loan->member->id) }}" class="text-sacco hover:underline">
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
                <div class="font-semibold text-emerald-600">
                    @php
                        $p = $loan->principal_amount;
                        $r = $loan->interest_rate / 100;
                        $n = $loan->term_months;
                        
                        if ($r > 0 && $n > 0) {
                            $emi = $p * $r * pow(1 + $r, $n) / (pow(1 + $r, $n) - 1);
                            $total = $emi * $n;
                        } else {
                            $total = $p;
                        }
                    @endphp
                    UGX {{ number_format($total, 2) }}
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 mb-1">Status</div>
                @php
                    $statusClass = match($loan->status) {
                        'approved', 'disbursed' => 'bg-emerald-100 text-emerald-700',
                        'rejected', 'defaulted' => 'bg-rose-100 text-rose-700',
                        'under_review' => 'bg-amber-100 text-amber-700',
                        default => 'bg-slate-100 text-slate-700'
                    };
                @endphp
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $loan->status)) }}
                </span>
            </div>
        </div>
        
        <!-- Action Buttons (Admin/Manager) -->
        @if($loan->status === 'under_review')
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-4">
            @if ($errors->has('rejection_reason'))
                <div class="text-rose-500 text-sm font-medium">{{ $errors->first('rejection_reason') }}</div>
            @endif
            <div class="flex gap-3">
                <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="px-4 py-2.5 bg-emerald-500 text-white text-sm font-medium rounded hover:bg-emerald-600 transition flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Approve Loan
                    </button>
                </form>
                <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="reject">
                    <input type="text" name="rejection_reason" placeholder="Reason for rejection (Required)" class="px-3 py-2 text-sm border border-slate-200 rounded outline-none focus:border-rose-500 w-64" required>
                    <button type="submit" class="px-4 py-2.5 bg-rose-500 text-white text-sm font-medium rounded hover:bg-rose-600 transition flex items-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i> Reject
                    </button>
                </form>
            </div>
        </div>
        @elseif($loan->status === 'approved')
        <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
            <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="disburse">
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 transition flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Disburse Loan to Member
                </button>
            </form>
        </div>
        @elseif($loan->status === 'rejected' && $loan->rejection_reason)
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="p-4 bg-rose-50 rounded border border-rose-100">
                <h4 class="text-sm font-bold text-rose-800 mb-1">Rejection Reason</h4>
                <p class="text-sm text-rose-700">{{ $loan->rejection_reason }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Guarantors Matrix -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Guarantor Pledges</h3>
            <p class="text-xs text-slate-500 mt-1">Savings frozen as social collateral for this loan.</p>
        </div>
        <div class="w-full overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Guarantor Member</th>
                        <th class="px-6 py-4 font-semibold">Pledged Amount</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($loan->guarantorPledges as $pledge)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                            <a href="{{ route('members.show', $pledge->guarantor->id) }}" class="text-sacco hover:underline">
                                {{ $pledge->guarantor->first_name }} {{ $pledge->guarantor->last_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">UGX {{ number_format($pledge->pledged_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                {{ ucfirst($pledge->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-slate-500">No guarantors attached to this loan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <!-- Repayment Tracker -->
    @if(in_array($loan->status, ['approved', 'disbursed', 'partially_paid', 'paid']))
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">Loan Repayment Tracker</h3>
            <p class="text-xs text-slate-500 mt-1">Record payments and track outstanding balance.</p>
        </div>
        @php
            $totalRepayable = $loan->total_repayable;
            $totalPaid = $loan->repayments->sum('amount_paid');
            $outstanding = max(0, $totalRepayable - $totalPaid);
            $paidPct = $totalRepayable > 0 ? min(100, ($totalPaid / $totalRepayable) * 100) : 0;
        @endphp
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-800">
            <div class="text-center p-4 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Total Repayable</div>
                <div class="text-xl font-bold text-slate-700 dark:text-slate-200 mt-1">UGX {{ number_format($totalRepayable) }}</div>
            </div>
            <div class="text-center p-4 bg-emerald-50 rounded-xl dark:bg-emerald-900/20">
                <div class="text-xs font-bold tracking-wider text-emerald-500 uppercase">Total Paid</div>
                <div class="text-xl font-bold text-emerald-600 mt-1">UGX {{ number_format($totalPaid) }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ number_format($paidPct, 1) }}% cleared</div>
            </div>
            <div class="text-center p-4 {{ $outstanding <= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-rose-50 dark:bg-rose-900/20' }} rounded-xl">
                <div class="text-xs font-bold tracking-wider {{ $outstanding <= 0 ? 'text-emerald-500' : 'text-rose-500' }} uppercase">Outstanding Balance</div>
                <div class="text-xl font-bold {{ $outstanding <= 0 ? 'text-emerald-600' : 'text-rose-600' }} mt-1">UGX {{ number_format($outstanding) }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $outstanding <= 0 ? '✓ Fully Paid' : 'Remaining' }}</div>
            </div>
        </div>
        <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex justify-between text-xs text-slate-500 mb-1"><span>Progress</span><span>{{ number_format($paidPct, 1) }}%</span></div>
            <div class="w-full bg-slate-200 rounded-full h-2 dark:bg-slate-700">
                <div class="h-2 rounded-full {{ $paidPct >= 100 ? 'bg-emerald-500' : 'bg-sacco' }}" style="width: {{ $paidPct }}%"></div>
            </div>
        </div>
        @if($loan->status !== 'paid')
        <div class="p-6 border-b border-slate-100 dark:border-slate-800">
            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Record a Repayment</h4>
            @if($errors->any())
                <div class="mb-3 p-3 bg-rose-50 text-rose-700 rounded text-sm border border-rose-100">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('loans.repayments.store', $loan->id) }}" class="flex flex-col sm:flex-row gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-500 block mb-1">Amount Paid (UGX) *</label>
                    <input type="number" name="amount" step="1" min="1" max="{{ $outstanding }}"
                        class="w-full h-10 px-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sacco text-sm dark:bg-slate-800 dark:border-slate-700" required>
                </div>
                <div class="flex-1">
                    <label class="text-xs font-semibold text-slate-500 block mb-1">Narration</label>
                    <input type="text" name="narration" placeholder="e.g. Mobile money, Bank transfer"
                        class="w-full h-10 px-3 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sacco text-sm dark:bg-slate-800 dark:border-slate-700">
                </div>
                <button type="submit" class="h-10 px-6 bg-sacco text-white rounded-lg hover:bg-sacco-dark transition font-medium text-sm whitespace-nowrap">
                    Record Payment
                </button>
            </form>
        </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">#</th>
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Narration</th>
                        <th class="px-6 py-4 font-semibold text-right">Amount Paid</th>
                        <th class="px-6 py-4 font-semibold text-right">Balance After</th>
                        <th class="px-6 py-4 font-semibold">Recorded By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($loan->repayments->sortByDesc('created_at') as $repayment)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 text-slate-500">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $repayment->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $repayment->narration }}</td>
                        <td class="px-6 py-4 text-right font-semibold text-emerald-600">+ UGX {{ number_format($repayment->amount_paid) }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $repayment->balance_after <= 0 ? 'text-emerald-600' : 'text-rose-600' }}">UGX {{ number_format($repayment->balance_after) }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $repayment->recorded_by }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500">No repayments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
