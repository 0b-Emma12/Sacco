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
        <div class="bg-[#38CE3C]/10 text-[#38CE3C] p-4 rounded-xl text-sm border border-[#38CE3C]/20 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <!-- Overview Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 dark:bg-slate-900 dark:border-slate-800">
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
                <div class="font-semibold text-[#38CE3C]">
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
                        'approved', 'disbursed' => 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20',
                        'rejected', 'defaulted' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20',
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
            @if ($errors->has('rejection_reason'))
                <div class="text-[#FF4D6B] text-sm font-medium">{{ $errors->first('rejection_reason') }}</div>
            @endif
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="px-4 py-2.5 bg-[#38CE3C] text-white text-sm font-semibold rounded-xl hover:bg-[#2cb030] transition flex items-center gap-2 shadow-sm">
                        <i data-lucide="check" class="w-4 h-4"></i> Approve Loan
                    </button>
                </form>
                <form action="{{ route('loans.update', $loan->id) }}" method="POST" class="inline flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="action" value="reject">
                    <input type="text" name="rejection_reason" placeholder="Reason for rejection (Required)" class="px-3 py-2 text-sm border border-slate-200 rounded-xl outline-none focus:border-[#FF4D6B] focus:ring-2 focus:ring-[#FF4D6B]/20 w-64 transition" required>
                    <button type="submit" class="px-4 py-2.5 bg-[#FF4D6B] text-white text-sm font-semibold rounded-xl hover:bg-[#FF4D6B]/90 transition flex items-center gap-2">
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
                <button type="submit" class="px-4 py-2.5 bg-[#8E32E9] text-white text-sm font-semibold rounded-xl hover:bg-purple-700 transition flex items-center gap-2 shadow-sm">
                    <i data-lucide="send" class="w-4 h-4"></i> Disburse Loan to Member
                </button>
            </form>
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
                            <a href="{{ route('members.show', $pledge->guarantor->id) }}" class="text-[#38CE3C] hover:underline">
                                {{ $pledge->guarantor->first_name }} {{ $pledge->guarantor->last_name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">UGX {{ number_format($pledge->pledged_amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#8E32E9]/15 text-[#8E32E9] border border-[#8E32E9]/20">
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
        <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-800">
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
                        <td class="px-6 py-4 text-right font-semibold text-[#38CE3C]">+ UGX {{ number_format($repayment->amount_paid) }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $repayment->balance_after <= 0 ? 'text-[#38CE3C]' : 'text-[#FF4D6B]' }}">UGX {{ number_format($repayment->balance_after) }}</td>
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
