<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open SACCO - @yield('title', 'Dashboard')</title>
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Tailwind Config for exact colors -->
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              sacco: '#1ABB9C',
              'sacco-dark': '#169f85',
              sidebar: '#1a2332',
              'sidebar-dark': '#151c28'
            }
          }
        }
      }
    </script>
    <style>
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
<body class="flex h-screen w-full overflow-hidden bg-[#f7f7f7] font-sans antialiased text-slate-800 dark:bg-slate-950 dark:text-slate-100">

    <!-- SIDEBAR (Desktop) -->
    <div id="sidebar" class="hidden w-[240px] shrink-0 flex-col bg-sidebar text-slate-300 lg:flex">
        <div class="flex h-16 items-center px-4 mb-4 mt-2">
            <div class="flex items-center gap-3 font-semibold text-lg text-white">
                <div class="flex h-8 w-8 items-center justify-center rounded bg-sacco text-white font-bold text-xl">S</div>
                Open SACCO <small class="text-sacco text-xs font-normal ml-1">v4</small>
            </div>
        </div>
        
                <div class="flex-1 overflow-y-auto px-3 py-2 space-y-4">
            
            <!-- MAIN -->
            <div>
                <p class="px-3 text-xs font-bold tracking-wider text-slate-500 uppercase mb-2">Main</p>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Dashboard</span>
                    </a>
                    <a href="{{ route('members.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('members.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Members</span>
                    </a>
                    <a href="{{ route('accounts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('accounts.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="piggy-bank" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Accounts</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('transactions.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Transactions</span>
                    </a>
                    <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('loans.index') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="briefcase" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Loans</span>
                    </a>
                    <a href="{{ route('loans.defaulted') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('loans.defaulted') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400"></i>
                        <span class="font-medium text-sm text-slate-300">Defaulted Instalments</span>
                    </a>
                </div>
            </div>

            <!-- MANAGEMENT -->
            <div>
                <p class="px-3 text-xs font-bold tracking-wider text-slate-500 uppercase mb-2">Management</p>
                <div class="space-y-1">
                    <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('expenses.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Expenses</span>
                    </a>
                    <a href="{{ route('accounting.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('accounting.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">General Ledger</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('reports.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="pie-chart" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Reports</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('users.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Staff</span>
                    </a>
                    <a href="{{ route('sms.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('sms.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">SMS</span>
                    </a>
                    <a href="{{ route('emails.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('emails.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Emails</span>
                    </a>
                </div>
            </div>

            <!-- COMPANY -->
            <div>
                <p class="px-3 text-xs font-bold tracking-wider text-slate-500 uppercase mb-2">Company</p>
                <div class="space-y-1">
                    <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('documents.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Documents</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('settings.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Settings</span>
                    </a>
                    <a href="{{ route('help.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md {{ request()->routeIs('help.*') ? 'bg-sacco text-white' : 'hover:bg-white/10' }} transition">
                        <i data-lucide="help-circle" class="w-4 h-4"></i>
                        <span class="font-medium text-sm">Help & Support</span>
                    </a>
                </div>
            </div>
            
        </div>
        <div class="flex items-center h-16 px-4 border-t border-slate-700/50 bg-sidebar-dark">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-sacco text-white font-bold text-sm">
                {{ substr(Auth::user()->username ?? 'U', 0, 1) }}
            </div>
            <div class="ml-3 flex flex-col">
                <span class="text-sm font-medium text-white">{{ Auth::user()->username ?? 'User' }}</span>
                <span class="text-xs text-slate-400">{{ Auth::user()->role ?? 'Guest' }}</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="flex flex-1 flex-col overflow-hidden relative print-main">
        
        <!-- NAVBAR -->
        <header id="navbar" class="flex h-[60px] shrink-0 items-center justify-between bg-white px-4 md:px-6 shadow-sm border-b border-slate-200 dark:bg-slate-900 dark:border-slate-800">
            <div class="flex items-center gap-4 flex-1">
                <button class="lg:hidden text-slate-500 hover:text-slate-700">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                
                <div class="hidden lg:flex items-center text-sm font-medium text-slate-400">
                    <span class="hover:text-slate-600 cursor-pointer transition">Home</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                    <span class="text-slate-800 dark:text-slate-200">@yield('title', 'Dashboard')</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </button>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                    <i data-lucide="moon" class="w-5 h-5"></i>
                </button>
                
                <div class="h-6 w-px bg-slate-200 dark:bg-slate-700 mx-1"></div>
                
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 text-sm font-medium text-rose-500 hover:text-rose-600 transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
            @yield('content')
        </main>
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

      // PDF Print: isolate .pdf-only, hide everything else before printing
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