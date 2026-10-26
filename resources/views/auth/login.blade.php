<x-guest-layout>
    <div class="bg-slate-900 border border-slate-800 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl backdrop-blur-xl">
        <!-- Header Branding WOLA MUSASHI -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/30 mb-3 font-black text-lg sm:text-xl">
                W
            </div>
            <h1 class="text-xl sm:text-2xl 2xl:text-3xl font-extrabold text-white tracking-tight">WOLA MUSASHI</h1>
            <p class="text-xs sm:text-sm font-semibold text-blue-400 mt-0.5">PORTAL WOLA</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email / NRP -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Email Akun / NRP Pegawai
                </label>
                <div class="relative">
                    <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all"
                           placeholder="nama@musashi.co.id atau NRP (contoh: 622)" />
                </div>
                <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <span>Login dengan Email atau NRP. Akun & password sinkron ke Spreadsheet.</span>
                </p>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-400 text-xs" />
            </div>

            <!-- Password -->
            <div class="mt-5">
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Kata Sandi</label>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="w-full px-4 py-3 bg-slate-800/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all"
                       placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-400 text-xs" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between mt-5">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-blue-600 focus:ring-blue-500 focus:ring-offset-slate-900" name="remember">
                    <span class="ms-2 text-xs text-slate-400">Ingat saya</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full mt-6 py-3.5 px-4 bg-blue-600 hover:bg-blue-500 active:scale-[0.99] text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center space-x-2">
                <span>Masuk ke Dashboard</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>
    </div>
</x-guest-layout>
