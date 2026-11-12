@extends('layouts.app')

@section('title', 'Member Details')

@section('content')
<div class="mx-auto w-full max-w-7xl flex flex-col gap-6">

    <x-page-header
        eyebrow="Members / {{ $member->membership_number }}"
        title="{{ $member->first_name }} {{ $member->last_name }}"
        subtitle="Member profile, KYC verification and financial position.">
        <a href="{{ route('members.edit', $member->id) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition">
            <x-icon name="edit" class="w-4 h-4"/> Edit member
        </a>
        @if(strtolower($member->status) === 'active')
        <details class="dropdown">
            <summary class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2 transition cursor-pointer select-none">
                <x-icon name="sliders" class="w-4 h-4"/> Services
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400"/>
            </summary>
            <div class="dropdown-panel w-56 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden dark:bg-[#181824] dark:border-[#272738] p-1">
                <a href="{{ route('member-services.share-transfers.create', ['member' => $member->id]) }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 rounded-lg hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                    <x-icon name="arrow-left-right" class="w-4 h-4 text-[#8E32E9]"/> Transfer shares
                </a>
                <a href="{{ route('member-services.death-claims.create', $member->id) }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 rounded-lg hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                    <x-icon name="file-text" class="w-4 h-4 text-[#FF4D6B]"/> Lodge death claim
                </a>
                <a href="{{ route('member-services.standing-orders.create', ['member' => $member->id]) }}" class="flex items-center gap-2 px-3 py-2 text-xs font-medium text-slate-700 rounded-lg hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                    <x-icon name="refresh-cw" class="w-4 h-4 text-[#38CE3C]"/> Standing order
                </a>
            </div>
        </details>
        @endif
        @if(strtolower($member->status) !== 'exited')
        <a href="#exit-sacco" class="h-10 px-4 rounded-xl bg-[#181824] text-white text-sm font-semibold hover:bg-slate-800 inline-flex items-center gap-2 transition shadow-sm">
            <x-icon name="user-x" class="w-4 h-4"/> Exit SACCO
        </a>
        @endif
        <a href="#delete-member" class="h-10 px-4 rounded-xl bg-[#FF4D6B]/15 border border-[#FF4D6B]/20 text-[#FF4D6B] text-sm font-semibold hover:bg-[#FF4D6B]/25 inline-flex items-center gap-2 transition">
            <x-icon name="trash-2" class="w-4 h-4"/> Delete
        </a>
    </x-page-header>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Liquid savings" :value="'UGX '.number_format($member->total_savings, 2)" meta="Available for withdrawal" icon="wallet" tone="purple"/>
        <x-stat-card label="Share capital" :value="'UGX '.number_format($member->share_capital, 2)" meta="Non-withdrawable equity" icon="coins" tone="amber"/>
        <x-stat-card label="Accounts" :value="number_format($member->accounts->count())" meta="Open member accounts" icon="landmark" tone="blue"/>
        <x-stat-card label="Loan applications" :value="number_format($member->loans->count())" meta="Applications on record" icon="credit-card" tone="coral"/>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 dark:bg-[#181824] dark:border-[#272738] flex flex-wrap items-center gap-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-2">Member documents</span>
        <a href="{{ route('prints.members.welcome', $member->id) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Welcome pack</a>
        <a href="{{ route('prints.members.share-certificate', $member->id) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Share certificate</a>
        <a href="{{ route('prints.members.statement', $member->id) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition dark:bg-slate-800 dark:text-slate-300">Account statement</a>
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
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs dark:border-[#272738] dark:bg-[#181824]">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                        <x-icon name="user" class="w-4 h-4"/>
                    </span>
                    <h3 class="font-semibold text-lg text-slate-800 dark:text-slate-100">Personal Details</h3>
                </div>
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
            <div class="bg-white rounded-2xl border border-[#38CE3C]/25 shadow-xs overflow-hidden dark:border-[#38CE3C]/30 dark:bg-[#181824]">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                        <x-icon name="wallet" class="w-4 h-4"/>
                    </span>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Financial Position</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Savings and equity held with the SACCO.</p>
                    </div>
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
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs dark:border-[#272738] dark:bg-[#181824]">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-[#8E32E9]/10 text-[#8E32E9] flex items-center justify-center shrink-0">
                        <x-icon name="building" class="w-4 h-4"/>
                    </span>
                    <h3 class="font-semibold text-lg text-slate-800 dark:text-slate-100">Employment Details</h3>
                </div>
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
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs dark:border-[#272738] dark:bg-[#181824]">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-[#FFDE73]/20 text-amber-600 flex items-center justify-center shrink-0">
                        <x-icon name="users" class="w-4 h-4"/>
                    </span>
                    <h3 class="font-semibold text-lg text-slate-800 dark:text-slate-100">Next Of Kin Details</h3>
                </div>
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
            <!-- KYC Verification (M02) -->
            @php
                $kycStatus = $member->kyc_status ?: ($member->kyc_verified ? 'VERIFIED' : 'PENDING');
                $kycBadge = match ($kycStatus) {
                    'VERIFIED' => 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20',
                    'REJECTED' => 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20',
                    default => 'bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30',
                };
                $kycChecklist = [
                    'national_id' => 'National ID (front & back scanned)',
                    'passport_photo' => 'Passport photo verified against person',
                    'proof_of_address' => 'Proof of address (utility bill / LC letter)',
                    'proof_of_common_bond' => 'Proof of common bond',
                    'next_of_kin_id' => 'Next of kin ID copy',
                ];
                $heldDocuments = $member->kycDocuments();
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-[#8E32E9]/10 text-[#8E32E9] flex items-center justify-center shrink-0">
                            <x-icon name="shield" class="w-4 h-4"/>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">KYC Verification</h3>
                            <p class="text-xs text-slate-500 mt-1">M02 — UMRA Tier 4 identity verification. Review every {{ (int) \App\Models\Setting::get('kyc_review_years', 2) }} years.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $kycBadge }}">{{ str_replace('_', ' ', $kycStatus) }}</span>
                        @if($member->kycReviewDue())
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md bg-[#FFDE73]/30 text-amber-700 border border-amber-300/30">Re-verification due</span>
                        @endif
                    </div>
                </div>

                @if($member->transactionsBlockedByKyc())
                    <div class="mx-5 mt-5 p-3 rounded-xl text-sm bg-[#FF4D6B]/10 text-[#FF4D6B] border border-[#FF4D6B]/20 font-semibold">
                        Transactions are blocked because KYC was not verified within {{ (int) \App\Models\Setting::get('kyc_grace_days', 30) }} days of joining (grace ended {{ $member->kycGraceEndsAt()->format('d M Y') }}).
                    </div>
                @endif

                <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Verified By</div>
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ $member->kyc_verified_by ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Verification Date</div>
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ optional($member->kyc_verified_at)->format('d M Y H:i') ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs font-bold tracking-wider text-slate-400 uppercase">Next Review</div>
                        <div class="text-sm font-semibold {{ $member->kycReviewDue() ? 'text-[#FF4D6B]' : 'text-slate-700 dark:text-slate-200' }} mt-1">{{ optional($member->kyc_next_review_at)->format('d M Y') ?: '—' }}</div>
                    </div>
                </div>

                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 border-b border-slate-100 dark:border-slate-800">
                    @foreach($kycChecklist as $key => $label)
                        <div class="flex items-center gap-2 text-sm {{ ($heldDocuments[$key] ?? false) ? 'text-slate-700 dark:text-slate-200' : 'text-slate-400' }}">
                            <x-icon name="{{ ($heldDocuments[$key] ?? false) ? 'check-circle' : 'clock' }}" class="w-4 h-4 {{ ($heldDocuments[$key] ?? false) ? 'text-[#38CE3C]' : 'text-slate-300' }}"/>
                            {{ $label }}
                        </div>
                    @endforeach
                </div>

                @if($member->kyc_rejection_reason)
                    <div class="mx-5 mt-5 p-3 bg-[#FF4D6B]/10 text-[#FF4D6B] rounded-xl text-sm border border-[#FF4D6B]/20">
                        <span class="font-semibold">Outstanding issue:</span> {{ $member->kyc_rejection_reason }}
                    </div>
                @endif

                @if(Auth::user()->canVerifyKyc())
                    <details class="group">
                        <summary class="px-5 py-4 cursor-pointer text-sm font-semibold text-[#8E32E9] select-none">Record verification (M02)</summary>
                        <form method="POST" action="{{ route('members.kyc', $member->id) }}" class="px-5 pb-5 border-t border-slate-100 dark:border-slate-800 pt-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 block mb-1">Decision *</label>
                                    <select name="kyc_status" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                                        <option value="VERIFIED" {{ $kycStatus === 'VERIFIED' ? 'selected' : '' }}>Verified / Approved</option>
                                        <option value="ADDITIONAL_INFO" {{ $kycStatus === 'ADDITIONAL_INFO' ? 'selected' : '' }}>Pending additional information</option>
                                        <option value="REJECTED" {{ $kycStatus === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 block mb-1">Verifying Officer *</label>
                                    <input type="text" name="verified_by" maxlength="100" value="{{ old('verified_by', $member->kyc_verified_by ?: (Auth::user()->username ?? Auth::user()->email)) }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700" required>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2">
                                @foreach($kycChecklist as $key => $label)
                                    <label class="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <input type="checkbox" name="documents[{{ $key }}]" value="1" class="mt-0.5" {{ ($heldDocuments[$key] ?? false) ? 'checked' : '' }}>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 block mb-1">Rejection reason / missing information</label>
                                    <textarea name="rejection_reason" rows="2" maxlength="1000" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">{{ old('rejection_reason', $member->kyc_rejection_reason) }}</textarea>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 block mb-1">Notes</label>
                                    <textarea name="notes" rows="2" maxlength="1000" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm dark:bg-slate-800 dark:border-slate-700">{{ old('notes', $member->kyc_notes) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="h-10 px-6 bg-[#8E32E9] text-white rounded-xl hover:bg-purple-700 transition font-semibold text-sm shadow-sm">Save KYC Decision</button>
                            </div>
                        </form>
                    </details>
                @endif
            </div>

            <!-- Accounts -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
                <div class="p-5 flex flex-row items-center justify-between border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                            <x-icon name="landmark" class="w-4 h-4"/>
                        </span>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Accounts</h3>
                    </div>
                    <a href="{{ route('accounts.create', ['member_id' => $member->id]) }}" class="h-10 px-4 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-semibold shadow-sm inline-flex items-center gap-2">
                        <x-icon name="plus" class="w-4 h-4"/> Open Account
                    </a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-950/50 text-slate-500 text-[11px] uppercase tracking-wider">
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account Number</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Balance</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($member->accounts as $account)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                                <td class="px-5 py-4 font-medium text-sacco">
                                    <a href="{{ route('accounts.show', $account->id) }}" class="hover:underline">{{ $account->account_number }}</a>
                                    @if($account->is_default)
                                        <span class="ml-2 px-2 py-0.5 text-[10px] font-bold tracking-wider text-[#38CE3C] bg-[#38CE3C]/15 rounded-md uppercase">Default</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    {{ $account->product->name ?? 'N/A' }}
                                    @if($account->term_months)
                                         <div class="text-xs text-slate-500 mt-1">{{ $account->term_months }} Months (Matures {{ \Carbon\Carbon::parse($account->maturity_date)->format('M d, Y') }})</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">UGX {{ number_format($account->balance, 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $account->is_active ? 'bg-[#38CE3C]/15 text-[#38CE3C] border border-[#38CE3C]/20' : 'bg-[#FF4D6B]/15 text-[#FF4D6B] border border-[#FF4D6B]/20' }}">
                                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="flex gap-4">
                                        <a href="{{ route('accounts.show', $account->id) }}" class="text-slate-400 hover:text-[#38CE3C] transition" title="View Account">
                                            <x-icon name="eye" class="w-4 h-4"/>
                                        </a>
                                        <a href="#delete-account-{{ $account->id }}" class="text-slate-400 hover:text-[#FF4D6B] transition" title="Delete Account">
                                            <x-icon name="trash-2" class="w-4 h-4"/>
                                        </a>
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <x-empty-state colspan="5" icon="wallet" title="No accounts yet" message="Open an account to begin tracking this member's savings."/>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transactions -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-[#38CE3C]/10 text-[#38CE3C] flex items-center justify-center shrink-0">
                        <x-icon name="arrow-left-right" class="w-4 h-4"/>
                    </span>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Transactions History</h3>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-950/50 text-slate-500 text-[11px] uppercase tracking-wider">
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Transaction ID</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Account Number</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Transaction Type</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($transactions as $transaction)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                                <td class="px-5 py-4 font-medium">{{ $transaction->reference }}</td>
                                <td class="px-5 py-4">{{ $transaction->account_number }}</td>
                                <td class="px-5 py-4">{{ ucwords(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                                <td class="px-5 py-4">UGX {{ number_format($transaction->amount, 2) }}</td>
                                <td class="px-5 py-4">{{ $transaction->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <x-empty-state colspan="5" icon="file-text" title="No transactions yet" message="Transactions on this member's accounts will appear here."/>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loan History -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden dark:border-[#272738] dark:bg-[#181824]">
                <div class="p-5 flex flex-row items-center justify-between border-b border-slate-100 dark:border-slate-800 gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-[#FF4D6B]/10 text-[#FF4D6B] flex items-center justify-center shrink-0">
                            <x-icon name="credit-card" class="w-4 h-4"/>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Loan Applications</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Applications submitted for this member.</p>
                        </div>
                    </div>
                    <a href="{{ route('loans.create', ['member_id' => $member->id]) }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200 inline-flex items-center gap-2">New application</a>
                </div>
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-950/50 text-slate-500 text-[11px] uppercase tracking-wider">
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Application ID</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Requested amount</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Applied</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-5 py-4 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($member->loans as $loan)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition">
                                <td class="px-5 py-4 font-medium">{{ $loan->id }}</td>
                                <td class="px-5 py-4">UGX {{ number_format($loan->principal_amount, 2) }}</td>
                                <td class="px-5 py-4">{{ $loan->created_at->format('M d, Y') }}</td>
                                <td class="px-5 py-4">
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
                                <td class="px-5 py-4"><a href="{{ route('loans.show', $loan->id) }}" class="text-sacco hover:underline">View</a></td>
                            </tr>
                            @empty
                            <x-empty-state colspan="5" icon="credit-card" title="No loan applications" message="Loan applications submitted by this member will appear here."/>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Exit SACCO confirmation (CSS :target) -->
@if(strtolower($member->status) !== 'exited')
<div id="exit-sacco" class="modal">
    <a href="#" class="modal-overlay" aria-label="Close"></a>
    <div class="modal-card bg-white dark:bg-[#181824] rounded-2xl shadow-xl p-6 border border-slate-200/80 dark:border-[#272738] max-w-md">
        <div class="flex items-center gap-3 mb-4">
            <span class="h-10 w-10 rounded-xl bg-[#181824] text-white flex items-center justify-center">
                <x-icon name="user-x" class="w-5 h-5"/>
            </span>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Exit member from SACCO?</h3>
                <p class="text-xs text-slate-500">{{ $member->first_name }} {{ $member->last_name }}</p>
            </div>
        </div>
        <p class="text-xs text-slate-500 mb-6">This liquidates the member's savings and share capital, deactivates all accounts and releases their guarantor pledges.</p>
        <div class="flex justify-end gap-3">
            <a href="#" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">Cancel</a>
            <form method="POST" action="{{ route('members.exitSacco', $member->id) }}" class="m-0">
                @csrf
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#181824] hover:bg-slate-800 rounded-xl transition">Confirm Exit</button>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Delete member confirmation (CSS :target) -->
<div id="delete-member" class="modal">
    <a href="#" class="modal-overlay" aria-label="Close"></a>
    <div class="modal-card bg-white dark:bg-[#181824] rounded-2xl shadow-xl p-6 border border-slate-200/80 dark:border-[#272738] max-w-md">
        <div class="flex items-center gap-3 mb-4">
            <span class="h-10 w-10 rounded-xl bg-[#FF4D6B]/10 text-[#FF4D6B] flex items-center justify-center">
                <x-icon name="alert-triangle" class="w-5 h-5"/>
            </span>
            <div>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Delete member permanently?</h3>
                <p class="text-xs text-slate-500">{{ $member->first_name }} {{ $member->last_name }}</p>
            </div>
        </div>
        <p class="text-xs text-slate-500 mb-6">WARNING: this removes the member and ALL their accounts, loans and transaction history.</p>
        <div class="flex justify-end gap-3">
            <a href="#" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">Cancel</a>
            <form method="POST" action="{{ route('members.destroy', $member->id) }}" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#FF4D6B] hover:bg-[#e63c58] rounded-xl transition">Delete Permanently</button>
            </form>
        </div>
    </div>
</div>

<!-- Delete account confirmations (CSS :target) -->
@foreach($member->accounts as $account)
<div id="delete-account-{{ $account->id }}" class="modal">
    <a href="#" class="modal-overlay" aria-label="Close"></a>
    <div class="modal-card bg-white dark:bg-[#181824] rounded-2xl shadow-xl p-6 border border-slate-200/80 dark:border-[#272738] max-w-sm">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-1">Delete account?</h3>
        <p class="text-xs text-slate-500 mb-6">{{ $account->account_number }} — {{ $account->product->name ?? 'N/A' }}</p>
        <div class="flex justify-end gap-3">
            <a href="#" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">Cancel</a>
            <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-[#FF4D6B] hover:bg-[#e63c58] rounded-xl transition">Delete</button>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
