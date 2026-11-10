<!DOCTYPE html><html><head><script src="https://cdn.tailwindcss.com"></script></head><body>
    <div class="w-full h-screen flex text-slate-700 dark:bg-blue-900 dark:text-slate-300">
        
        <!-- Left side splash -->
        <div class="lg:w-1/2 h-full rounded-e-full flex flex-col justify-center items-center bg-gray-200 max-md:hidden dark:bg-blue-950 dark:text-slate-300">
            <img src="{{ asset('assets/authenticate.svg') }}" alt="login" class="w-72 h-72" />
            <div class="max-w-96 text-left">
                <h1 class="text-4xl py-5">Welcome back</h1>
                <p class="text-lg">
                    Welcome back! We're glad to see you again. Please enter your
                    credentials to continue.
                </p>
            </div>
        </div>

        <!-- Right side form -->
        <div class="lg:w-1/2 w-full">
            <div class="w-full h-full flex flex-col items-center justify-center">
                
                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mb-2 text-center flex flex-col items-center">
                    <img src="{{ asset('assets/open-sacco.png') }}" alt="Open sacco logo" class="w-32 h-32" />
                    <h3 class="pb-3 text-lg">Welcome back!</h3>
                </div>

                <form class="w-72 space-y-4" method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 bg-transparent border dark:border-slate-600">
                        @error('email')
                            <p class="mt-2 text-red-500 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 bg-transparent border dark:border-slate-600">
                        @error('password')
                            <p class="mt-2 text-red-500 text-xs">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="flex justify-end items-center hover:underline">
                        @if (Route::has('password.request'))
                            <a class="text-xs" href="{{ route('password.request') }}">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 my-5">
                        Login
                    </button>
                </form>

                <div class="border-b border-slate-100 dark:border-slate-700 w-72 mt-7 mb-3"></div>
                
                <p>
                    Don't have an account?
                    <a class="ps-3 text-blue-700 dark:text-blue-400" href="{{ route('register') }}">
                        Sign Up
                    </a>
                </p>
            </div>
        </div>
    </div>
</body></html>
