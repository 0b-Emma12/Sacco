@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="mx-auto w-full max-w-5xl flex flex-col gap-6" id="settings-tabs">
    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Settings</h1>

    @if(session('success'))
        <div class="bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 p-4 rounded-xl border border-emerald-100 dark:border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 dark:bg-slate-900 dark:border-slate-800 min-h-[600px] overflow-hidden flex flex-col md:flex-row w-full h-full">
        <!-- Vertical Tabs List -->
        <div class="w-full md:w-64 h-auto md:h-full flex flex-row md:flex-col justify-start items-stretch bg-slate-50/50 border-b md:border-b-0 md:border-r border-slate-100 dark:bg-slate-950/20 dark:border-slate-800 p-4 gap-2">
            <button onclick="openTab('organization')" id="tab-organization" class="tab-button flex items-center justify-start px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors bg-transparent border-0 data-[state=active]:bg-indigo-50 data-[state=active]:text-indigo-700 dark:data-[state=active]:bg-indigo-500/20 dark:data-[state=active]:text-indigo-300">
                <i data-lucide="building" class="mr-3 w-5 h-5"></i> Organization
            </button>
            <button onclick="openTab('system')" id="tab-system" class="tab-button flex items-center justify-start px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors bg-transparent border-0 data-[state=active]:bg-indigo-50 data-[state=active]:text-indigo-700 dark:data-[state=active]:bg-indigo-500/20 dark:data-[state=active]:text-indigo-300">
                <i data-lucide="settings" class="mr-3 w-5 h-5"></i> System
            </button>
            <button onclick="openTab('notifications')" id="tab-notifications" class="tab-button flex items-center justify-start px-4 py-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors bg-transparent border-0 data-[state=active]:bg-indigo-50 data-[state=active]:text-indigo-700 dark:data-[state=active]:bg-indigo-500/20 dark:data-[state=active]:text-indigo-300">
                <i data-lucide="bell" class="mr-3 w-5 h-5"></i> Notifications
            </button>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-6 md:p-10 relative">
            <form action="{{ route('settings.store') }}" method="POST">
                @csrf
                
                <!-- Organization Tab -->
                <div id="content-organization" class="tab-content w-full max-w-lg flex flex-col hidden">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Organization Details</h2>
                        <p class="text-sm text-slate-500 mt-1">Update the primary details of the organization.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">Organization Name</label>
                            <input type="text" name="org_name" value="{{ $settings['org_name'] ?? '' }}" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 border outline-none focus:border-indigo-500 transition" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">Email Address</label>
                            <input type="email" name="org_email" value="{{ $settings['org_email'] ?? '' }}" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 border outline-none focus:border-indigo-500 transition" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">Phone Number</label>
                            <input type="text" name="org_phone" value="{{ $settings['org_phone'] ?? '' }}" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 border outline-none focus:border-indigo-500 transition" />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">Address</label>
                            <textarea name="org_address" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 rounded-xl p-3 border outline-none focus:border-indigo-500 transition" rows="3">{{ $settings['org_address'] ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="w-full sm:w-auto px-8 h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition flex items-center justify-center">
                            Save Changes
                        </button>
                    </div>
                </div>

                <!-- System Tab -->
                <div id="content-system" class="tab-content w-full max-w-lg flex flex-col hidden">
                    <div class="mb-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">System Configurations</h2>
                        <p class="text-sm text-slate-500 mt-1">Manage system-wide preferences and formatting.</p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">Default Currency</label>
                            <select name="sys_currency" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 border outline-none focus:border-indigo-500 transition">
                                <option value="UGX" {{ ($settings['sys_currency'] ?? 'UGX') == 'UGX' ? 'selected' : '' }}>UGX</option>
                                <option value="USD" {{ ($settings['sys_currency'] ?? 'UGX') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ ($settings['sys_currency'] ?? 'UGX') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                <option value="KES" {{ ($settings['sys_currency'] ?? 'UGX') == 'KES' ? 'selected' : '' }}>KES</option>
                                <option value="NGN" {{ ($settings['sys_currency'] ?? 'UGX') == 'NGN' ? 'selected' : '' }}>NGN (₦)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">Timezone</label>
                            <select name="sys_timezone" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 border outline-none focus:border-indigo-500 transition">
                                <option value="UTC" {{ ($settings['sys_timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Africa/Nairobi" {{ ($settings['sys_timezone'] ?? '') == 'Africa/Nairobi' ? 'selected' : '' }}>Africa/Nairobi</option>
                                <option value="Africa/Lagos" {{ ($settings['sys_timezone'] ?? '') == 'Africa/Lagos' ? 'selected' : '' }}>Africa/Lagos</option>
                                <option value="America/New_York" {{ ($settings['sys_timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">Fiscal Year Start</label>
                            <input type="date" name="sys_fiscal_year" value="{{ $settings['sys_fiscal_year'] ?? '' }}" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 border outline-none focus:border-indigo-500 transition" />
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300 mb-1 block">SACCO Share Price (UGX)</label>
                            <input type="number" name="sys_share_price" value="{{ $settings['sys_share_price'] ?? '10000' }}" min="1" step="100" class="w-full bg-slate-50 border-slate-200 focus-visible:ring-indigo-500 dark:bg-slate-900 dark:border-slate-800 h-11 rounded-xl px-3 border outline-none focus:border-indigo-500 transition" placeholder="e.g. 10000" />
                            <p class="text-xs text-slate-500 mt-1">The fixed monetary value of a single share in the cooperative.</p>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="w-full sm:w-auto px-8 h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition flex items-center justify-center">
                            Save Changes
                        </button>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div id="content-notifications" class="tab-content w-full max-w-lg flex flex-col hidden">
                    <div class="mb-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Notifications</h2>
                        <p class="text-sm text-slate-500 mt-1">Configure automated communication settings.</p>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-200">Email Notifications</h3>
                                <p class="text-xs text-slate-500">Send automated emails for transactions</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="notif_email" value="0">
                                <input type="checkbox" name="notif_email" value="1" class="sr-only peer" {{ ($settings['notif_email'] ?? '0') == '1' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-200">SMS Notifications</h3>
                                <p class="text-xs text-slate-500">Send SMS alerts for important events</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="notif_sms" value="0">
                                <input type="checkbox" name="notif_sms" value="1" class="sr-only peer" {{ ($settings['notif_sms'] ?? '0') == '1' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="w-full sm:w-auto px-8 h-11 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition flex items-center justify-center">
                            Save Changes
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('content-' + tabName).classList.remove('hidden');

        document.querySelectorAll('.tab-button').forEach(el => {
            el.removeAttribute('data-state');
            el.classList.remove('bg-indigo-50', 'text-indigo-700', 'dark:bg-indigo-500/20', 'dark:text-indigo-300');
        });
        const activeBtn = document.getElementById('tab-' + tabName);
        activeBtn.setAttribute('data-state', 'active');
        activeBtn.classList.add('bg-indigo-50', 'text-indigo-700', 'dark:bg-indigo-500/20', 'dark:text-indigo-300');
    }

    // Initialize first tab
    document.addEventListener('DOMContentLoaded', () => {
        openTab('organization');
        // Re-initialize lucide icons for dynamic changes if necessary
        if(typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endsection
