<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 sm:gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Manajemen Pengguna & Tim Penilai') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    @if(auth()->user()->hasRole('superadmin'))
                        Kelola akun login (Superadmin, Admin, Ketua) serta susunan Tim Penilai per Kelompok WOLA MUSASHI.
                    @else
                        Kelola akun Ketua Kelompok serta susunan Tim Penilai untuk pengisian penilaian WOLA.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Tombol Sinkronkan Spreadsheet -->
                <form method="POST" action="{{ route('wola.users.sync-sheet') }}" class="inline">
                    @csrf
                    <input type="hidden" name="tab" :value="activeTab">
                    <button type="submit"
                            onclick="this.disabled=true; this.innerHTML='<span class=\'animate-spin inline-block me-1.5\'>↻</span> Menyinkronkan...'; this.form.submit();"
                            class="inline-flex items-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-md shadow-amber-500/30 transition cursor-pointer"
                            title="Tarik data terbaru dari Google Spreadsheet (Kelompok, Pengguna, dan tim_penilai)">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sinkronkan Spreadsheet
                    </button>
                </form>

                <!-- Tombol Tambah Penilai Cepat (Tanpa Dobel +) -->
                <button type="button" @click="openAddAssessor()"
                        class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition cursor-pointer">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Tambah Penilai
                </button>

                <!-- Tombol Tambah Akun Baru (Tanpa Dobel +) -->
                <a href="{{ route('wola.users.create') }}"
                   class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/30 transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Akun Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
             activeTab: '{{ request('tab', 'users') }}',
             assessorSearch: '',
             assessorGroupFilter: '',
             assessorRoleFilter: '',
             expandedGroups: [{{ request('group_id') ? (int)request('group_id') : '' }}],
             toggleGroup(id) {
                 if (this.expandedGroups.includes(id)) {
                     this.expandedGroups = this.expandedGroups.filter(g => g !== id);
                 } else {
                     this.expandedGroups.push(id);
                 }
             },
             addAssessorModal: false,
             editAssessorModal: false,
             selectedGroupId: '{{ request('group_id') ?? ($groups->first()->id ?? '') }}',
             openAddAssessor(groupId = null) {
                 if (groupId) {
                     this.selectedGroupId = groupId;
                 }
                 this.addAssessorModal = true;
             },
             editData: {
                 id: '',
                 group_id: '',
                 group_name: '',
                 fullname: '',
                 nrp: '',
                 dept: '',
                 plant: '',
                 is_leader: false
             },
             openEditAssessor(assessorId, groupId, groupName, fullname, nrp, dept, plant, isLeader) {
                 this.editData = {
                     id: assessorId,
                     group_id: groupId,
                     group_name: groupName,
                     fullname: fullname,
                     nrp: nrp,
                     dept: dept,
                     plant: plant,
                     is_leader: isLeader
                 };
                 this.editAssessorModal = true;
             }
         }">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- 4 Kartu Ringkasan KPI Statistik -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                @if(auth()->user()->hasRole('superadmin'))
                    <!-- Superadmin -->
                    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 01.894.553l1.618 3.279 3.619.526a1 1 0 01.554 1.705l-2.618 2.552.618 3.604a1 1 0 01-1.451 1.054L10 13.567l-3.236 1.702a1 1 0 01-1.451-1.054l.618-3.604-2.618-2.552a1 1 0 01.554-1.705l3.619-.526L9.106 2.553A1 1 0 0110 2z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Superadmin</p>
                            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ $totalSuperadmin }} Akun</p>
                        </div>
                    </div>

                    <!-- Admin WOLA -->
                    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Admin WOLA</p>
                            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ $totalAdmin }} Akun</p>
                        </div>
                    </div>
                @else
                    <!-- Total Kelompok jika role Admin biasa -->
                    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Total Area</p>
                            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ $groups->count() }} Kelompok</p>
                        </div>
                    </div>

                    <!-- Status Akses -->
                    <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Hak Akses Anda</p>
                            <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">Admin WOLA</p>
                        </div>
                    </div>
                @endif

                <!-- Ketua Kelompok -->
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Ketua Kelompok</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ $totalKetua }} Akun</p>
                    </div>
                </div>

                <!-- Total Penilai (Klik untuk buka tab Penilai) -->
                <div @click="activeTab = 'assessors'"
                     class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4 cursor-pointer hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-md transition group"
                     title="Klik untuk membuka tab Daftar Seluruh Tim Penilai">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Tim Penilai</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate group-hover:text-amber-600 transition">{{ $totalAssessors }} Penilai</p>
                    </div>
                </div>
            </div>

            <!-- TAB NAVIGASI UTAMA -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 dark:border-slate-700 pb-3">
                <button type="button"
                        @click="activeTab = 'users'"
                        :class="activeTab === 'users' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Daftar Akun Pengguna ({{ $users->total() }})
                </button>

                <button type="button"
                        @click="activeTab = 'assessors'"
                        :class="activeTab === 'assessors' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Daftar Seluruh Tim Penilai ({{ $totalAssessors }})
                </button>

                <button type="button"
                        @click="activeTab = 'groups'"
                        :class="activeTab === 'groups' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Struktur Kelompok & Tim Penilai ({{ $groups->count() }} Area)
                </button>
            </div>

            <!-- ========================================================================= -->
            <!-- TAB 1: MANAJEMEN AKUN PENGGUNA -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'users'" class="space-y-6">

                <!-- Panel Filter & Search Akun -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <form method="GET" action="{{ route('wola.users.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <input type="hidden" name="tab" value="users">
                        <div class="w-full sm:w-80">
                            <div class="relative">
                                <input type="text" name="search" value="{{ $search }}"
                                       placeholder="Cari nama atau email akun..."
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                                @if($search)
                                    <a href="{{ route('wola.users.index', ['tab' => 'users', 'role' => $roleFilter]) }}"
                                       class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600">✕</a>
                                @endif
                            </div>
                        </div>

                        @if(auth()->user()->hasRole('superadmin'))
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('wola.users.index', ['tab' => 'users', 'search' => $search]) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ !$roleFilter ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                    Semua
                                </a>
                                <a href="{{ route('wola.users.index', ['tab' => 'users', 'role' => 'superadmin', 'search' => $search]) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $roleFilter === 'superadmin' ? 'bg-purple-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                    Superadmin
                                </a>
                                <a href="{{ route('wola.users.index', ['tab' => 'users', 'role' => 'admin', 'search' => $search]) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $roleFilter === 'admin' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                    Admin
                                </a>
                                <a href="{{ route('wola.users.index', ['tab' => 'users', 'role' => 'ketua', 'search' => $search]) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $roleFilter === 'ketua' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                                    Ketua
                                </a>
                            </div>
                        @endif
                    </form>
                </div>

                <!-- Petunjuk Interaktif -->
                <div class="p-3 bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl flex items-center justify-between text-xs text-blue-800 dark:text-blue-300">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span><strong>Petunjuk:</strong> Klik pada nama <strong>Kelompok Kerja</strong> di tabel untuk membuka dan mengatur daftar penilai di bawah akun tersebut.</span>
                    </div>
                </div>

                <!-- Tabel Akun Pengguna -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Nama & Email</th>
                                    <th class="py-3.5 px-4">Role Akses</th>
                                    <th class="py-3.5 px-4">Kelompok Kerja (Area WLA)</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($users as $user)
                                    @php
                                        $role = $user->getRoleNames()->first();
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                <span>{{ $user->name }}</span>
                                                @if($user->employee)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 font-mono font-bold text-[10px] border border-blue-200 dark:border-blue-800" title="NRP Master Karyawan: {{ $user->employee->nrp }}">
                                                        NRP: {{ $user->employee->nrp }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $user->email }}</div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                                                @if($role === 'superadmin') bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300
                                                @elseif($role === 'admin') bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300
                                                @else bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 @endif">
                                                {{ $role }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($user->group)
                                                <!-- Tombol Interaktif Kelompok: Klik untuk buka daftar penilai di bawahnya -->
                                                <button type="button"
                                                        @click="toggleGroup({{ $user->group->id }})"
                                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-semibold transition text-left cursor-pointer
                                                               {{ $user->group->assessors->count() > 0 ? 'bg-blue-50 hover:bg-blue-100 border-blue-200 text-blue-700 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300' : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300' }}"
                                                        title="Klik untuk membuka & kelola daftar penilai kelompok ini">
                                                    <span>Kelompok {{ $user->group->number }}: {{ $user->group->name }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0
                                                                 {{ $user->group->assessors->count() > 0 ? 'bg-blue-200 text-blue-900 dark:bg-blue-800 dark:text-blue-100' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                                        {{ $user->group->assessors->count() }} Penilai
                                                    </span>
                                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-600 transition-transform duration-200 shrink-0"
                                                         :class="expandedGroups.includes({{ $user->group->id }}) ? 'rotate-180 text-blue-600' : ''"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>

                                                @if($user->group->assessors->isNotEmpty())
                                                    <div class="flex flex-wrap items-center gap-1 mt-1.5 max-w-xs">
                                                        @foreach($user->group->assessors->take(3) as $ass)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] {{ $ass->is_leader ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 font-semibold' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}" title="{{ $ass->employee?->fullname }}">
                                                                {{ $ass->is_leader ? '★ ' : '' }}{{ \Illuminate\Support\Str::limit($ass->employee?->fullname ?? 'Penilai', 14) }}
                                                            </span>
                                                        @endforeach
                                                        @if($user->group->assessors->count() > 3)
                                                            <span class="text-[10px] text-slate-400 font-bold" title="Klik tombol kelompok untuk melihat semua penilai">+{{ $user->group->assessors->count() - 3 }} lainnya</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-xs text-slate-400 italic">Semua Kelompok</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($user->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <!-- Edit -->
                                                <a href="{{ route('wola.users.edit', $user) }}"
                                                   class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                   title="Edit Akun">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </a>

                                                <!-- Hapus (Proteksi diri sendiri) -->
                                                @if($user->id !== auth()->id())
                                                    <form method="POST" action="{{ route('wola.users.destroy', $user) }}"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?');"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                                title="Hapus Akun">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- BARIS AKORDION DAFTAR PENILAI DI BAWAH KELOMPOK -->
                                    @if($user->group)
                                        <tr x-show="expandedGroups.includes({{ $user->group->id }})"
                                            x-cloak
                                            class="bg-blue-50/40 dark:bg-slate-900/60 border-b-2 border-blue-200 dark:border-blue-900">
                                            <td colspan="5" class="p-4 sm:p-5">
                                                <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 sm:p-5 border border-blue-200 dark:border-blue-900 shadow-sm space-y-4">
                                                    <!-- Header Bagian Penilai -->
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                                                        <div class="flex items-center space-x-3">
                                                            <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 flex items-center justify-center shrink-0">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                                </svg>
                                                            </span>
                                                            <div>
                                                                <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">
                                                                    Tim Penilai: Kelompok {{ $user->group->number }} — {{ $user->group->name }}
                                                                </h4>
                                                                <p class="text-xs text-slate-500">
                                                                    {{ $user->group->assessors->count() }} orang penilai ditugaskan pada kelompok ini.
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <!-- Tombol Tambah Penilai ke Kelompok (Tanpa Dobel +) -->
                                                            <button type="button"
                                                                    @click="openAddAssessor({{ $user->group->id }})"
                                                                    class="inline-flex items-center px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                                                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                                </svg>
                                                                Tambah Penilai ke Kelompok Ini
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Tabel Penilai di Kelompok Ini -->
                                                    @if($user->group->assessors->isNotEmpty())
                                                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                                                            <table class="w-full text-left text-xs border-collapse">
                                                                <thead>
                                                                    <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                                                        <th class="py-2.5 px-3">Status & NRP</th>
                                                                        <th class="py-2.5 px-3">Nama Lengkap Penilai</th>
                                                                        <th class="py-2.5 px-3">Dept & Plant</th>
                                                                        <th class="py-2.5 px-3">Progres Ceklis</th>
                                                                        <th class="py-2.5 px-3 text-center w-36">Aksi Penilai</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                                                    @foreach($user->group->assessors as $assessor)
                                                                        @php
                                                                            $progress = $assessor->getProgressData();
                                                                        @endphp
                                                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40">
                                                                            <td class="py-2.5 px-3">
                                                                                <div class="flex items-center space-x-2">
                                                                                    @if($assessor->is_leader)
                                                                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                                                                            ★ Ketua
                                                                                        </span>
                                                                                    @else
                                                                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                                                                            Penilai
                                                                                        </span>
                                                                                    @endif
                                                                                    <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $assessor->employee?->nrp ?? '-' }}</span>
                                                                                </div>
                                                                            </td>
                                                                            <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white">
                                                                                {{ $assessor->employee?->fullname ?? 'Karyawan Tidak Ditemukan' }}
                                                                            </td>
                                                                            <td class="py-2.5 px-3 text-slate-500">
                                                                                {{ $assessor->employee?->dept ?? '-' }} • {{ $assessor->employee?->plant ?? '-' }}
                                                                            </td>
                                                                            <td class="py-2.5 px-3">
                                                                                <div class="flex items-center space-x-2">
                                                                                    <div class="w-24 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                                                                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $progress['percentage'] }}%"></div>
                                                                                    </div>
                                                                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $progress['percentage'] }}%</span>
                                                                                    <span class="text-[11px] text-slate-400">({{ $progress['checked'] }}/{{ $progress['target'] }})</span>
                                                                                </div>
                                                                            </td>
                                                                            <td class="py-2.5 px-3 text-center">
                                                                                <div class="flex items-center justify-center space-x-1.5">
                                                                                    <!-- Edit Penilai (Ketik Manual) -->
                                                                                    <button type="button"
                                                                                            @click="openEditAssessor({{ $assessor->id }}, {{ $user->group->id }}, '{{ addslashes($user->group->name) }}', '{{ addslashes($assessor->employee?->fullname ?? '') }}', '{{ addslashes($assessor->employee?->nrp ?? '') }}', '{{ addslashes($assessor->employee?->dept ?? '') }}', '{{ addslashes($assessor->employee?->plant ?? '') }}', {{ $assessor->is_leader ? 'true' : 'false' }})"
                                                                                            class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                                                            title="Edit Data Penilai">
                                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                                                        </svg>
                                                                                    </button>

                                                                                    @if(!$assessor->is_leader)
                                                                                        <!-- Hapus Penilai -->
                                                                                        <form method="POST" action="{{ route('wola.groups.assessors.remove', [$user->group, $assessor]) }}"
                                                                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus penilai {{ $assessor->employee?->fullname }} dari kelompok ini?');"
                                                                                              class="inline">
                                                                                            @csrf
                                                                                            @method('DELETE')
                                                                                            <button type="submit"
                                                                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                                                                    title="Hapus Penilai">
                                                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                                                </svg>
                                                                                            </button>
                                                                                        </form>
                                                                                    @else
                                                                                        <span class="px-2 py-0.5 text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-md italic" title="Ketua ditetapkan dari akun Ketua di Manajemen Akun">
                                                                                            Ketua (Terkunci)
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="p-6 text-center bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Belum ada penilai yang terdaftar pada kelompok ini.</p>
                                                            <button type="button"
                                                                    @click="openAddAssessor({{ $user->group->id }})"
                                                                    class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                                                Tambahkan Penilai Sekarang
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">
                                            Tidak ada akun pengguna ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- TAB 2: DAFTAR SELURUH TIM PENILAI (DARI DATABASE & SPREADSHEET TIM_PENILAI) -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'assessors'" class="space-y-6">

                <!-- Panel Filter & Search Penilai -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base flex items-center gap-2">
                                <span>Daftar Seluruh Tim Penilai</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                    {{ $allAssessors->count() }} Penilai
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Terhubung langsung dengan sheet <strong>tim_penilai</strong> di Google Spreadsheet mencakup seluruh Ketua dan Anggota Penilai.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <button type="button" @click="openAddAssessor()"
                                    class="inline-flex items-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Penilai Baru
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-100 dark:border-slate-700/80">
                        <!-- Input Cari Penilai -->
                        <div class="relative">
                            <input type="text"
                                   x-model="assessorSearch"
                                   placeholder="Cari nama, NRP, dept, plant, atau kelompok..."
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                            <button type="button" x-show="assessorSearch" @click="assessorSearch = ''"
                                    class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600">✕</button>
                        </div>

                        <!-- Dropdown Filter Kelompok -->
                        <div>
                            <select x-model="assessorGroupFilter"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                                <option value="">-- Semua Kelompok (1 - {{ $groups->count() }}) --</option>
                                @foreach($groups as $grp)
                                    <option value="{{ $grp->id }}">
                                        Kelompok {{ $grp->number }}: {{ $grp->name }} ({{ $grp->assessors->count() }} penilai)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dropdown Filter Status / Role -->
                        <div>
                            <select x-model="assessorRoleFilter"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                                <option value="">-- Semua Peran (Ketua & Anggota) --</option>
                                <option value="1">Hanya Ketua Kelompok (★ Ketua)</option>
                                <option value="0">Hanya Anggota Penilai</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tabel Seluruh Penilai -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4">Status & NRP</th>
                                    <th class="py-3 px-4">Nama Lengkap Penilai</th>
                                    <th class="py-3 px-4">Kelompok Kerja (Area WLA)</th>
                                    <th class="py-3 px-4">Dept & Plant</th>
                                    <th class="py-3 px-4">Progres Penilaian</th>
                                    <th class="py-3 px-4 text-center w-28">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @forelse($allAssessors as $index => $assessor)
                                    @php
                                        $prog = $assessor->getProgressData();
                                        $empName = $assessor->employee?->fullname ?? 'Karyawan Tidak Ditemukan';
                                        $empNrp = $assessor->employee?->nrp ?? '-';
                                        $empDept = $assessor->employee?->dept ?? '-';
                                        $empPlant = $assessor->employee?->plant ?? '-';
                                        $grpNum = $assessor->group?->number ?? 0;
                                        $grpName = $assessor->group?->name ?? 'Tanpa Kelompok';
                                        $grpId = $assessor->group_id ?? 0;
                                        $isLeaderVal = $assessor->is_leader ? '1' : '0';
                                        $searchString = strtolower("{$empName} {$empNrp} {$empDept} {$empPlant} kelompok {$grpNum} {$grpName}");
                                    @endphp
                                    <tr x-show="(assessorGroupFilter === '' || assessorGroupFilter == '{{ $grpId }}') &&
                                                (assessorRoleFilter === '' || assessorRoleFilter == '{{ $isLeaderVal }}') &&
                                                (assessorSearch === '' || '{{ addslashes($searchString) }}'.includes(assessorSearch.toLowerCase().trim()))"
                                        class="hover:bg-slate-50/60 dark:hover:bg-slate-900/40 transition">
                                        <td class="py-3 px-4 text-center font-mono text-slate-400">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-2">
                                                @if($assessor->is_leader)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                                        ★ Ketua
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                                        Penilai
                                                    </span>
                                                @endif
                                                <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $empNrp }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm">
                                                {{ $empName }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($assessor->group)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                                    Kelompok {{ $grpNum }}: {{ $grpName }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400 italic">Belum ditentukan</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-500">
                                            <div class="font-medium text-slate-700 dark:text-slate-300">{{ $empDept }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $empPlant }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-20 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $prog['percentage'] }}%"></div>
                                                </div>
                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $prog['percentage'] }}%</span>
                                                <span class="text-[10px] text-slate-400">({{ $prog['checked'] }}/{{ $prog['target'] }})</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <!-- Edit Penilai -->
                                                <button type="button"
                                                        @click="openEditAssessor({{ $assessor->id }}, {{ $assessor->group_id ?? 0 }}, '{{ addslashes($grpName) }}', '{{ addslashes($empName) }}', '{{ addslashes($empNrp) }}', '{{ addslashes($empDept) }}', '{{ addslashes($empPlant) }}', {{ $assessor->is_leader ? 'true' : 'false' }})"
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                        title="Edit Data Penilai">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>

                                                @if(!$assessor->is_leader && $assessor->group)
                                                    <!-- Hapus Penilai -->
                                                    <form method="POST" action="{{ route('wola.groups.assessors.remove', [$assessor->group, $assessor]) }}"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus penilai {{ addslashes($empName) }} dari kelompok ini?');"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                                title="Hapus Penilai">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="px-2 py-0.5 text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-md italic" title="Ketua dikelola melalui akun Ketua">
                                                        ★ Ketua
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-10 text-center text-slate-500 dark:text-slate-400">
                                            Belum ada data penilai terdaftar. Silakan klik "Sinkronkan Spreadsheet" atau "Tambah Penilai".
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- TAB 3: STRUKTUR KELOMPOK & TIM PENILAI LENGKAP -->
            <!-- ========================================================================= -->
            <div x-show="activeTab === 'groups'" class="space-y-4">
                <div class="p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white text-base">Seluruh Area Kelompok WOLA</h3>
                        <p class="text-xs text-slate-500">Klik kelompok manapun untuk melihat, menambah, atau mengedit daftar penilai di dalamnya.</p>
                    </div>
                    <button type="button" @click="openAddAssessor()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-sm transition self-start sm:self-center">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Penilai
                    </button>
                </div>

                <!-- Grid Kartu Accordion 12 Kelompok -->
                <div class="space-y-3">
                    @foreach($groups as $grp)
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition">
                            <!-- Card Header -->
                            <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer select-none hover:bg-slate-50/60 dark:hover:bg-slate-900/40"
                                 @click="toggleGroup({{ $grp->id }})">
                                <div class="flex items-center space-x-3.5 min-w-0">
                                    <span class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-extrabold flex items-center justify-center shrink-0 border border-blue-200/60 dark:border-blue-800/60">
                                        {{ $grp->number }}
                                    </span>
                                    <div class="min-w-0">
                                        <h4 class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white truncate">
                                            {{ $grp->name }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-2 mt-0.5 text-xs text-slate-500">
                                            @if($grp->leader)
                                                <span>Ketua Akun: <strong class="text-slate-700 dark:text-slate-300">{{ $grp->leader->name }}</strong></span>
                                            @else
                                                <span class="italic text-slate-400">Belum ada akun Ketua</span>
                                            @endif
                                            <span>•</span>
                                            <span>Target Ceklis: <strong class="text-slate-700 dark:text-slate-300">{{ $grp->custom_target ?? 'Otomatis' }}</strong></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3 shrink-0 self-end sm:self-center" @click.stop>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                                 {{ $grp->assessors->count() > 0 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                        {{ $grp->assessors->count() }} Penilai
                                    </span>

                                    <button type="button"
                                            @click="openAddAssessor({{ $grp->id }})"
                                            class="p-2 rounded-xl text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/60 border border-blue-200 dark:border-blue-800 transition"
                                            title="Tambah Penilai ke Kelompok ini">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>

                                    <button type="button"
                                            @click="toggleGroup({{ $grp->id }})"
                                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                                        <svg class="w-5 h-5 transition-transform duration-200"
                                             :class="expandedGroups.includes({{ $grp->id }}) ? 'rotate-180 text-blue-600' : ''"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Card Body: Daftar Penilai -->
                            <div x-show="expandedGroups.includes({{ $grp->id }})"
                                 x-cloak
                                 class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/40 dark:bg-slate-900/40">
                                @if($grp->assessors->isNotEmpty())
                                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                        <table class="w-full text-left text-xs border-collapse">
                                            <thead>
                                                <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                                    <th class="py-2.5 px-3">Status & NRP</th>
                                                    <th class="py-2.5 px-3">Nama Lengkap Penilai</th>
                                                    <th class="py-2.5 px-3">Dept & Plant</th>
                                                    <th class="py-2.5 px-3">Progres Penilaian</th>
                                                    <th class="py-2.5 px-3 text-center w-36">Aksi Penilai</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                                @foreach($grp->assessors as $assessor)
                                                    @php
                                                        $prog = $assessor->getProgressData();
                                                    @endphp
                                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40">
                                                        <td class="py-2.5 px-3">
                                                            <div class="flex items-center space-x-2">
                                                                @if($assessor->is_leader)
                                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                                                                        ★ Ketua
                                                                    </span>
                                                                @else
                                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                                                        Penilai
                                                                    </span>
                                                                @endif
                                                                <span class="font-mono font-bold text-slate-700 dark:text-slate-300">{{ $assessor->employee?->nrp ?? '-' }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white">
                                                            {{ $assessor->employee?->fullname ?? 'Karyawan Tidak Ditemukan' }}
                                                        </td>
                                                        <td class="py-2.5 px-3 text-slate-500">
                                                            {{ $assessor->employee?->dept ?? '-' }} • {{ $assessor->employee?->plant ?? '-' }}
                                                        </td>
                                                        <td class="py-2.5 px-3">
                                                            <div class="flex items-center space-x-2">
                                                                <div class="w-24 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $prog['percentage'] }}%"></div>
                                                                </div>
                                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $prog['percentage'] }}%</span>
                                                                <span class="text-[11px] text-slate-400">({{ $prog['checked'] }}/{{ $prog['target'] }})</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-2.5 px-3 text-center">
                                                            <div class="flex items-center justify-center space-x-1.5">
                                                                <button type="button"
                                                                        @click="openEditAssessor({{ $assessor->id }}, {{ $grp->id }}, '{{ addslashes($grp->name) }}', '{{ addslashes($assessor->employee?->fullname ?? '') }}', '{{ addslashes($assessor->employee?->nrp ?? '') }}', '{{ addslashes($assessor->employee?->dept ?? '') }}', '{{ addslashes($assessor->employee?->plant ?? '') }}', {{ $assessor->is_leader ? 'true' : 'false' }})"
                                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                                        title="Edit Data Penilai">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                                    </svg>
                                                                </button>

                                                                @if(!$assessor->is_leader)
                                                                    <form method="POST" action="{{ route('wola.groups.assessors.remove', [$grp, $assessor]) }}"
                                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus penilai {{ $assessor->employee?->fullname }} dari kelompok ini?');"
                                                                          class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                                                title="Hapus Penilai">
                                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                            </svg>
                                                                        </button>
                                                                    </form>
                                                                @else
                                                                    <span class="px-2 py-0.5 text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-md italic" title="Ketua ditetapkan dari akun Ketua di Manajemen Akun">
                                                                        Ketua (Terkunci)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-6 text-center bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                                        <p class="text-xs text-slate-500 mb-2">Belum ada penilai di kelompok ini.</p>
                                        <button type="button"
                                                @click="openAddAssessor({{ $grp->id }})"
                                                class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                            Tambahkan Penilai Sekarang
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- MODAL TAMBAH PENILAI KELOMPOK (INPUT KETIK MANUAL, BUKAN DROPDOWN) -->
        <!-- ========================================================================= -->
        <div x-show="addAssessorModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog" aria-modal="true">
            <div class="min-h-screen px-4 text-center flex items-center justify-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                     @click="addAssessorModal = false"></div>

                <!-- Modal Content -->
                <div class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 relative z-10">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                    Tambah Penilai Kelompok
                                </h3>
                                <p class="text-xs text-slate-500">Ketik data penilai langsung, dapat diisi siapa saja.</p>
                            </div>
                        </div>
                        <button type="button" @click="addAssessorModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            ✕
                        </button>
                    </div>

                    <form method="POST" :action="'/wola/groups/' + selectedGroupId + '/assessors'" class="mt-5 space-y-4">
                        @csrf

                        <!-- Pilihan Kelompok yang Ditugaskan -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Area Kelompok <span class="text-rose-500">*</span>
                            </label>
                            <select x-model="selectedGroupId" required
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}">
                                        Kelompok {{ $g->number }}: {{ $g->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 1. Nama Lengkap Penilai (Ketik Manual) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Lengkap Penilai <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="fullname" required
                                   placeholder="Ketik nama lengkap penilai (contoh: ISNANTO / BUDI SANTOSO)..."
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <p class="text-[11px] text-slate-400 mt-1">Bebas diketik langsung, tidak dibatasi hanya yang ada di file excel.</p>
                        </div>

                        <!-- 2. NRP / Nomor Karyawan (Ketik Manual) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                NRP / No. Karyawan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="nrp"
                                   placeholder="Contoh: 1183 atau kosongkan untuk otomatis dibuatkan..."
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- 3. Departemen & Plant (Ketik Manual) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    Departemen <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="dept"
                                       placeholder="Contoh: SALES & MARKETING..."
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    Plant <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="plant"
                                       placeholder="Contoh: CIKARANG atau KARAWANG..."
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                        </div>



                        <!-- Tombol Aksi -->
                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" @click="addAssessorModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md transition">
                                Tambahkan Penilai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL EDIT DATA PENILAI KELOMPOK (INPUT KETIK MANUAL, BUKAN DROPDOWN) -->
        <!-- ========================================================================= -->
        <div x-show="editAssessorModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog" aria-modal="true">
            <div class="min-h-screen px-4 text-center flex items-center justify-center">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                     @click="editAssessorModal = false"></div>

                <!-- Modal Content -->
                <div class="inline-block w-full max-w-lg p-6 my-8 text-left align-middle transition-all transform bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 relative z-10">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                    Edit Data Penilai Kelompok
                                </h3>
                                <p class="text-xs text-slate-500">Perbarui nama atau data penilai langsung secara manual.</p>
                            </div>
                        </div>
                        <button type="button" @click="editAssessorModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            ✕
                        </button>
                    </div>

                    <form method="POST" :action="'/wola/groups/' + editData.group_id + '/assessors/' + editData.id" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Info Kelompok -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                Kelompok
                            </label>
                            <div class="p-3 bg-slate-100 dark:bg-slate-900 rounded-xl font-bold text-xs text-slate-800 dark:text-white"
                                 x-text="editData.group_name">
                            </div>
                        </div>

                        <!-- 1. Nama Lengkap Penilai (Ketik Manual) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Lengkap Penilai <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="fullname" x-model="editData.fullname" required
                                   placeholder="Ketik nama lengkap penilai..."
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- 2. NRP / Nomor Karyawan (Ketik Manual) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                NRP / No. Karyawan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="nrp" x-model="editData.nrp"
                                   placeholder="Nomor induk karyawan..."
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        <!-- 3. Departemen & Plant (Ketik Manual) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    Departemen <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="dept" x-model="editData.dept"
                                       placeholder="Contoh: SALES & MARKETING..."
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                    Plant <span class="text-slate-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="plant" x-model="editData.plant"
                                       placeholder="Contoh: CIKARANG atau KARAWANG..."
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                        </div>



                        <!-- Tombol Aksi -->
                        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" @click="editAssessorModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
