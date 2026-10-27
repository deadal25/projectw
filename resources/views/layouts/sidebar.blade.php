<!-- BACKDROP UNTUK MOBILE DRAWER -->
<div x-show="sidebarOpen" 
     x-cloak
     @click="sidebarOpen = false"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden">
</div>

<!-- SIDEBAR CONTAINER -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 dark:bg-slate-950 border-r border-slate-800 text-slate-300 flex flex-col justify-between transition-transform duration-300 ease-in-out">
    
    <!-- Bagian Atas: Brand & User Snippet -->
    <div class="flex flex-col flex-1 overflow-y-auto">
        
        <!-- Header Brand WOLA MUSASHI -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 shrink-0 bg-slate-950/60">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white p-1.5 flex items-center justify-center shadow-md shadow-blue-900/40 shrink-0">
                    <img src="{{ asset('logo.png') }}" alt="Logo WOLA MUSASHI" class="max-h-full max-w-full object-contain">
                </div>
                <div>
                    <span class="font-black text-sm tracking-tight text-white block leading-tight">WOLA MUSASHI</span>
                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-widest block">PORTAL WOLA</span>
                </div>
            </a>
            
            <!-- Tombol Tutup Sidebar di Mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Profil Pengguna Ringkas -->
        <div class="p-4 mx-3 my-4 rounded-2xl bg-slate-800/60 border border-slate-700/60">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/40 text-blue-400 font-extrabold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs font-bold text-white truncate" title="{{ Auth::user()->name }}">
                        {{ Auth::user()->name }}
                    </h4>
                    <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
            
            <div class="mt-2.5 pt-2.5 border-t border-slate-700/60 flex items-center justify-between">
                <span class="text-[10px] text-slate-400 font-medium">Hak Akses:</span>
                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full tracking-wide
                    @if(Auth::user()->hasRole('superadmin')) bg-purple-500/20 text-purple-300 border border-purple-500/30
                    @elseif(Auth::user()->hasRole('admin')) bg-blue-500/20 text-blue-300 border border-blue-500/30
                    @else bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 @endif">
                    {{ Auth::user()->getRoleNames()->first() }}
                </span>
            </div>

            @if(Auth::user()->group)
                <div class="mt-1 text-[10px] text-slate-400 truncate">
                    Area: <strong class="text-slate-200">{{ Auth::user()->group->name }}</strong>
                </div>
            @endif
        </div>

        <!-- DAFTAR MENU NAVIGASI SIDEBAR -->
        <nav class="px-3 space-y-6 text-xs font-semibold">
            
            <!-- SEKSI 1: DASHBOARD -->
            <div>
                <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    DASHBOARD WOLA
                </span>
                <div class="mt-2 space-y-1">
                    @hasanyrole('superadmin|admin')
                        <a href="{{ route('wola.admin.dashboard') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                           {{ request()->routeIs('wola.admin.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard Admin</span>
                        </a>

                        <a href="{{ route('wola.ketua.dashboard') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                           {{ request()->routeIs('wola.ketua.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Dashboard Ketua (Area)</span>
                        </a>
                    @else
                        <a href="{{ route('wola.ketua.dashboard') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                           {{ request()->routeIs('wola.ketua.dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard Kelompok</span>
                        </a>
                    @endhasanyrole
                </div>
            </div>

            <!-- SEKSI 2: DATA MASTER & KELOMPOK -->
            <div>
                <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    MASTER & KELOMPOK
                </span>
                <div class="mt-2 space-y-1">
                    @hasanyrole('superadmin|admin')
                        <a href="{{ route('wola.employees.index') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                           {{ request()->routeIs('wola.employees.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Master Karyawan</span>
                        </a>

                        <a href="{{ route('wola.groups.index') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                           {{ request()->routeIs('wola.groups.*') && !request()->routeIs('wola.groups.assessments.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Kelola Kelompok</span>
                        </a>

                        <a href="{{ route('wola.users.index') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                           {{ request()->routeIs('wola.users.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Manajemen Akun</span>
                        </a>

                        @hasrole('superadmin')
                            <a href="{{ route('wola.google-sheets.index') }}"
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                               {{ request()->routeIs('wola.google-sheets.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Google Sheets Sync</span>
                            </a>
                        @endhasrole
                    @else
                        @if(Auth::user()->group_id)
                            <a href="{{ route('wola.groups.show', Auth::user()->group_id) }}"
                               class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                               {{ request()->routeIs('wola.groups.show') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span>Kelola Komponen Saya</span>
                            </a>
                        @endif
                    @endhasanyrole
                </div>
            </div>

            <!-- SEKSI 3: PENILAIAN WOLA & LAPORAN -->
            <div>
                <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    PENILAIAN BEBAN KERJA
                </span>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('wola.assessments.root') }}"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                       {{ request()->routeIs('wola.assessments.*') || request()->routeIs('wola.groups.assessments.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span>Lembar Penilaian WOLA</span>
                    </a>

                    <a href="{{ route('wola.reports.index') }}"
                       class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                       {{ request()->routeIs('wola.reports.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Pusat Laporan & Rekap</span>
                    </a>
                </div>
            </div>

            <!-- SEKSI 4: SISTEM & AUDIT -->
            @hasanyrole('superadmin|admin')
                <div>
                    <span class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        AUDIT & SISTEM
                    </span>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('wola.activity-logs.index') }}"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition font-bold
                           {{ request()->routeIs('wola.activity-logs.*') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Log Aktivitas Sistem</span>
                        </a>
                    </div>
                </div>
            @endhasanyrole

        </nav>
    </div>

    <!-- Bagian Bawah: Profil & Logout -->
    <div class="p-3 border-t border-slate-800 shrink-0 bg-slate-950/80">
        <div class="flex items-center space-x-2">
            <a href="{{ route('profile.edit') }}"
               class="flex-1 flex items-center justify-center space-x-1.5 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Profil</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center space-x-1.5 py-2 px-3 rounded-xl bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white text-xs font-bold border border-rose-500/30 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>

</aside>
