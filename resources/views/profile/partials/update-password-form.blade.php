<section>
    <header class="pb-5 border-b border-slate-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">
                        {{ __('Perbarui Kata Sandi') }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        {{ __('Gunakan kata sandi yang kuat dan aman untuk akun Anda.') }}
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                Sinkron Password
            </span>
        </div>

        <!-- Info Sync Password -->
        <div class="mt-4 p-3 rounded-xl bg-amber-50/70 border border-amber-100 flex items-start space-x-2.5 text-xs text-amber-900 leading-relaxed">
            <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong class="font-semibold text-amber-950">Sinkronisasi Kata Sandi ke Spreadsheet:</strong>
                Saat Anda memperbarui kata sandi di sini, kata sandi baru otomatis tersimpan di database dan langsung disinkronkan ke kolom Password pada sheet <strong>Pengguna</strong> di Google Spreadsheet.
            </div>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <!-- Kata Sandi Saat Ini -->
        <div>
            <label for="update_password_current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Kata Sandi Saat Ini') }} <span class="text-rose-500">*</span>
            </label>
            <input id="update_password_current_password"
                   name="current_password"
                   type="password"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm text-slate-800 transition placeholder:text-slate-400 font-medium shadow-2xs"
                   autocomplete="current-password"
                   placeholder="Masukkan kata sandi lama Anda..." />
            @if($errors->updatePassword->get('current_password'))
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <!-- Kata Sandi Baru -->
        <div>
            <label for="update_password_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Kata Sandi Baru') }} <span class="text-rose-500">*</span>
            </label>
            <input id="update_password_password"
                   name="password"
                   type="password"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm text-slate-800 transition placeholder:text-slate-400 font-medium shadow-2xs"
                   autocomplete="new-password"
                   placeholder="Minimal 8 karakter aman..." />
            @if($errors->updatePassword->get('password'))
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <!-- Konfirmasi Kata Sandi Baru -->
        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Konfirmasi Kata Sandi Baru') }} <span class="text-rose-500">*</span>
            </label>
            <input id="update_password_password_confirmation"
                   name="password_confirmation"
                   type="password"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm text-slate-800 transition placeholder:text-slate-400 font-medium shadow-2xs"
                   autocomplete="new-password"
                   placeholder="Ulangi kata sandi baru..." />
            @if($errors->updatePassword->get('password_confirmation'))
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <!-- Tombol Simpan Kata Sandi -->
        <div class="pt-3 flex items-center justify-between gap-4">
            <button type="submit"
                    class="inline-flex items-center px-5 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-bold rounded-xl shadow-md shadow-amber-600/25 transition cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                {{ __('Perbarui Kata Sandi') }}
            </button>

            @if (session('status') === 'password-updated')
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     x-init="setTimeout(() => show = false, 4000)"
                     class="flex items-center text-xs font-bold text-emerald-600">
                    <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Kata sandi tersimpan!') }}
                </div>
            @endif
        </div>
    </form>
</section>
