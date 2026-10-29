<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
            <div class="flex items-center space-x-3.5 sm:space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="font-extrabold text-xl sm:text-2xl text-slate-900 leading-tight">
                            {{ __('Profil Pengguna') }}
                        </h2>
                        <span class="px-2.5 py-0.5 text-xs font-black uppercase rounded-full tracking-wide
                            @if(Auth::user()->hasRole('superadmin')) bg-purple-100 text-purple-700 border border-purple-200
                            @elseif(Auth::user()->hasRole('admin')) bg-blue-100 text-blue-700 border border-blue-200
                            @else bg-emerald-100 text-emerald-700 border border-emerald-200 @endif">
                            {{ Auth::user()->getRoleNames()->first() }}
                        </span>
                        @if(Auth::user()->group)
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                Kelompok {{ Auth::user()->group->number }} ({{ Auth::user()->group->name }})
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Kelola data profil dan kata sandi akun Anda.
                    </p>
                </div>
            </div>

            <!-- Status Sinkronisasi -->
            <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold self-start xl:self-auto shadow-2xs">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span>Sinkronisasi Aktif</span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6">

            <!-- Flash Alert: Sinkronisasi Profil Berhasil -->
            @if (session('sync_success'))
                <div class="p-4 sm:p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-900 flex items-center gap-3.5 shadow-xs transition animate-fade-in">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-extrabold text-sm text-emerald-900">{{ session('sync_success') }}</h4>
                    </div>
                </div>
            @endif

            <!-- Flash Alert: Sinkronisasi Password Berhasil -->
            @if (session('password_sync_success'))
                <div class="p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-950 flex items-center gap-3.5 shadow-xs transition animate-fade-in">
                    <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-extrabold text-sm text-amber-950">{{ session('password_sync_success') }}</h4>
                    </div>
                </div>
            @endif

            <!-- Banner Ringkasan Akun -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-blue-50/70 via-indigo-50/50 to-white rounded-2xl border border-blue-200/80 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">
                            Pengaturan Akun & Profil
                        </h3>
                        <p class="text-xs text-slate-600 mt-0.5">
                            Perubahan profil dan kata sandi Anda akan otomatis disimpan dan diperbarui di sistem.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                    <span class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs">
                        NRP / Akun: <strong>{{ Auth::user()->username ?: '-' }}</strong>
                    </span>
                    <span class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-semibold shadow-2xs">
                        Email: <strong>{{ Auth::user()->email }}</strong>
                    </span>
                </div>
            </div>

            <!-- GRID 2 KOLOM UNTUK LAYAR SEDANG / LEBAR -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-start">
                
                <!-- KOLOM 1: INFORMASI PROFIL -->
                <div class="p-5 sm:p-7 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- KOLOM 2: PERBARUI KATA SANDI -->
                <div class="p-5 sm:p-7 bg-white rounded-2xl border border-slate-200/80 shadow-2xs">
                    @include('profile.partials.update-password-form')
                </div>

            </div>

            @if(!Auth::user()->hasRole('superadmin'))
                <!-- SEKSI HAPUS AKUN (HANYA DITAMPILKAN JIKA BUKAN SUPERADMIN) -->
                <div class="p-5 sm:p-7 bg-white rounded-2xl border border-rose-200 shadow-2xs">
                    @include('profile.partials.delete-user-form')
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
