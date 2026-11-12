<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $orgName }} - @yield('title', 'Dashboard')</title>

    <!-- Google Fonts: Inter (CSS only) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Compiled stylesheet (no JavaScript) -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-[#F4F5F7] text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100 {{ request('print') ? 'print-mode' : '' }}">

    <!-- Pure CSS state toggles -->
    <input type="checkbox" id="sidebarToggle" class="sidebar-state" aria-hidden="true" tabindex="-1">
    <input type="checkbox" id="mobileSidebarToggle" class="sidebar-state" aria-hidden="true" tabindex="-1">

    <!-- Mobile backdrop: clicking closes the drawer -->
    <label for="mobileSidebarToggle" class="sidebar-backdrop" aria-hidden="true"></label>

    <div class="app-shell">

        <!-- ==================== SIDEBAR ==================== -->
        <aside class="sidebar">

            <!-- BRAND HEADER -->
            <div class="sidebar-brand flex h-[68px] items-center justify-between px-5 border-b border-[#222234]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#38CE3C] to-[#20a324] text-white font-extrabold text-xl shadow-lg shadow-[#38CE3C]/20">
                        {{ strtoupper(substr($orgName, 0, 1)) }}
                    </div>
                    <div class="sidebar-brand-text flex flex-col whitespace-nowrap">
                        <span class="font-bold text-lg text-white tracking-tight truncate max-w-[150px]" title="{{ $orgName }}">
                            {{ $orgName }}
                        </span>
                    </div>
                </a>

                <!-- Collapse Toggle (Desktop) -->
                <label for="sidebarToggle"
                       class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition cursor-pointer"
                       title="Toggle Sidebar width">
                    <x-icon name="menu" class="w-5 h-5"/>
                </label>

                <!-- Mobile Close -->
                <label for="mobileSidebarToggle" class="lg:hidden p-1.5 text-slate-400 hover:text-white cursor-pointer">
                    <x-icon name="x" class="w-5 h-5"/>
                </label>
            </div>

            <!-- USER PROFILE SECTION -->
            <div class="px-4 py-4 border-b border-[#222234] bg-[#14141e]/70">
                <div class="sidebar-profile flex items-center gap-3">
                    <div class="relative shrink-0">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#38CE3C] text-white text-sm font-bold border-2 border-[#38CE3C]">
                            {{ strtoupper(substr(Auth::user()->username ?? 'Admin', 0, 2)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-[#38CE3C] ring-2 ring-[#181824]"></span>
                    </div>
                    <div class="sidebar-profile-meta flex-1 overflow-hidden">
                        <h4 class="text-sm font-semibold text-white truncate">{{ Auth::user()->username ?? 'Henry Klein' }}</h4>
                        <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->role_label }}</p>
                    </div>
                    <div class="sidebar-profile-meta flex items-center gap-1">
                        <a href="{{ route('emails.index') }}" class="p-1 rounded text-slate-400 hover:text-[#38CE3C] hover:bg-white/5 transition" title="Messages">
                            <x-icon name="mail" class="w-4 h-4"/>
                        </a>
                    </div>
                </div>
            </div>

            <!-- NAVIGATION -->
            <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-4">

                <!-- CATEGORY: DASHBOARD -->
                <div>
                    <div class="sidebar-section-label px-3 pt-1 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Dashboard</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}"
                           class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                           title="Dashboard">
                            <x-icon name="layout-dashboard"/>
                            <span class="sidebar-label truncate flex-1">Dashboard Overview</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: FINANCIAL OPERATIONS -->
                <div>
                    <div class="sidebar-section-label px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Financial Operations</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('members.index') }}"
                           class="sidebar-link {{ request()->routeIs('members.*') ? 'is-active' : '' }}"
                           title="Members">
                            <x-icon name="users"/>
                            <span class="sidebar-label truncate">Members Register</span>
                        </a>
                        <a href="{{ route('accounts.index') }}"
                           class="sidebar-link {{ request()->routeIs('accounts.*') ? 'is-active' : '' }}"
                           title="Accounts">
                            <x-icon name="wallet"/>
                            <span class="sidebar-label truncate">Savings Accounts</span>
                        </a>
                        <a href="{{ route('transactions.index') }}"
                           class="sidebar-link {{ request()->routeIs('transactions.*') ? 'is-active' : '' }}"
                           title="Transactions">
                            <x-icon name="arrow-left-right"/>
                            <span class="sidebar-label truncate">Transactions</span>
                        </a>
                        <a href="{{ route('loans.index') }}"
                           class="sidebar-link {{ request()->routeIs('loans.index') ? 'is-active' : '' }}"
                           title="Loans">
                            <x-icon name="credit-card"/>
                            <span class="sidebar-label truncate">Loan Management</span>
                        </a>
                        <a href="{{ route('loans.defaulted') }}"
                           class="sidebar-link is-danger {{ request()->routeIs('loans.defaulted') ? 'is-active' : '' }}"
                           title="Defaulted Loans">
                            <x-icon name="alert-octagon"/>
                            <span class="sidebar-label truncate">Defaulted Instalments</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: MEMBER SERVICES -->
                <div>
                    <div class="sidebar-section-label px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Member Services</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('member-services.death-claims.index') }}"
                           class="sidebar-link {{ request()->routeIs('member-services.death-claims.*') ? 'is-active' : '' }}"
                           title="Death Claims">
                            <x-icon name="user-x"/>
                            <span class="sidebar-label truncate">Death Claims</span>
                        </a>
                        <a href="{{ route('member-services.share-transfers.index') }}"
                           class="sidebar-link {{ request()->routeIs('member-services.share-transfers.*') ? 'is-active' : '' }}"
                           title="Share Transfers">
                            <x-icon name="arrow-left-right"/>
                            <span class="sidebar-label truncate">Share Transfers</span>
                        </a>
                        <a href="{{ route('member-services.standing-orders.index') }}"
                           class="sidebar-link {{ request()->routeIs('member-services.standing-orders.*') ? 'is-active' : '' }}"
                           title="Standing Orders">
                            <x-icon name="refresh-cw"/>
                            <span class="sidebar-label truncate">Standing Orders</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: MANAGEMENT & ACCOUNTING -->
                <div>
                    <div class="sidebar-section-label px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Accounting &amp; Reports</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('expenses.index') }}"
                           class="sidebar-link {{ request()->routeIs('expenses.*') ? 'is-active' : '' }}"
                           title="Expenses">
                            <x-icon name="receipt"/>
                            <span class="sidebar-label truncate">Operating Expenses</span>
                        </a>
                        <a href="{{ route('accounting.index') }}"
                           class="sidebar-link {{ request()->routeIs('accounting.*') ? 'is-active' : '' }}"
                           title="General Ledger">
                            <x-icon name="book-open"/>
                            <span class="sidebar-label truncate">General Ledger</span>
                        </a>
                        <a href="{{ route('treasury.dividends.index') }}"
                           class="sidebar-link {{ request()->routeIs('treasury.dividends.*') ? 'is-active' : '' }}"
                           title="Dividends">
                            <x-icon name="coins"/>
                            <span class="sidebar-label truncate">Dividends &amp; Surplus</span>
                        </a>
                        <a href="{{ route('treasury.petty-cash.index') }}"
                           class="sidebar-link {{ request()->routeIs('treasury.petty-cash.*') ? 'is-active' : '' }}"
                           title="Petty Cash">
                            <x-icon name="wallet"/>
                            <span class="sidebar-label truncate">Petty Cash Imprest</span>
                        </a>
                        <a href="{{ route('treasury.bank-reconciliations.index') }}"
                           class="sidebar-link {{ request()->routeIs('treasury.bank-reconciliations.*') ? 'is-active' : '' }}"
                           title="Bank Reconciliation">
                            <x-icon name="file-spreadsheet"/>
                            <span class="sidebar-label truncate">Bank Reconciliation</span>
                        </a>
                        <a href="{{ route('reports.index') }}"
                           class="sidebar-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}"
                           title="Reports">
                            <x-icon name="pie-chart"/>
                            <span class="sidebar-label truncate">Regulatory Reports</span>
                        </a>
                        <a href="{{ route('governance.index') }}"
                           class="sidebar-link {{ request()->routeIs('governance.*') ? 'is-active' : '' }}"
                           title="Governance">
                            <x-icon name="landmark"/>
                            <span class="sidebar-label truncate">Governance</span>
                        </a>
                        <a href="{{ route('users.index') }}"
                           class="sidebar-link {{ request()->routeIs('users.*') ? 'is-active' : '' }}"
                           title="Staff Management">
                            <x-icon name="shield"/>
                            <span class="sidebar-label truncate">Staff &amp; Roles</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: COMMUNICATIONS -->
                <div>
                    <div class="sidebar-section-label px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Communications</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('sms.index') }}"
                           class="sidebar-link {{ request()->routeIs('sms.*') ? 'is-active' : '' }}"
                           title="SMS Alerts">
                            <x-icon name="message-square"/>
                            <span class="sidebar-label truncate">SMS Broadcast</span>
                        </a>
                        <a href="{{ route('emails.index') }}"
                           class="sidebar-link {{ request()->routeIs('emails.*') ? 'is-active' : '' }}"
                           title="Emails">
                            <x-icon name="send"/>
                            <span class="sidebar-label truncate">Email Notifications</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: SYSTEM -->
                <div>
                    <div class="sidebar-section-label px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">System</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('documents.index') }}"
                           class="sidebar-link {{ request()->routeIs('documents.*') ? 'is-active' : '' }}"
                           title="Documents">
                            <x-icon name="folder"/>
                            <span class="sidebar-label truncate">Documents Vault</span>
                        </a>
                        <a href="{{ route('settings.index') }}"
                           class="sidebar-link {{ request()->routeIs('settings.*') ? 'is-active' : '' }}"
                           title="Settings">
                            <x-icon name="settings"/>
                            <span class="sidebar-label truncate">Settings</span>
                        </a>
                        <a href="{{ route('help.index') }}"
                           class="sidebar-link {{ request()->routeIs('help.*') ? 'is-active' : '' }}"
                           title="Help & Support">
                            <x-icon name="help-circle"/>
                            <span class="sidebar-label truncate">Help &amp; Knowledge</span>
                        </a>
                    </div>
                </div>

            </nav>

            <!-- FOOTER STATUS BAR -->
            <div class="p-3 border-t border-[#222234] bg-[#12121b]">
                <div class="sidebar-footer flex items-center justify-between gap-3">
                    <div class="sidebar-footer-status flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#38CE3C] animate-pulse"></span>
                        <span class="text-[11px] font-medium text-slate-400">{{ strtoupper(config('database.default')) }} Online</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-[#FF4D6B] hover:bg-[#FF4D6B]/10 transition"
                                title="Sign Out">
                            <x-icon name="log-out" class="w-4 h-4"/>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- ==================== MAIN CONTENT WRAPPER ==================== -->
        <div class="main-wrap">

            <!-- TOP NAVIGATION BAR -->
            <header class="app-header flex h-[68px] shrink-0 items-center justify-between bg-white px-4 md:px-7 border-b border-slate-200/80 dark:bg-[#181824] dark:border-[#272738] z-30">

                <div class="flex items-center gap-4">
                    <label for="mobileSidebarToggle" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer">
                        <x-icon name="menu" class="w-6 h-6"/>
                    </label>

                    <div>
                        <h1 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span>Welcome to {{ $orgName }}!</span>
                        </h1>
                        <p class="text-xs text-slate-400 hidden sm:block">Managing financial cooperatives &amp; social collateral</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-4">

                    <!-- Search (server-side GET) -->
                    <form method="GET" action="{{ route('members.index') }}" class="relative hidden md:block m-0">
                        <input type="search"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Search members, accounts..."
                               class="w-48 lg:w-64 h-9 pl-9 pr-3 text-xs rounded-full bg-slate-100 border-none text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 transition dark:bg-slate-800 dark:text-slate-200">
                        <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"/>
                    </form>

                    <a href="{{ route('reports.index') }}"
                       class="p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition dark:hover:bg-slate-800"
                       title="Analytics & Reports">
                        <x-icon name="bar-chart-3" class="w-4 h-4"/>
                    </a>

                    <!-- Notifications (native details/summary) -->
                    <details class="dropdown">
                        <summary class="relative p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition dark:hover:bg-slate-800"
                                 title="Notifications">
                            <x-icon name="bell" class="w-4 h-4"/>
                            @if($headerNotificationCount > 0)
                                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#FF4D6B] text-[9px] font-bold text-white">
                                    {{ $headerNotificationCount }}
                                </span>
                            @endif
                        </summary>
                        <div class="dropdown-panel w-72 rounded-xl bg-white p-3 shadow-xl border border-slate-100 dark:bg-[#181824] dark:border-[#272738]">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Notifications</span>
                                @if($headerNotificationCount > 0)
                                    <span class="text-[10px] text-[#38CE3C] font-semibold">{{ $headerNotificationCount }} open</span>
                                @endif
                            </div>
                            @if($headerNotificationCount > 0)
                                <div class="py-2 space-y-2 text-xs">
                                    @foreach($headerNotifications as $notification)
                                        <a href="{{ $notification['url'] }}" class="block p-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition dark:bg-slate-900/50 dark:hover:bg-slate-800">
                                            <div class="flex gap-2">
                                                <span class="h-2 w-2 rounded-full mt-1 shrink-0 {{ match($notification['tone']) {
                                                    'amber' => 'bg-[#FFDE73]',
                                                    'red' => 'bg-[#FF4D6B]',
                                                    default => 'bg-[#38CE3C]',
                                                } }}"></span>
                                                <div>
                                                    <p class="font-medium text-slate-800 dark:text-slate-200">{{ $notification['title'] }}</p>
                                                    <p class="text-[10px] text-slate-400">{{ $notification['detail'] }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-6 text-center text-xs text-slate-400">
                                    <x-icon name="bell" class="w-6 h-6 mx-auto mb-2 opacity-50"/>
                                    <p class="font-medium text-slate-500 dark:text-slate-400">No notifications yet.</p>
                                    <p class="text-[10px] mt-0.5">System alerts will appear here.</p>
                                </div>
                            @endif
                        </div>
                    </details>

                    <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 font-medium px-2 py-1.5 rounded-lg border border-slate-200/80 bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700">
                        <span>EN</span>
                        <span>English</span>
                        <x-icon name="chevron-down" class="w-3 h-3 text-slate-400"/>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

                    <!-- User menu (native details/summary) -->
                    <details class="dropdown">
                        <summary class="flex items-center gap-2.5 p-1 sm:px-2.5 sm:py-1.5 rounded-full sm:rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <span class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-full bg-[#38CE3C] text-white text-[11px] font-bold border border-[#38CE3C]">
                                {{ strtoupper(substr(Auth::user()->username ?? 'Admin', 0, 2)) }}
                            </span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 hidden sm:inline-block">
                                {{ Auth::user()->username ?? 'Henry Klein' }}
                            </span>
                            <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400 hidden sm:inline-block"/>
                        </summary>

                        <div class="dropdown-panel w-48 rounded-xl bg-white p-1.5 shadow-xl border border-slate-100 dark:bg-[#181824] dark:border-[#272738]">
                            <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800 text-xs">
                                <p class="font-bold text-slate-800 dark:text-slate-100">{{ Auth::user()->username ?? 'Admin User' }}</p>
                                <p class="text-slate-400 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                                <x-icon name="user" class="w-3.5 h-3.5"/> Profile &amp; Roles
                            </a>
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                                <x-icon name="sliders" class="w-3.5 h-3.5"/> Preferences
                            </a>
                            <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-[#FF4D6B] hover:bg-[#FF4D6B]/10 transition text-left">
                                    <x-icon name="log-out" class="w-3.5 h-3.5"/> Logout
                                </button>
                            </form>
                        </div>
                    </details>

                </div>

            </header>

            <!-- PAGE CONTENT -->
            <main class="app-main">
                @yield('content')
            </main>

        </div>

    </div>

</body>
</html>
