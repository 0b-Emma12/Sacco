<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open SACCO - @yield('title', 'Dashboard')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind & UI Libraries -->
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script src="{{ asset('js/chart.min.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind Config with Exact Stellar Dashboard Colors -->
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
              // Exact Stellar Primary Colors
              'stellar-green': '#38CE3C',
              'stellar-dark': '#181824',
              'stellar-dark-card': '#1f1f2e',
              'stellar-dark-border': '#272738',

              // Exact Stellar Supporting Colors
              'stellar-coral': '#FF4D6B',
              'stellar-yellow': '#FFDE73',
              'stellar-purple': '#8E32E9',

              // SACCO Brand mappings
              sacco: '#38CE3C',
              'sacco-dark': '#2cb030',
              sidebar: '#181824',
              'sidebar-dark': '#12121b',
              'sidebar-hover': '#232334',
            }
          }
        }
      }
    </script>

    <style>
      body {
        font-family: 'Inter', sans-serif;
      }
      /* Custom subtle scrollbars */
      ::-webkit-scrollbar {
        width: 5px;
        height: 5px;
      }
      ::-webkit-scrollbar-track {
        background: transparent;
      }
      ::-webkit-scrollbar-thumb {
        background: rgba(140, 150, 170, 0.25);
        border-radius: 9999px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background: rgba(140, 150, 170, 0.45);
      }

      /* PDF Print isolation */
      @media print {
        @page {
          size: 210mm 297mm;
          margin: 15mm 18mm;
        }
        html, body {
          display: block !important;
          width: 210mm !important;
          height: auto !important;
          overflow: visible !important;
          margin: 0 !important;
          padding: 0 !important;
          background: #fff !important;
        }
        body > * { display: none !important; }
        .pdf-only {
          display: block !important;
          width: 174mm !important;
          margin: 0 auto !important;
          padding: 0 !important;
          background: #fff !important;
          color: #000 !important;
        }
        *::-webkit-scrollbar { display: none !important; }
      }
    </style>
</head>
<body class="bg-[#F4F5F7] text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100 h-screen w-full overflow-hidden"
      x-data="{ sidebarCollapsed: false, mobileSidebarOpen: false }">

    <div class="flex h-screen w-full overflow-hidden">

        <!-- MOBILE BACKDROP -->
        <div x-show="mobileSidebarOpen" 
             @click="mobileSidebarOpen = false"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden">
        </div>

        <!-- ==================== STELLAR SIDEBAR ==================== -->
        <aside id="sidebar"
               :class="{
                   'w-[260px]': !sidebarCollapsed,
                   'w-[78px]': sidebarCollapsed,
                   '-translate-x-full lg:translate-x-0': !mobileSidebarOpen,
                   'translate-x-0': mobileSidebarOpen
               }"
               class="fixed inset-y-0 left-0 z-50 flex flex-col bg-[#181824] text-slate-300 transition-all duration-300 ease-in-out lg:static lg:translate-x-0 shrink-0 border-r border-[#222234]">

            <!-- BRAND HEADER -->
            <div class="flex h-[68px] items-center justify-between px-5 border-b border-[#222234]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                    <!-- Logo Icon with Stellar style -->
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#38CE3C] to-[#20a324] text-white font-extrabold text-xl shadow-lg shadow-[#38CE3C]/20">
                        S
                    </div>
                    <div x-show="!sidebarCollapsed" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-x-2"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="flex flex-col whitespace-nowrap">
                        <span class="font-bold text-lg text-white tracking-tight flex items-center gap-1.5">
                            Stellar <span class="text-[11px] font-semibold text-[#38CE3C] uppercase tracking-wider px-1.5 py-0.5 rounded bg-[#38CE3C]/10 border border-[#38CE3C]/20">SACCO</span>
                        </span>
                    </div>
                </a>

                <!-- Collapse Toggle Button (Desktop) -->
                <button @click="sidebarCollapsed = !sidebarCollapsed" 
                        class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition"
                        title="Toggle Sidebar width">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <!-- Mobile Close Button -->
                <button @click="mobileSidebarOpen = false" class="lg:hidden p-1.5 text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- USER PROFILE SECTION (Stellar Reference) -->
            <div class="px-4 py-4 border-b border-[#222234] bg-[#14141e]/70">
                <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <div class="relative shrink-0">
                        <img src="{{ asset('assets/profile-placeholder.png') }}" 
                             alt="User Avatar" 
                             class="h-10 w-10 rounded-full object-cover border-2 border-[#38CE3C]"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->username ?? 'Admin') }}&background=38CE3C&color=fff'">
                        <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-[#38CE3C] ring-2 ring-[#181824]"></span>
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex-1 overflow-hidden">
                        <h4 class="text-sm font-semibold text-white truncate">{{ Auth::user()->username ?? 'Henry Klein' }}</h4>
                        <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->role === 'AC' ? 'Administrator' : (Auth::user()->role ?? 'Administrator') }}</p>
                    </div>
                    <div x-show="!sidebarCollapsed" class="flex items-center gap-1">
                        <a href="{{ route('emails.index') }}" class="p-1 rounded text-slate-400 hover:text-[#38CE3C] hover:bg-white/5 transition" title="Messages">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- NAVIGATION ITEMS (Stellar Categorized List) -->
            <div class="flex-1 overflow-y-auto px-3 py-3 space-y-4">

                <!-- CATEGORY: DASHBOARD -->
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 pt-1 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Dashboard</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" 
                           class="group relative flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Dashboard">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate flex-1">Dashboard Overview</span>
                            <span x-show="!sidebarCollapsed && {{ request()->routeIs('dashboard') ? 'true' : 'false' }}" class="shrink-0 text-[#38CE3C]">
                                <i data-lucide="tv" class="w-3.5 h-3.5"></i>
                            </span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: FINANCIAL OPERATIONS -->
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Financial Operations</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('members.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('members.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Members">
                            <i data-lucide="users" class="w-4 h-4 shrink-0 {{ request()->routeIs('members.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Members Register</span>
                        </a>
                        <a href="{{ route('accounts.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('accounts.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Accounts">
                            <i data-lucide="wallet" class="w-4 h-4 shrink-0 {{ request()->routeIs('accounts.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Savings Accounts</span>
                        </a>
                        <a href="{{ route('transactions.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('transactions.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Transactions">
                            <i data-lucide="arrow-left-right" class="w-4 h-4 shrink-0 {{ request()->routeIs('transactions.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Transactions</span>
                        </a>
                        <a href="{{ route('loans.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('loans.index') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Loans">
                            <i data-lucide="credit-card" class="w-4 h-4 shrink-0 {{ request()->routeIs('loans.index') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Loan Management</span>
                        </a>
                        <a href="{{ route('loans.defaulted') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('loans.defaulted') ? 'bg-[#FF4D6B]/20 text-[#FF4D6B] font-semibold border-l-4 border-[#FF4D6B]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Defaulted Loans">
                            <i data-lucide="alert-octagon" class="w-4 h-4 shrink-0 text-[#FF4D6B]"></i>
                            <span x-show="!sidebarCollapsed" class="truncate text-[#FF4D6B]">Defaulted Instalments</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: MANAGEMENT & ACCOUNTING -->
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Accounting & Reports</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('expenses.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('expenses.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Expenses">
                            <i data-lucide="receipt" class="w-4 h-4 shrink-0 {{ request()->routeIs('expenses.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Operating Expenses</span>
                        </a>
                        <a href="{{ route('accounting.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('accounting.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="General Ledger">
                            <i data-lucide="book-open" class="w-4 h-4 shrink-0 {{ request()->routeIs('accounting.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">General Ledger</span>
                        </a>
                        <a href="{{ route('reports.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Reports">
                            <i data-lucide="pie-chart" class="w-4 h-4 shrink-0 {{ request()->routeIs('reports.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Regulatory Reports</span>
                        </a>
                        <a href="{{ route('users.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Staff Management">
                            <i data-lucide="shield" class="w-4 h-4 shrink-0 {{ request()->routeIs('users.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Staff & Roles</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: COMMUNICATIONS & TOOLS -->
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">Communications</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('sms.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('sms.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="SMS Alerts">
                            <i data-lucide="message-square" class="w-4 h-4 shrink-0 {{ request()->routeIs('sms.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">SMS Broadcast</span>
                        </a>
                        <a href="{{ route('emails.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('emails.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Emails">
                            <i data-lucide="send" class="w-4 h-4 shrink-0 {{ request()->routeIs('emails.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Email Notifications</span>
                        </a>
                    </div>
                </div>

                <!-- CATEGORY: SETTINGS & SUPPORT -->
                <div>
                    <div x-show="!sidebarCollapsed" class="px-3 pt-2 pb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#38CE3C]">System</p>
                    </div>
                    <div class="space-y-1">
                        <a href="{{ route('documents.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('documents.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Documents">
                            <i data-lucide="folder" class="w-4 h-4 shrink-0 {{ request()->routeIs('documents.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Documents Vault</span>
                        </a>
                        <a href="{{ route('settings.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Settings">
                            <i data-lucide="settings" class="w-4 h-4 shrink-0 {{ request()->routeIs('settings.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Settings</span>
                        </a>
                        <a href="{{ route('help.index') }}" 
                           class="group flex items-center gap-3.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all {{ request()->routeIs('help.*') ? 'bg-[#38CE3C]/15 text-[#38CE3C] font-semibold border-l-4 border-[#38CE3C]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}"
                           :class="sidebarCollapsed ? 'justify-center px-0' : ''"
                           title="Help & Support">
                            <i data-lucide="help-circle" class="w-4 h-4 shrink-0 {{ request()->routeIs('help.*') ? 'text-[#38CE3C]' : 'text-slate-400 group-hover:text-white' }}"></i>
                            <span x-show="!sidebarCollapsed" class="truncate">Help & Knowledge</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- FOOTER STATUS BAR -->
            <div class="p-3 border-t border-[#222234] bg-[#12121b]">
                <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
                    <div x-show="!sidebarCollapsed" class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-[#38CE3C] animate-pulse"></span>
                        <span class="text-[11px] font-medium text-slate-400">Offline SQLite Online</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" 
                                class="p-1.5 rounded-lg text-slate-400 hover:text-[#FF4D6B] hover:bg-[#FF4D6B]/10 transition"
                                title="Sign Out">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- ==================== MAIN CONTENT WRAPPER ==================== -->
        <div class="flex flex-1 flex-col overflow-hidden relative">

            <!-- ==================== TOP NAVIGATION BAR ==================== -->
            <header class="flex h-[68px] shrink-0 items-center justify-between bg-white px-4 md:px-7 border-b border-slate-200/80 shadow-xs dark:bg-[#181824] dark:border-[#272738] z-30">
                
                <!-- Left: Welcome Message / Page Title (Stellar reference) -->
                <div class="flex items-center gap-4">
                    <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>

                    <div>
                        <h1 class="text-base sm:text-lg font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <span>Welcome to Open SACCO!</span>
                        </h1>
                        <p class="text-xs text-slate-400 hidden sm:block">Managing financial cooperatives & social collateral</p>
                    </div>
                </div>

                <!-- Right: Search, Notifications, Language & User Pill (Stellar reference) -->
                <div class="flex items-center gap-2 sm:gap-4">

                    <!-- Search Input (Stellar style) -->
                    <div class="relative hidden md:block">
                        <input type="text" 
                               placeholder="Search members, accounts..." 
                               class="w-48 lg:w-64 h-9 pl-9 pr-3 text-xs rounded-full bg-slate-100 border-none text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 transition dark:bg-slate-800 dark:text-slate-200">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>

                    <!-- Shortcut Icons (Stellar reference: search, cart, chart, notification) -->
                    <a href="{{ route('reports.index') }}" 
                       class="p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition dark:hover:bg-slate-800" 
                       title="Analytics & Reports">
                        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    </a>

                    <!-- Notification Bell with Red Badge count (Stellar reference) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="relative p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition dark:hover:bg-slate-800"
                                title="Notifications">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#FF4D6B] text-[9px] font-bold text-white shadow-xs">
                                3
                            </span>
                        </button>
                        
                        <!-- Dropdown -->
                        <div x-show="open" 
                             @click.away="open = false" 
                             style="display: none;" 
                             class="absolute right-0 mt-2 w-72 rounded-xl bg-white p-3 shadow-xl border border-slate-100 dark:bg-[#181824] dark:border-[#272738] z-50">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Notifications</span>
                                <span class="text-[10px] text-[#38CE3C] font-semibold">3 New</span>
                            </div>
                            <div class="py-2 space-y-2 text-xs">
                                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/50 flex gap-2">
                                    <span class="h-2 w-2 rounded-full bg-[#38CE3C] mt-1 shrink-0"></span>
                                    <div>
                                        <p class="font-medium text-slate-800 dark:text-slate-200">System Backup Completed</p>
                                        <p class="text-[10px] text-slate-400">Offline SQLite database synced</p>
                                    </div>
                                </div>
                                <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/50 flex gap-2">
                                    <span class="h-2 w-2 rounded-full bg-[#FF4D6B] mt-1 shrink-0"></span>
                                    <div>
                                        <p class="font-medium text-slate-800 dark:text-slate-200">Default Check Triggered</p>
                                        <p class="text-[10px] text-slate-400">Auto-recovery protocol active</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Language Indicator (Stellar reference) -->
                    <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 font-medium px-2 py-1.5 rounded-lg border border-slate-200/80 bg-slate-50 dark:bg-slate-800/50 dark:border-slate-700">
                        <span>🇺🇸</span>
                        <span>English</span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                    </div>

                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-800 mx-1"></div>

                    <!-- User Profile Dropdown Pill (Stellar reference) -->
                    <div class="relative" x-data="{ userMenu: false }">
                        <button @click="userMenu = !userMenu" 
                                class="flex items-center gap-2.5 p-1 sm:px-2.5 sm:py-1.5 rounded-full sm:rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <img src="{{ asset('assets/profile-placeholder.png') }}" 
                                 alt="Avatar" 
                                 class="h-7 w-7 sm:h-8 sm:w-8 rounded-full object-cover border border-[#38CE3C]"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->username ?? 'Admin') }}&background=38CE3C&color=fff'">
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 hidden sm:inline-block">
                                {{ Auth::user()->username ?? 'Henry Klein' }}
                            </span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 hidden sm:inline-block"></i>
                        </button>

                        <div x-show="userMenu" 
                             @click.away="userMenu = false"
                             style="display: none;" 
                             class="absolute right-0 mt-2 w-48 rounded-xl bg-white p-1.5 shadow-xl border border-slate-100 dark:bg-[#181824] dark:border-[#272738] z-50">
                            <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800 text-xs">
                                <p class="font-bold text-slate-800 dark:text-slate-100">{{ Auth::user()->username ?? 'Admin User' }}</p>
                                <p class="text-slate-400 truncate">{{ Auth::user()->email ?? 'admin@opensacco.com' }}</p>
                            </div>
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i> Profile & Roles
                            </a>
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800 transition">
                                <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Preferences
                            </a>
                            <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium text-[#FF4D6B] hover:bg-[#FF4D6B]/10 transition text-left">
                                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            </header>

            <!-- ==================== PAGE CONTENT BODY ==================== -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-7">
                @yield('content')
            </main>

        </div>

    </div>

    <!-- Initialize Lucide Icons -->
    <script>
      lucide.createIcons();

      function exportTableToCSV(filename) {
          var csv = [];
          var table = document.querySelector("table");
          if(!table) return;
          var rows = table.querySelectorAll("tr");
          
          for (var i = 0; i < rows.length; i++) {
              var row = [], cols = rows[i].querySelectorAll("td, th");
              for (var j = 0; j < cols.length; j++) 
                  row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
              csv.push(row.join(","));        
          }

          var csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
          var downloadLink = document.createElement("a");
          downloadLink.download = filename;
          downloadLink.href = window.URL.createObjectURL(csvFile);
          downloadLink.style.display = "none";
          document.body.appendChild(downloadLink);
          downloadLink.click();
      }

      // Classic PDF Print logic
      var _printClone = null;
      var _hiddenNodes = [];

      window.addEventListener('beforeprint', function() {
          var pdfEl = document.querySelector('.pdf-only');
          if (!pdfEl) return;
          _printClone = pdfEl.cloneNode(true);
          _printClone.style.display = 'block';
          _printClone.id = '__pdf_print_root';
          _hiddenNodes = Array.from(document.body.children);
          _hiddenNodes.forEach(function(el) { el.style.display = 'none'; });
          document.body.appendChild(_printClone);
          document.body.style.display = 'block';
          document.body.style.margin = '0';
          document.body.style.padding = '0';
          document.body.style.background = '#fff';
          document.body.style.overflow = 'visible';
      });

      window.addEventListener('afterprint', function() {
          var clone = document.getElementById('__pdf_print_root');
          if (clone) document.body.removeChild(clone);
          _hiddenNodes.forEach(function(el) { el.style.display = ''; });
          document.body.style.display = '';
          document.body.style.margin = '';
          document.body.style.padding = '';
          document.body.style.background = '';
          document.body.style.overflow = '';
          _hiddenNodes = [];
          _printClone = null;
      });
    </script>
</body>
</html>