<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open SACCO - Sign In</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              'stellar-green': '#38CE3C',
              'stellar-dark': '#181824',
              'stellar-coral': '#FF4D6B',
              'stellar-yellow': '#FFDE73',
              'stellar-purple': '#8E32E9',
            }
          }
        }
      }
    </script>
</head>
<body class="bg-[#F4F5F7] text-slate-800 antialiased font-sans">
    <div class="w-full h-screen flex">
        
        <!-- Left Side Splash (Stellar Dark Brand) -->
        <div class="lg:w-1/2 h-full flex flex-col justify-center items-center bg-[#181824] text-white p-12 max-md:hidden relative overflow-hidden border-r border-[#222234]">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#38CE3C]/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-[#8E32E9]/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col items-center text-center max-w-md">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#38CE3C] to-[#20a324] text-white font-black text-3xl shadow-xl shadow-[#38CE3C]/30 mb-8">
                    S
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white mb-3">
                    Stellar <span class="text-[#38CE3C]">SACCO</span> System
                </h1>
                <p class="text-slate-400 text-sm leading-relaxed mb-8">
                    Enterprise cooperative banking, loan portfolio underwriting, guarantor matrices & double-entry statutory accounting.
                </p>
                <div class="grid grid-cols-2 gap-3 w-full text-xs text-left">
                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center gap-2.5">
                        <span class="h-2 w-2 rounded-full bg-[#38CE3C]"></span>
                        <span class="text-slate-300 font-medium">UMRA Compliant</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center gap-2.5">
                        <span class="h-2 w-2 rounded-full bg-[#8E32E9]"></span>
                        <span class="text-slate-300 font-medium">Offline-First SQLite</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Form (Stellar Clean Light Card) -->
        <div class="lg:w-1/2 w-full flex flex-col justify-center items-center p-6 sm:p-12">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200/80 p-8 sm:p-10">
                
                <div class="mb-6">
                    <div class="flex items-center gap-2 text-xs font-bold text-[#38CE3C] uppercase tracking-wider mb-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#38CE3C]"></span> Secure Access
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Sign In to Dashboard</h2>
                    <p class="text-xs text-slate-400 mt-1">Enter your registered administrative credentials</p>
                </div>

                @if (session('status'))
                    <div class="mb-4 p-3 rounded-xl text-xs font-semibold text-[#38CE3C] bg-[#38CE3C]/10 border border-[#38CE3C]/20">
                        {{ session('status') }}
                    </div>
                @endif

                <form class="space-y-4" method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email', 'admin@opensacco.com') }}" required autofocus
                            class="w-full h-11 px-4 text-sm rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition">
                        @error('email')
                            <p class="mt-1 text-[#FF4D6B] text-xs font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Password</label>
                            @if (Route::has('password.request'))
                                <a class="text-xs font-semibold text-[#8E32E9] hover:underline" href="{{ route('password.request') }}">
                                    Forgot?
                                </a>
                            @endif
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="w-full h-11 px-4 text-sm rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#38CE3C]/40 focus:border-[#38CE3C] transition">
                        @error('password')
                            <p class="mt-1 text-[#FF4D6B] text-xs font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full h-11 mt-2 flex items-center justify-center rounded-xl bg-[#38CE3C] hover:bg-[#2cb030] text-white text-sm font-bold shadow-md shadow-[#38CE3C]/25 transition">
                        Sign In to Stellar SACCO
                    </button>
                </form>

                <div class="my-6 border-t border-slate-100"></div>
                
                <p class="text-center text-xs text-slate-500">
                    Need new staff credentials?
                    <a class="font-bold text-[#8E32E9] hover:underline ml-1" href="{{ route('register') }}">
                        Contact System Admin
                    </a>
                </p>
            </div>
        </div>

    </div>
</body>
</html>
