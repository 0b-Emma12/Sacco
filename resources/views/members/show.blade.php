@extends('layouts.app')

@section('title', 'Member Details')

@section('content')
<div class="flex flex-col gap-6 mx-auto w-full max-w-7xl">
    
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
        <div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Members / View</div>
            <h1 class="text-2xl font-semibold text-slate-800 dark:text-slate-100">Member Details</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('members.edit', $member->id) }}" class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded hover:bg-slate-50 transition">
                <i data-lucide="edit" class="w-4 h-4"></i> Edit Member
            </a>
            @if(strtolower($member->status) !== 'exited')
            <form method="POST" action="{{ route('members.exitSacco', $member->id) }}" onsubmit="return confirm('Are you sure you want to exit this member from the SACCO? This will liquidate their savings and share capital.');">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-white bg-[#181824] rounded-xl hover:bg-slate-800 transition shadow-sm">
                    <i data-lucide="user-x" class="w-4 h-4"></i> Exit SACCO
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('members.destroy', $member->id) }}" onsubmit="return confirm('WARNING: This will permanently delete the member and ALL their associated accounts, loans, and transaction history. Are you sure?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-[#FF4D6B] bg-[#FF4D6B]/15 border border-[#FF4D6B]/20 rounded-xl hover:bg-[#FF4D6B]/25 transition">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Delete Member
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-[#38CE3C] font-semibold bg-[#38CE3C]/10 border border-[#38CE3C]/20 rounded-xl" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 mb-4 text-sm text-[#FF4D6B] bg-[#FF4D6B]/10 border border-[#FF4D6B]/20 rounded-xl" role="alert">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-10 lg:space-y-0 lg:grid lg:grid-cols-12 gap-8">
        <!-- LEFT SIDE -->
        <div class="col-span-3 space-y-8">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h3 class="text-center font-semibold text-lg pb-2 border-b border-slate-100 dark:border-slate-800 mb-4">
                    Personal Details
                </h3>
                <div class="space-y-3">
                    <div class="font-medium text-sm">Membership No: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->membership_number }}</span></div>
                    <div class="font-medium text-sm flex items-center justify-between">
                        Status: 
                        @php
                            $st = strtolower($member->status);
                            $sBadge = $st === 'active'
                                ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20'
                                : ($st === 'exited' || $st === 'closed'
                                    ? 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20'
                                    : 'bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30');
                        @endphp
                        <span class="font-semibold rounded-full px-2.5 py-0.5 text-xs text-center {{ $sBadge }}">
                            {{ $member->status }}
                        </span>
                    </div>
                    <div class="font-medium text-sm">Salutation: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->salutation }}</span></div>
                    <div class="font-medium text-sm">First Name: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->first_name }}</span></div>
                    <div class="font-medium text-sm">Last Name: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->last_name }}</span></div>
                    <div class="font-medium text-sm">National ID: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->national_id }}</span></div>
                    <div class="font-medium text-sm">Phone Number: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->phone_number }}</span></div>
                </div>
            </div>

            <!-- SACCO EQUITY -->
            <div class="rounded-xl border border-sacco bg-white shadow-sm overflow-hidden dark:border-sacco-dark dark:bg-slate-950">
                <div class="bg-sacco text-white p-4 text-center">
                    <h3 class="font-bold text-lg">Financial Position</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Liquid Savings</div>
                        <div class="text-xl font-bold text-slate-800 dark:text-slate-100">UGX {{ number_format($member->total_savings) }}</div>
                        <div class="text-[11px] text-slate-500 mt-1">Available for withdrawal / loan guarantee</div>
                    </div>
                    <div class="h-px bg-slate-100 dark:bg-slate-800"></div>
                    <div>
                        <div class="text-xs font-bold tracking-wider text-sacco uppercase">Share Capital (Equity)</div>
                        <div class="text-xl font-bold text-sacco-dark dark:text-sacco">UGX {{ number_format($member->share_capital) }}</div>
                        <div class="text-[11px] text-slate-500 mt-1">Non-withdrawable. Earns dividends.</div>
                    </div>
                    
                    @if(strtolower($member->status) === 'active')
                    <div class="pt-2">
                        <form method="POST" action="{{ route('members.buyShares', $member->id) }}" class="flex flex-col gap-2">
                            @csrf
                            <input type="number" name="shares_amount" placeholder="Amount (UGX)" class="w-full h-9 px-3 rounded-xl text-sm border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition" required min="1000">
                            <button type="submit" class="w-full py-2 bg-[#38CE3C] text-white rounded-xl text-sm font-semibold hover:bg-[#2cb030] transition shadow-sm">Buy Shares</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            <!-- EMPLOYMENT DETAILS -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h3 class="text-center font-semibold text-lg pb-2 border-b border-slate-100 dark:border-slate-800 mb-4">
                    Employment Details
                </h3>
                @if($member->employmentDetail)
                <div class="space-y-3">
                    <div class="font-medium text-sm">Employment Type: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->employmentDetail->employment_type }}</span></div>
                    <div class="font-medium text-sm">Employer: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->employmentDetail->employer_name ?? 'N/A' }}</span></div>
                    <div class="font-medium text-sm">Job Title: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->employmentDetail->job_title ?? 'N/A' }}</span></div>
                    <div class="font-medium text-sm">Monthly Income: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->employmentDetail->monthly_income ? 'UGX ' . number_format($member->employmentDetail->monthly_income, 2) : 'N/A' }}</span></div>
                    <div class="font-medium text-sm">Business Name: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->employmentDetail->business_name ?? 'N/A' }}</span></div>
                    <div class="font-medium text-sm">Business Type: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $member->employmentDetail->business_type ?? 'N/A' }}</span></div>
                </div>
                @else
                <p class="text-sm text-slate-500 text-center py-4">No employment details available.</p>
                @endif
            </div>

            <!-- NEXT OF KIN -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h3 class="text-center font-semibold text-lg pb-2 border-b border-slate-100 dark:border-slate-800 mb-4">
                    Next Of Kin Details
                </h3>
                @if($member->nextOfKins->count())
                    <div class="space-y-5">
                    @foreach($member->nextOfKins as $nok)
                        <div class="bg-slate-50 dark:bg-slate-900/50 p-3 rounded-lg border border-slate-100 dark:border-slate-800 space-y-2">
                            <div class="font-medium text-sm">Name: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $nok->name }}</span></div>
                            <div class="font-medium text-sm">Relationship: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $nok->relationship }}</span></div>
                            <div class="font-medium text-sm">Phone Number: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $nok->phone_number }}</span></div>
                            <div class="font-medium text-sm">National ID: <span class="font-light block text-slate-600 dark:text-slate-400">{{ $nok->national_id ?? 'N/A' }}</span></div>
                        </div>
                    @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-4">No next of kin details available.</p>
                @endif
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="col-span-9 space-y-8">
            <!-- Accounts -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-950">
                <div class="p-5 flex flex-row items-center justify-between border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Accounts</h3>
                    <button x-data @click="$dispatch('open-account-modal')" class="px-3 py-1.5 text-sm font-semibold text-white bg-[#38CE3C] border border-[#38CE3C] rounded-xl hover:bg-[#2cb030] transition shadow-sm flex items-center gap-1">
                        <i data-lucide="plus" class="w-4 h-4"></i> Open Account
                    </button>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Account Number</th>
                                <th class="px-6 py-4 font-semibold">Type</th>
                                <th class="px-6 py-4 font-semibold">Balance</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                                <th class="px-6 py-4 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($member->accounts as $account)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                                <td class="px-6 py-4 font-medium text-sacco hover:underline cursor-pointer">
                                    {{ $account->account_number }}
                                    @if($account->is_default)
                                        <span class="ml-2 px-2 py-0.5 text-[10px] font-bold tracking-wider text-[#38CE3C] bg-[#38CE3C]/15 rounded-md uppercase">Default</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    {{ $account->product->name ?? 'N/A' }}
                                    @if($account->term_months)
                                         <div class="text-xs text-slate-500 mt-1">{{ $account->term_months }} Months (Matures {{ \Carbon\Carbon::parse($account->maturity_date)->format('M d, Y') }})</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">UGX {{ number_format($account->balance, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $account->is_active ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20' : 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20' }}">
                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 flex gap-4">
                                    <a href="{{ route('accounts.show', $account->id) }}" class="text-slate-400 hover:text-[#38CE3C] transition" title="View Account">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this account?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-[#FF4D6B] transition" title="Delete Account">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">No account found for this member</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transactions -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-950">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Transactions History</h3>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Transaction ID</th>
                                <th class="px-6 py-4 font-semibold">Account Number</th>
                                <th class="px-6 py-4 font-semibold">Transaction Type</th>
                                <th class="px-6 py-4 font-semibold">Amount</th>
                                <th class="px-6 py-4 font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($transactions as $transaction)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                                <td class="px-6 py-4 font-medium">{{ $transaction->reference }}</td>
                                <td class="px-6 py-4">{{ $transaction->account_number }}</td>
                                <td class="px-6 py-4">{{ ucwords(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                                <td class="px-6 py-4">UGX {{ number_format($transaction->amount, 2) }}</td>
                                <td class="px-6 py-4">{{ $transaction->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">No transactions found for this member</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loan History -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden dark:border-slate-800 dark:bg-slate-950">
                <div class="p-5 flex flex-row items-center justify-between border-b border-slate-100 dark:border-slate-800 gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Loan Applications</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Applications submitted for this member.</p>
                    </div>
                    <a href="{{ route('loans.create', ['member_id' => $member->id]) }}" class="text-sm font-medium text-sacco hover:underline">New application</a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Application ID</th>
                                <th class="px-6 py-4 font-semibold">Requested amount</th>
                                <th class="px-6 py-4 font-semibold">Applied</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                                <th class="px-6 py-4 font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($member->loans as $loan)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                                <td class="px-6 py-4 font-medium">{{ $loan->id }}</td>
                                <td class="px-6 py-4">UGX {{ number_format($loan->principal_amount, 2) }}</td>
                                <td class="px-6 py-4">{{ $loan->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $lStatusClass = match(strtolower($loan->status)) {
                                            'approved', 'disbursed' => 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20',
                                            'rejected', 'defaulted' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20',
                                            'under_review', 'submitted' => 'bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30',
                                            default => 'bg-[#8E32E9]/15 text-[#8E32E9] border border-[#8E32E9]/20'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $lStatusClass }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4"><a href="{{ route('loans.show', $loan->id) }}" class="text-sacco hover:underline">View</a></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">No loan applications found for this member.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Open Account Modal -->
<div x-data="{ open: false, selectedProduct: '', products: {{ $products->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; })->toJson() }}, isFixed: false }" 
     @open-account-modal.window="open = true"
     x-init="$watch('selectedProduct', value => {
        let product = products.find(p => p.id == value);
        isFixed = product && product.name.toLowerCase().includes('fixed');
     })"
     x-show="open" 
     style="display: none;"
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-slate-900/75 dark:bg-slate-900/90 backdrop-blur-sm" aria-hidden="true" @click="open = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white border border-slate-200 shadow-xl rounded-2xl dark:bg-slate-900 dark:border-slate-800 sm:align-middle">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold leading-6 text-slate-900 dark:text-slate-100" id="modal-title">Open Account</h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-500 focus:outline-none">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="member_id" value="{{ $member->id }}">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Which type of account would you like to open?</label>
                    <div class="space-y-2 mt-3">
                        @foreach($products as $product)
                            <label class="flex items-center p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800 transition">
                                <input type="radio" name="product_id" value="{{ $product->id }}" x-model="selectedProduct" class="w-4 h-4 text-sacco border-slate-300 focus:ring-sacco dark:border-slate-600 dark:bg-slate-700 dark:ring-offset-slate-800" required>
                                <span class="ml-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $product->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="isFixed" x-collapse class="space-y-4 mb-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-700">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Term (Months)</label>
                        <select name="term_months" class="w-full px-3 py-2 text-sm border border-slate-200 rounded outline-none focus:border-sacco dark:bg-slate-900 dark:border-slate-700" :required="isFixed">
                            <option value="">Select Term</option>
                            <option value="3">3 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">12 Months</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" class="w-full px-3 py-2 text-sm border border-slate-200 rounded outline-none focus:border-sacco dark:bg-slate-900 dark:border-slate-700" placeholder="e.g. 5.5">
                    </div>
                </div>

                <div class="mt-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-semibold text-white bg-[#38CE3C] border border-[#38CE3C] rounded-xl shadow-sm hover:bg-[#2cb030] transition mb-2 sm:mb-0">
                        Create Account
                    </button>
                    <button type="button" @click="open = false" class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl shadow-sm hover:bg-slate-50 transition dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
