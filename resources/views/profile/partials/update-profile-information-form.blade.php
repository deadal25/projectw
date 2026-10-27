<section>
    <header class="pb-5 border-b border-slate-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">
                        {{ __('Informasi Profil') }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        {{ __('Perbarui data diri, username / NRP, dan email akun Anda.') }}
                    </p>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                Sheet Pengguna
            </span>
        </div>

        <!-- Info Sync Spreadsheet -->
        <div class="mt-4 p-3 rounded-xl bg-blue-50/70 border border-blue-100 flex items-start space-x-2.5 text-xs text-blue-900 leading-relaxed">
            <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <strong class="font-semibold text-blue-950">Sinkronisasi Otomatis Google Spreadsheet:</strong>
                Data profil Anda terhubung langsung ke Google Spreadsheet sheet <strong>Pengguna</strong> @if($user->hasRole('ketua')) dan sheet <strong>tim_penilai</strong> @endif. Perubahan nama, NRP, atau email akan langsung tersinkronkan.
            </div>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Nama Lengkap') }} <span class="text-rose-500">*</span>
            </label>
            <input id="name"
                   name="name"
                   type="text"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm text-slate-800 transition placeholder:text-slate-400 font-medium shadow-2xs"
                   value="{{ old('name', $user->name) }}"
                   required
                   autofocus
                   autocomplete="name"
                   placeholder="Masukkan nama lengkap Anda..." />
            @error('name')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- NRP Pegawai / Username Login -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    {{ __('NRP Pegawai / Username Login') }}
                </label>
                <span class="text-[11px] text-slate-400 font-normal">Identitas Login & Spreadsheet</span>
            </div>
            <input id="username"
                   name="username"
                   type="text"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm text-slate-800 transition placeholder:text-slate-400 font-mono font-medium shadow-2xs"
                   value="{{ old('username', $user->username) }}"
                   placeholder="Contoh: 12345 atau username login..." />
            <p class="mt-1 text-[11px] text-slate-500">
                NRP / Username digunakan untuk masuk sistem dan identifikasi di sheet <strong>Pengguna</strong> dan <strong>tim_penilai</strong>.
            </p>
            @error('username')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Alamat Email -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                {{ __('Alamat Email') }} <span class="text-rose-500">*</span>
            </label>
            <input id="email"
                   name="email"
                   type="email"
                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 text-sm text-slate-800 transition placeholder:text-slate-400 font-medium shadow-2xs"
                   value="{{ old('email', $user->email) }}"
                   required
                   autocomplete="username"
                   placeholder="nama@musashi.co.id" />
            @error('email')
                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2.5 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                    <p>
                        {{ __('Alamat email Anda belum diverifikasi.') }}
                        <button form="send-verification" class="underline font-bold text-amber-900 hover:text-amber-700 focus:outline-none ml-1">
                            {{ __('Klik di sini untuk mengirim ulang link verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1.5 font-bold text-emerald-700">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Detail Peran & Kelompok (Readonly Info) -->
        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-slate-500 font-medium">Peran / Hak Akses:</span>
                <span class="px-2.5 py-0.5 font-extrabold uppercase rounded-full text-[10px] tracking-wide
                    @if($user->hasRole('superadmin')) bg-purple-100 text-purple-700 border border-purple-200
                    @elseif($user->hasRole('admin')) bg-blue-100 text-blue-700 border border-blue-200
                    @else bg-emerald-100 text-emerald-700 border border-emerald-200 @endif">
                    {{ $user->getRoleNames()->first() ?? 'User' }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-500 font-medium">Penugasan Kelompok:</span>
                <span class="font-bold text-slate-800">
                    @if($user->group)
                        Kelompok {{ $user->group->number }} ({{ $user->group->name }})
                    @elseif($user->hasRole('ketua'))
                        <span class="text-amber-600 italic">Belum ditautkan ke kelompok</span>
                    @else
                        <span class="text-slate-600">Semua Area & Kelompok</span>
                    @endif
                </span>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="pt-3 flex items-center justify-between gap-4">
            <button type="submit"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/25 transition cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ __('Simpan Perubahan Profil') }}
            </button>

            @if (session('status') === 'profile-updated')
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition
                     x-init="setTimeout(() => show = false, 4000)"
                     class="flex items-center text-xs font-bold text-emerald-600">
                    <svg class="w-4 h-4 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Profil tersimpan & tersinkron!') }}
                </div>
            @endif
        </div>
    </form>
</section>
