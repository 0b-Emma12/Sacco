@extends('layouts.app')

@section('title', 'Settings')

@section('content')
@php
    $tab = in_array(request('tab'), ['organization', 'system', 'notifications']) ? request('tab') : 'organization';
    $tabBase = 'flex items-center justify-start px-4 py-3 rounded-xl transition-colors border-0';
@endphp
<div class="mx-auto w-full max-w-6xl flex flex-col gap-6">
    <x-page-header
        eyebrow="Configuration"
        title="Settings"
        subtitle="Organisation identity, system defaults and notification preferences.">
        <a href="{{ route('help.index') }}" class="h-10 px-4 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold inline-flex items-center gap-2 hover:bg-slate-50 transition dark:bg-[#181824] dark:border-[#272738] dark:text-slate-200">
            <x-icon name="help-circle" class="w-4 h-4"/> Help &amp; guides
        </a>
    </x-page-header>

    @if(session('success'))
        <div class="p-4 text-sm text-[#38CE3C] font-semibold rounded-xl bg-[#38CE3C]/10 border border-[#38CE3C]/20" role="alert">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 text-sm text-[#FF4D6B] font-semibold rounded-xl bg-[#FF4D6B]/10 border border-[#FF4D6B]/20" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Organisation" :value="$settings['org_name'] ?? $orgName" meta="Registered SACCO name" icon="building" tone="green"/>
        <x-stat-card label="Currency" :value="$settings['sys_currency'] ?? '—'" meta="Default posting currency" icon="coins" tone="blue"/>
        <x-stat-card label="Timezone" :value="$settings['sys_timezone'] ?? '—'" meta="System clock reference" icon="clock" tone="purple"/>
        <x-stat-card label="Fiscal year starts" :value="$settings['sys_fiscal_year'] ?? '—'" meta="Financial period start" icon="pie-chart" tone="amber"/>
    </div>

    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 dark:bg-[#181824] dark:border-[#272738] min-h-[600px] overflow-hidden flex flex-col md:flex-row w-full h-full">
        <!-- Vertical Tabs (server-side ?tab=) -->
        <nav class="w-full md:w-64 h-auto md:h-full flex flex-row md:flex-col justify-start items-stretch bg-slate-50/50 border-b md:border-b-0 md:border-r border-slate-100 dark:bg-slate-950/20 dark:border-slate-800 p-4 gap-2">
            <a href="{{ route('settings.index', ['tab' => 'organization']) }}"
               class="{{ $tabBase }} {{ $tab === 'organization' ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <x-icon name="building" class="mr-3 w-5 h-5"/> Organization
            </a>
            <a href="{{ route('settings.index', ['tab' => 'system']) }}"
               class="{{ $tabBase }} {{ $tab === 'system' ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <x-icon name="settings" class="mr-3 w-5 h-5"/> System
            </a>
            <a href="{{ route('settings.index', ['tab' => 'notifications']) }}"
               class="{{ $tabBase }} {{ $tab === 'notifications' ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <x-icon name="bell" class="mr-3 w-5 h-5"/> Notifications
            </a>
        </nav>

        <!-- Content Area -->
        <div class="flex-1 p-6 md:p-10 relative">
            <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if($tab === 'organization')
                <div class="w-full max-w-lg flex flex-col">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Organization Details</h2>
                        <p class="text-sm text-slate-500 mt-1">Update the primary details of the organization.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Organization Name</label>
                            <input type="text" name="org_name" value="{{ $settings['org_name'] ?? '' }}" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Email Address</label>
                            <input type="email" name="org_email" value="{{ $settings['org_email'] ?? '' }}" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Phone Number</label>
                            <input type="text" name="org_phone" value="{{ $settings['org_phone'] ?? '' }}" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Address</label>
                            <textarea name="org_address" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 rounded-xl p-3 outline-none transition" rows="3">{{ $settings['org_address'] ?? '' }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">URA TIN</label>
                                <input type="text" name="org_tin" value="{{ $settings['org_tin'] ?? '' }}" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" />
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">UMRA Registration No.</label>
                                <input type="text" name="org_umra_reg_no" value="{{ $settings['org_umra_reg_no'] ?? '' }}" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" />
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">SACCO Logo</label>
                            @if(! empty($settings['org_logo']))
                                <div class="flex items-center gap-4 mb-2">
                                    <img src="{{ route('settings.logo') }}" alt="SACCO logo" class="h-12 w-12 object-contain border border-slate-200 rounded-lg bg-white p-1">
                                    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                                        <input type="checkbox" name="remove_logo" value="1"> Remove current logo
                                    </label>
                                </div>
                            @endif
                            <input type="file" name="org_logo_file" accept="image/png,image/jpeg" class="w-full text-sm text-slate-600 dark:text-slate-300 file:mr-3 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-[#38CE3C]/15 file:text-[#38CE3C] file:font-semibold" />
                            <p class="text-xs text-slate-500 mt-1">PNG or JPEG, maximum 2 MB. Used on every printed document.</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Document Disclaimer</label>
                            <textarea name="doc_disclaimer" rows="2" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 rounded-xl p-3 outline-none transition">{{ $settings['doc_disclaimer'] ?? $docDisclaimer ?? '' }}</textarea>
                            <p class="text-xs text-slate-500 mt-1">Printed at the foot of every document.</p>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="w-full sm:w-auto h-10 px-5 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white font-semibold transition flex items-center justify-center gap-2 shadow-sm">
                            <x-icon name="save" class="w-4 h-4"/> Save Changes
                        </button>
                    </div>
                </div>
                @endif

                @if($tab === 'system')
                <div class="w-full max-w-lg flex flex-col">
                    <div class="mb-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">System Configurations</h2>
                        <p class="text-sm text-slate-500 mt-1">Manage system-wide preferences and formatting.</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Default Currency</label>
                            <select name="sys_currency" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition">
                                @foreach(['UGX' => 'UGX', 'USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'KES' => 'KES', 'NGN' => 'NGN (₦)'] as $code => $label)
                                    <option value="{{ $code }}" {{ ($settings['sys_currency'] ?? 'UGX') == $code ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Timezone</label>
                            <select name="sys_timezone" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition">
                                @foreach(['UTC', 'Africa/Nairobi', 'Africa/Lagos', 'America/New_York'] as $timezone)
                                    <option value="{{ $timezone }}" {{ ($settings['sys_timezone'] ?? '') == $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Fiscal Year Start</label>
                            <input type="date" name="sys_fiscal_year" value="{{ $settings['sys_fiscal_year'] ?? '' }}" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" />
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">SACCO Share Price (UGX)</label>
                            <input type="number" name="sys_share_price" value="{{ $settings['sys_share_price'] ?? '10000' }}" min="1" step="100" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" placeholder="e.g. 10000" />
                            <p class="text-xs text-slate-500 mt-1">The fixed monetary value of a single share in the cooperative.</p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300 mb-1 block">Withholding Tax Rate on Dividends (%)</label>
                            <input type="number" name="sys_wht_rate" value="{{ $settings['sys_wht_rate'] ?? '15' }}" min="0" max="100" step="0.01" class="w-full bg-slate-50 border border-slate-200 focus:border-[#38CE3C] focus:ring-2 focus:ring-[#38CE3C]/30 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 outline-none transition" />
                            <p class="text-xs text-slate-500 mt-1">URA rate applied to dividends on shares (interest on savings is not taxed).</p>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="w-full sm:w-auto h-10 px-5 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white font-semibold transition flex items-center justify-center gap-2 shadow-sm">
                            <x-icon name="save" class="w-4 h-4"/> Save Changes
                        </button>
                    </div>
                </div>
                @endif

                @if($tab === 'notifications')
                <div class="w-full max-w-lg flex flex-col">
                    <div class="mb-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Notifications</h2>
                        <p class="text-sm text-slate-500 mt-1">Configure automated communication settings.</p>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Email Notifications</h3>
                                <p class="text-xs text-slate-500">Send automated emails for transactions</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="notif_email" value="0">
                                <input type="checkbox" name="notif_email" value="1" class="sr-only peer" {{ ($settings['notif_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-[#38CE3C]"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl dark:bg-slate-800/50">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">SMS Notifications</h3>
                                <p class="text-xs text-slate-500">Send SMS alerts for important events</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="notif_sms" value="0">
                                <input type="checkbox" name="notif_sms" value="1" class="sr-only peer" {{ ($settings['notif_sms'] ?? '0') == '1' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-[#38CE3C]"></div>
                            </label>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="w-full sm:w-auto h-10 px-5 rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white font-semibold transition flex items-center justify-center gap-2 shadow-sm">
                            <x-icon name="save" class="w-4 h-4"/> Save Changes
                        </button>
                    </div>
                </div>
                @endif

            </form>
        </div>
    </div>
</div>
@endsection
