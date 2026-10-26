<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Pusat Laporan & Rekapitulasi WOLA') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Laporan komprehensif penilaian beban kerja WOLA MUSASHI
                </p>
            </div>

            <!-- Tombol Aksi Ekspor & Cetak -->
            <div class="flex flex-wrap items-center gap-2">
                @if(Auth::user()->hasRole(['superadmin', 'admin']))
                    <a href="{{ route('wola.reports.export.dashboard-excel') }}"
                       class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5"
                       title="Unduh file Excel lengkap dengan Sheet Dashboard (Pivot Rekapitulasi & 2 Diagram Batang Native)">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Download Excel (Dashboard & Chart)</span>
                    </a>
                @endif

                <a href="{{ route('wola.reports.export.excel', ['group_id' => request('group_id', ($selectedGroup ? $selectedGroup->id : 'all')), 'job_id' => request('job_id')]) }}"
                   class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Ekspor Excel Matriks (.xlsx)</span>
                </a>

                <a href="{{ route('wola.reports.print', ['group_id' => request('group_id', ($selectedGroup ? $selectedGroup->id : 'all')), 'job_id' => request('job_id')]) }}"
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm transition">
                    <svg class="w-4 h-4 me-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Cetak Laporan / PDF</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center space-x-3 text-emerald-800 dark:text-emerald-200 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl flex items-center space-x-3 text-rose-800 dark:text-rose-200 shadow-sm">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Navigasi Tab Laporan -->
            <div class="flex border-b border-slate-200 bg-white px-6 pt-3 rounded-2xl shadow-xs">
                <nav class="flex space-x-6">
                    <a href="{{ route('wola.reports.index', ['tab' => 'matrix', 'group_id' => request('group_id', ($selectedGroup ? $selectedGroup->id : 'all')), 'job_id' => request('job_id')]) }}"
                       class="py-3 px-1 border-b-2 font-bold text-xs transition flex items-center space-x-2
                       {{ $tab === 'matrix' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        <span>Matriks Karyawan × Penilai per Jabatan</span>
                    </a>

                    <a href="{{ route('wola.reports.index', ['tab' => 'details', 'detail_group_id' => request('detail_group_id', request('group_id', 'all'))]) }}"
                       class="py-3 px-1 border-b-2 font-bold text-xs transition flex items-center space-x-2
                       {{ $tab === 'details' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Tabel Detail Penilaian</span>
                    </a>

                    <a href="{{ route('wola.reports.index', ['tab' => 'summary', 'group_id' => ($selectedGroup ? $selectedGroup->id : null)]) }}"
                       class="py-3 px-1 border-b-2 font-bold text-xs transition flex items-center space-x-2
                       {{ $tab === 'summary' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>{{ $isKetuaOnly ? 'Ringkasan Progres Kelompok' : 'Ringkasan Progres Seluruh Kelompok' }}</span>
                    </a>

                    @if(Auth::user()->hasRole(['superadmin', 'admin']))
                    <a href="{{ route('wola.reports.index', ['tab' => 'group_summary', 'group_id' => ($selectedGroup ? $selectedGroup->id : null)]) }}"
                       class="py-3 px-1 border-b-2 font-bold text-xs transition flex items-center space-x-2
                       {{ $tab === 'group_summary' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Summary Kelompok</span>
                    </a>
                    @endif
                </nav>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 1: MATRIKS KARYAWAN × PENILAI PER JABATAN -->
            <!-- ============================================================= -->
            @if($tab === 'matrix')
                <div class="space-y-6">
                    <!-- Filter Toolbar Kelompok & Jabatan -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <form method="GET" action="{{ route('wola.reports.index') }}" class="flex flex-wrap items-center gap-3">
                            <input type="hidden" name="tab" value="matrix">

                            @if(!$isKetuaOnly)
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Kelompok:</label>
                                    <select name="group_id" onchange="this.form.submit()"
                                            class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                        @if(Auth::user()->hasRole(['superadmin', 'admin']))
                                            <option value="all" {{ ($groupId === 'all') ? 'selected' : '' }}>
                                                🌐 Semua Kelompok (Seluruh Area 1-12)
                                            </option>
                                        @endif
                                        @foreach($allGroups as $g)
                                            <option value="{{ $g->id }}" {{ ($groupId != 'all' && $selectedGroup && $selectedGroup->id == $g->id) ? 'selected' : '' }}>
                                                Kelompok {{ $g->number }}: {{ $g->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if(!$isAllGroups && $selectedGroup)
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Jabatan yang Dinilai:</label>
                                    <select name="job_id" onchange="this.form.submit()"
                                            class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                        @forelse($jobs as $j)
                                            <option value="{{ $j->id }}" {{ $selectedJob && $selectedJob->id == $j->id ? 'selected' : '' }}>
                                                {{ $j->name }}
                                            </option>
                                        @empty
                                            <option value="">Belum ada jabatan</option>
                                        @endforelse
                                    </select>
                                </div>
                            @endif
                        </form>

                        @if(!$isAllGroups && $selectedGroup && $selectedJob)
                            <div class="text-right">
                                <span class="text-xs text-slate-400 block">Jabatan Terpilih:</span>
                                <strong class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                    {{ $selectedJob->name }}
                                </strong>
                            </div>
                        @elseif($isAllGroups)
                            <div class="text-right">
                                <span class="text-xs text-slate-400 block">Mode Matriks:</span>
                                <strong class="text-sm font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1.5 justify-end">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Seluruh 12 Kelompok Kerja
                                </strong>
                            </div>
                        @endif
                    </div>

                    @if($isAllGroups)
                        <!-- 4 Stat Card Global untuk Seluruh Kelompok -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- 1. Total Kelompok -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shadow-xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                        100% Terdaftar
                                    </span>
                                </div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Total Area Kelompok</span>
                                <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                    {{ $globalMatrixStats['total_groups'] }} <span class="text-xs text-slate-400 font-semibold">Kelompok Kerja</span>
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                    Kelompok 1 s.d. 12 WOLA MUSASHI
                                </p>
                            </div>

                            <!-- 2. Total Karyawan Target -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shadow-xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                        Sasaran
                                    </span>
                                </div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Total Sasaran Karyawan</span>
                                <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                    {{ number_format($globalMatrixStats['total_employees']) }} <span class="text-xs text-slate-400 font-semibold">Orang</span>
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                    Seluruh karyawan yang menjadi target
                                </p>
                            </div>

                            <!-- 3. Total Penilai -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shadow-xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                        Asesor
                                    </span>
                                </div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Total Tim Penilai</span>
                                <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                    {{ $globalMatrixStats['total_assessors'] }} <span class="text-xs text-slate-400 font-semibold">Penilai</span>
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                    Terbagi pada 12 tim kerja
                                </p>
                            </div>

                            <!-- 4. Total Checklist Selesai -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shadow-xs">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                        Verifikasi
                                    </span>
                                </div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Checklist Terverifikasi</span>
                                <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                    {{ number_format($globalMatrixStats['total_checks']) }} <span class="text-xs text-slate-400 font-semibold">Unit</span>
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">
                                    Total centang checklist yang masuk
                                </p>
                            </div>
                        </div>

                        <!-- Quick Jump Bar -->
                        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Lompat Cepat ke Kelompok:
                                </span>
                                <span class="text-[11px] text-slate-400 font-semibold">12 Kelompok Tersedia</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($allGroupsMatrixData as $gData)
                                    <a href="#kelompok-{{ $gData['group']->id }}"
                                       class="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-100 hover:bg-blue-50 dark:bg-slate-700 dark:hover:bg-blue-900/40 text-slate-700 hover:text-blue-600 dark:text-slate-200 dark:hover:text-blue-300 border border-slate-200 dark:border-slate-600 transition flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span>G{{ $gData['group']->number }}</span>
                                        <span class="text-[10px] text-slate-400">({{ $gData['employees']->count() }})</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <!-- Matriks Seluruh 12 Kelompok -->
                        <div class="space-y-8">
                            @foreach($allGroupsMatrixData as $gData)
                                <div id="kelompok-{{ $gData['group']->id }}"
                                     x-data="{ activeJobId: '{{ $gData['firstJobId'] }}' }"
                                     class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden scroll-mt-24">
                                    
                                    <!-- Header Kelompok -->
                                    <div class="p-5 bg-gradient-to-r from-slate-50 via-white to-blue-50/30 dark:from-slate-900/80 dark:via-slate-800 dark:to-blue-950/20 border-b border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-black bg-blue-600 text-white">
                                                    Kelompok {{ $gData['group']->number }}
                                                </span>
                                                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">
                                                    {{ $gData['group']->name }}
                                                </h3>
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                                                <span>Ketua: <strong class="text-slate-700 dark:text-slate-300">{{ $gData['group']->leader ? $gData['group']->leader->name : 'Belum ditentukan' }}</strong></span>
                                                <span>•</span>
                                                <span>Penilai: <strong class="text-slate-700 dark:text-slate-300">{{ $gData['assessors']->count() }} Orang</strong></span>
                                                <span>•</span>
                                                <span>Target: <strong class="text-slate-700 dark:text-slate-300">{{ $gData['employees']->count() }} Karyawan</strong></span>
                                                <span>•</span>
                                                <span>Jabatan: <strong class="text-slate-700 dark:text-slate-300">{{ $gData['jobs']->count() }} Posisi</strong></span>
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('wola.reports.index', ['tab' => 'matrix', 'group_id' => $gData['group']->id]) }}"
                                               class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950 dark:hover:bg-blue-900 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 transition flex items-center gap-1.5 shadow-2xs">
                                                <span>Fokus Kelompok Ini</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </a>
                                        </div>
                                    </div>

                                    @if($gData['jobs']->isNotEmpty())
                                        <!-- Sub Toolbar: Pemilihan Jabatan di Kelompok Ini -->
                                        <div class="px-5 py-3 bg-slate-50/70 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="flex items-center gap-2">
                                                <label class="text-xs font-bold text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                                    Pilih Posisi Jabatan:
                                                </label>
                                                <select x-model="activeJobId"
                                                        class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl text-xs font-bold text-blue-600 dark:text-blue-400 focus:ring-2 focus:ring-blue-500 shadow-2xs">
                                                    @foreach($gData['jobs'] as $j)
                                                        <option value="{{ $j->id }}">{{ $j->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center space-x-3 text-slate-400 text-[11px]">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="inline-block w-3 h-3 rounded bg-emerald-500"></span>
                                                    <span class="text-slate-600 dark:text-slate-300 font-semibold">Sudah Dinilai (✓)</span>
                                                </span>
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="inline-block w-3 h-3 rounded bg-slate-200 dark:bg-slate-700"></span>
                                                    <span>Belum (-)</span>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Render Tabel Matriks per Jabatan -->
                                        @foreach($gData['jobs'] as $j)
                                            @php
                                                $jStats = $gData['jobStats'][$j->id] ?? null;
                                            @endphp
                                            <div x-show="activeJobId == '{{ $j->id }}'" class="overflow-x-auto">
                                                @if($jStats)
                                                    <div class="px-5 py-2.5 bg-slate-100/50 dark:bg-slate-900/20 border-b border-slate-200 dark:border-slate-700/60 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
                                                        <div>
                                                            Posisi: <strong class="text-slate-800 dark:text-slate-200 font-bold">{{ $j->name }}</strong>
                                                        </div>
                                                        <div class="flex items-center gap-3 text-[11px]">
                                                            <span>Lengkap: <strong class="text-emerald-600 dark:text-emerald-400">{{ $jStats['full'] }} Orang ({{ $jStats['full_pct'] }}%)</strong></span>
                                                            <span>•</span>
                                                            <span>Sebagian: <strong class="text-amber-600 dark:text-amber-400">{{ ($jStats['one'] ?? 0) + ($jStats['two'] ?? ($jStats['partial'] ?? 0)) }} Orang</strong></span>
                                                            <span>•</span>
                                                            <span>Belum: <strong class="text-rose-600 dark:text-rose-400">{{ $jStats['zero'] }} Orang ({{ $jStats['zero_pct'] }}%)</strong></span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                                                    <thead class="bg-slate-100/80 dark:bg-slate-900 font-bold text-slate-700 dark:text-slate-300">
                                                        <tr>
                                                            <th scope="col" class="py-3 px-4 sticky left-0 z-10 bg-slate-100 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 min-w-[220px]">
                                                                EMPLOYEENO & FULLNAME
                                                            </th>
                                                            @foreach($gData['assessors'] as $assessor)
                                                                <th scope="col" class="py-3 px-3 text-center whitespace-nowrap min-w-[120px]">
                                                                    <div class="font-bold line-clamp-1" title="{{ $assessor->employee ? $assessor->employee->fullname : 'Penilai' }}">
                                                                        {{ $assessor->employee ? $assessor->employee->fullname : 'Penilai' }}
                                                                    </div>
                                                                    <div class="text-[10px] font-mono text-slate-400 font-normal">
                                                                        {{ $assessor->employee ? $assessor->employee->nrp : '' }} {{ $assessor->is_leader ? '(Ketua)' : '' }}
                                                                    </div>
                                                                </th>
                                                            @endforeach
                                                            <th scope="col" class="py-3 px-4 text-center whitespace-nowrap min-w-[100px] bg-slate-50 dark:bg-slate-900">
                                                                Total Selesai
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                                        @forelse($gData['employees'] as $emp)
                                                            @php
                                                                $doneCount = 0;
                                                                foreach ($gData['assessors'] as $ass) {
                                                                    if (isset($gData['matrixData'][$j->id][$emp->id][$ass->id])) {
                                                                        $doneCount++;
                                                                    }
                                                                }
                                                                $allDone = ($doneCount === $gData['assessors']->count() && $gData['assessors']->count() > 0);
                                                            @endphp
                                                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition">
                                                                <td class="py-2.5 px-4 sticky left-0 z-10 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                                                    <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                                                                        <span class="font-mono text-blue-600 dark:text-blue-400 font-bold">{{ $emp->nrp }}</span>
                                                                        <span>{{ $emp->fullname }}</span>
                                                                    </div>
                                                                </td>

                                                                @foreach($gData['assessors'] as $ass)
                                                                    @php
                                                                        $isCheck = isset($gData['matrixData'][$j->id][$emp->id][$ass->id]);
                                                                        $checkInfo = $isCheck ? $gData['matrixData'][$j->id][$emp->id][$ass->id]['checked_at'] : null;
                                                                    @endphp
                                                                    <td class="py-2 px-3 text-center align-middle">
                                                                        @if($isCheck)
                                                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 font-extrabold shadow-2xs"
                                                                                  title="Dinilai pada {{ $checkInfo }}">
                                                                                ✓
                                                                            </span>
                                                                        @else
                                                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-300 dark:text-slate-600">
                                                                                -
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                @endforeach

                                                                <td class="py-2 px-4 text-center font-bold text-xs bg-slate-50/50 dark:bg-slate-900/40">
                                                                    <span class="px-2.5 py-0.5 rounded-full {{ $allDone ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                                                        {{ $doneCount }} / {{ $gData['assessors']->count() }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="{{ $gData['assessors']->count() + 2 }}" class="py-6 text-center text-slate-400">
                                                                    Belum ada target karyawan pada kelompok ini.
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-8 text-center text-slate-400 text-xs">
                                            Belum ada posisi jabatan terdaftar pada kelompok ini.
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @elseif($selectedGroup && $selectedJob)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- 1. Belum Dinilai (Kosong / 0 Penilai) -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold shadow-xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            {{ $jobMatrixStats['zero_pct'] }}%
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Belum Dinilai (Kosong)</span>
                                    <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                        {{ $jobMatrixStats['zero'] }} <span class="text-xs text-slate-400 font-semibold">/ {{ $jobMatrixStats['total_employees'] }} Orang</span>
                                    </h4>
                                </div>
                                <div class="mt-3">
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-rose-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $jobMatrixStats['zero_pct'] }}%"></div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                                        <span>0 Penilai menilai</span>
                                        <span class="font-bold text-rose-600 dark:text-rose-400">{{ $jobMatrixStats['zero'] }} Menunggu</span>
                                    </p>
                                </div>
                            </div>

                            <!-- 2. Baru 1 Penilai Menilai -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shadow-xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                            {{ $jobMatrixStats['one_pct'] }}%
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Baru 1 Penilai</span>
                                    <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                        {{ $jobMatrixStats['one'] }} <span class="text-xs text-slate-400 font-semibold">/ {{ $jobMatrixStats['total_employees'] }} Orang</span>
                                    </h4>
                                </div>
                                <div class="mt-3">
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $jobMatrixStats['one_pct'] }}%"></div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                                        <span>Baru dinilai 1 penilai</span>
                                        <span class="font-bold text-amber-600 dark:text-amber-400">{{ $jobMatrixStats['one'] }} Orang</span>
                                    </p>
                                </div>
                            </div>

                            <!-- 3. 2 Penilai Menilai -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shadow-xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            {{ $jobMatrixStats['two_pct'] }}%
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">
                                        {{ $matrixAssessors->count() == 3 ? '2 Penilai Menilai' : 'Sebagian Penilai' }}
                                    </span>
                                    <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                        {{ $jobMatrixStats['two'] }} <span class="text-xs text-slate-400 font-semibold">/ {{ $jobMatrixStats['total_employees'] }} Orang</span>
                                    </h4>
                                </div>
                                <div class="mt-3">
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $jobMatrixStats['two_pct'] }}%"></div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                                        <span>Progres penilai kedua</span>
                                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $jobMatrixStats['two'] }} Orang</span>
                                    </p>
                                </div>
                            </div>

                            <!-- 4. Full Dinilai (Semua Penilai) -->
                            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shadow-xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ $jobMatrixStats['full_pct'] }}%
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">
                                        Full Dinilai ({{ $matrixAssessors->count() }} Penilai)
                                    </span>
                                    <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                                        {{ $jobMatrixStats['full'] }} <span class="text-xs text-slate-400 font-semibold">/ {{ $jobMatrixStats['total_employees'] }} Orang</span>
                                    </h4>
                                </div>
                                <div class="mt-3">
                                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $jobMatrixStats['full_pct'] }}%"></div>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                                        <span>Lengkap seluruh penilai</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $jobMatrixStats['full'] }} Selesai</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Tabel Matriks -->
                    @if($selectedGroup && $selectedJob)
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center text-xs">
                                <div>
                                    Kelompok: <strong class="text-slate-800 dark:text-slate-200">{{ $selectedGroup->name }}</strong>
                                    • Total Penilai: <strong>{{ $matrixAssessors->count() }}</strong>
                                    • Total Target: <strong>{{ $matrixEmployees->count() }}</strong> Karyawan
                                </div>
                                <div class="flex items-center space-x-2 text-slate-400 text-[11px]">
                                    <span class="inline-block w-3 h-3 rounded bg-emerald-500"></span>
                                    <span>Sudah Dinilai (✓)</span>
                                    <span class="inline-block w-3 h-3 rounded bg-slate-300 dark:bg-slate-700 ms-2"></span>
                                    <span>Belum (-)</span>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                                    <thead class="bg-slate-100/80 dark:bg-slate-900 font-bold text-slate-700 dark:text-slate-300">
                                        <tr>
                                            <th scope="col" class="py-3 px-4 sticky left-0 z-10 bg-slate-100 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 min-w-[220px]">
                                                EMPLOYEENO & FULLNAME
                                            </th>
                                            @foreach($matrixAssessors as $assessor)
                                                <th scope="col" class="py-3 px-3 text-center whitespace-nowrap min-w-[120px]">
                                                    <div class="font-bold line-clamp-1" title="{{ $assessor->employee->fullname }}">
                                                        {{ $assessor->employee->fullname }}
                                                    </div>
                                                    <div class="text-[10px] font-mono text-slate-400 font-normal">
                                                        {{ $assessor->employee->nrp }} {{ $assessor->is_leader ? '(Ketua)' : '' }}
                                                    </div>
                                                </th>
                                            @endforeach
                                            <th scope="col" class="py-3 px-4 text-center whitespace-nowrap min-w-[100px] bg-slate-50 dark:bg-slate-900">
                                                Total Selesai
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                        @forelse($matrixEmployees as $emp)
                                            @php
                                                $doneCount = 0;
                                                foreach ($matrixAssessors as $ass) {
                                                    if (isset($matrixData[$emp->id][$ass->id])) {
                                                        $doneCount++;
                                                    }
                                                }
                                                $allDone = ($doneCount === $matrixAssessors->count() && $matrixAssessors->count() > 0);
                                            @endphp
                                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition">
                                                <td class="py-3 px-4 sticky left-0 z-10 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                                    <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                                                        <span class="font-mono text-blue-600 dark:text-blue-400 font-bold">{{ $emp->nrp }}</span>
                                                        <span>{{ $emp->fullname }}</span>
                                                    </div>
                                                </td>

                                                @foreach($matrixAssessors as $ass)
                                                    @php
                                                        $isCheck = isset($matrixData[$emp->id][$ass->id]);
                                                        $checkInfo = $isCheck ? $matrixData[$emp->id][$ass->id]['checked_at'] : null;
                                                    @endphp
                                                    <td class="py-2.5 px-3 text-center align-middle">
                                                        @if($isCheck)
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 font-extrabold shadow-sm"
                                                                  title="Dinilai pada {{ $checkInfo }}">
                                                                ✓
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-300 dark:text-slate-600">
                                                                -
                                                            </span>
                                                        @endif
                                                    </td>
                                                @endforeach

                                                <td class="py-2.5 px-4 text-center font-bold text-xs bg-slate-50/50 dark:bg-slate-900/40">
                                                    <span class="px-2.5 py-1 rounded-full {{ $allDone ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                                        {{ $doneCount }} / {{ $matrixAssessors->count() }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $matrixAssessors->count() + 2 }}" class="py-8 text-center text-slate-400">
                                                    Belum ada target karyawan pada kelompok ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="p-12 text-center text-slate-400 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                            Pilih kelompok dan jabatan untuk melihat matriks detail.
                        </div>
                    @endif
                </div>
            @endif

            <!-- ============================================================= -->
            <!-- TAB 2: TABEL DETAIL "SIAPA MENILAI SIAPA" -->
            <!-- ============================================================= -->
            @if($tab === 'details')
                <div class="space-y-6">
                    <!-- Filter Toolbar Detail -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <form method="GET" action="{{ route('wola.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                            <input type="hidden" name="tab" value="details">

                            <!-- Search -->
                            <div class="{{ !$isKetuaOnly ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Cari Karyawan / Penilai</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari nama atau NRP..."
                                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                            </div>

                            @if(!$isKetuaOnly)
                                <div class="lg:col-span-3">
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Filter Kelompok</label>
                                    <select name="detail_group_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                        @if(Auth::user()->hasRole(['superadmin', 'admin']))
                                            <option value="all" {{ ($detailGroupId === 'all' || empty($detailGroupId)) ? 'selected' : '' }}>
                                                🌐 Semua Kelompok (Seluruh Area 1-12)
                                            </option>
                                        @endif
                                        @foreach($allGroups as $g)
                                            <option value="{{ $g->id }}" {{ ($detailGroupId != 'all' && $detailGroupId == $g->id) ? 'selected' : '' }}>
                                                Kelompok {{ $g->number }}: {{ $g->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="{{ !$isKetuaOnly ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Filter Posisi Jabatan</label>
                                <select name="job_id_filter" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                    <option value="">Semua Jabatan</option>
                                    @if(isset($detailJobs))
                                        @foreach($detailJobs as $dj)
                                            <option value="{{ $dj->id }}" {{ request('job_id_filter') == $dj->id ? 'selected' : '' }}>
                                                {{ $dj->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Opsi Tampilkan / Baris per Halaman -->
                            <div class="{{ !$isKetuaOnly ? 'lg:col-span-2' : 'lg:col-span-2' }}">
                                <label class="block text-xs font-semibold text-slate-500 mb-1">Tampilkan Baris</label>
                                <select name="per_page" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500 font-medium">
                                    <option value="25" {{ ($perPage ?? 25) == 25 ? 'selected' : '' }}>25 Baris (Standar)</option>
                                    <option value="50" {{ ($perPage ?? 25) == 50 ? 'selected' : '' }}>50 Baris</option>
                                    <option value="100" {{ ($perPage ?? 25) == 100 ? 'selected' : '' }}>⚡ 100 Baris (Langsung)</option>
                                    <option value="250" {{ ($perPage ?? 25) == 250 ? 'selected' : '' }}>250 Baris</option>
                                    <option value="500" {{ ($perPage ?? 25) == 500 ? 'selected' : '' }}>500 Baris</option>
                                </select>
                            </div>

                            <!-- Tombol Aksi Filter, Reset, dan Hapus (Khusus Superadmin) -->
                            <div class="{{ !$isKetuaOnly ? 'lg:col-span-2' : 'lg:col-span-3' }} flex items-end gap-1.5 flex-wrap">
                                <button type="submit"
                                        class="px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-sm transition whitespace-nowrap">
                                    Terapkan Filter
                                </button>
                                <a href="{{ route('wola.reports.index', ['tab' => 'details', 'detail_group_id' => 'all']) }}"
                                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition text-center whitespace-nowrap">
                                    Reset
                                </a>
                                @if(Auth::user()->hasRole('superadmin'))
                                    <!-- Shortcut 100 Data untuk Superadmin jika belum 100 -->
                                    @if(($perPage ?? 25) != 100)
                                        <a href="{{ request()->fullUrlWithQuery(['per_page' => 100]) }}"
                                           class="px-2.5 py-2 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-bold text-xs rounded-xl border border-amber-300 dark:border-amber-700 transition flex items-center gap-1 shadow-xs whitespace-nowrap"
                                           title="Khusus Superadmin: Tampilkan langsung 100 baris data per halaman">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>100 Data</span>
                                        </a>
                                    @endif

                                    <!-- Tombol Hapus Semua Data Penilaian (Khusus Superadmin) -->
                                    <button type="button"
                                            onclick="openDeleteAssessmentsModal()"
                                            class="px-3 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1 whitespace-nowrap"
                                            title="Khusus Superadmin: Hapus Semua Data Checklist Penilaian">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>Hapus Semua</span>
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Header Ringkasan & Total Data Detail -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-1 text-xs text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <span>Menampilkan <strong>{{ $detailAssessments->firstItem() ?? 0 }} - {{ $detailAssessments->lastItem() ?? 0 }}</strong> dari <strong>{{ $detailAssessments->total() }}</strong> catatan checklist penilaian</span>
                            @if(($perPage ?? 25) == 100)
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 font-bold text-[10px]">
                                    Mode 100 Baris / Halaman
                                </span>
                            @endif
                        </div>
                        @if(Auth::user()->hasRole('superadmin'))
                            <div class="text-[11px] text-slate-400 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                                <span>Akses Superadmin: Kontrol Hapus & 100 Baris Aktif</span>
                            </div>
                        @endif
                    </div>

                    <!-- Tabel Komprehensif Siapa Menilai Siapa -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-900/60 font-bold text-slate-500 dark:text-slate-400">
                                    <tr>
                                        <th scope="col" class="py-3 px-4 w-36">Waktu Ceklis</th>
                                        <th scope="col" class="py-3 px-3 w-40">Area Kelompok</th>
                                        <th scope="col" class="py-3 px-4">Penilai (Assessor)</th>
                                        <th scope="col" class="py-3 px-4">Karyawan Target</th>
                                        <th scope="col" class="py-3 px-4">Jabatan yang Dinilai</th>
                                        <th scope="col" class="py-3 px-3 w-32">Pencatat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                    @forelse($detailAssessments as $item)
                                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition">
                                            <td class="py-2.5 px-4 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                                {{ $item->checked_at ? $item->checked_at->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $item->group->name }}</span>
                                            </td>
                                            <td class="py-2.5 px-4">
                                                @if($item->assessor && $item->assessor->employee)
                                                    <div class="font-bold text-slate-900 dark:text-white">
                                                        {{ $item->assessor->employee->fullname }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono">
                                                        NRP: {{ $item->assessor->employee->nrp }}
                                                    </div>
                                                @else
                                                    <span class="text-slate-400">Penilai #{{ $item->assessor_id }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-4">
                                                @if($item->employee)
                                                    <div class="font-bold text-slate-900 dark:text-white">
                                                        {{ $item->employee->fullname }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono">
                                                        NRP: {{ $item->employee->nrp }} • {{ $item->employee->dept }}
                                                    </div>
                                                @else
                                                    <span class="text-slate-400">Karyawan #{{ $item->employee_id }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-4">
                                                @if($item->job)
                                                    <span class="font-semibold text-slate-800 dark:text-slate-200">
                                                        {{ $item->job->name }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400">Jabatan #{{ $item->job_id }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                                {{ $item->creator ? $item->creator->name : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-12 text-center text-slate-400">
                                                Belum ada data checklist penilaian yang tersimpan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($detailAssessments->hasPages())
                            <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                                {{ $detailAssessments->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- ============================================================= -->
            <!-- TAB 3: RINGKASAN CAPAIAN (ADAPTIF KETUA & ADMINISTRATOR) -->
            <!-- ============================================================= -->
            @if($tab === 'summary')
                <div class="space-y-6">

                    @if(!$isKetuaOnly)
                        <!-- TAMPILAN ADMINISTRATOR: TABEL KOMPARATIF 12 KELOMPOK -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                            <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <h3 class="font-extrabold text-base text-slate-900">Rekapitulasi Capaian Seluruh 12 Kelompok Kerja</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Pemantauan progres pengisian checklist WOLA seluruh area proyek WOLA MUSASHI</p>
                                </div>
                                <span class="text-xs font-bold text-slate-400">Total 12 Kelompok Terdaftar</span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                                    <thead class="bg-slate-50 font-bold text-slate-500">
                                        <tr>
                                            <th scope="col" class="py-3 px-4 w-14 text-center">No</th>
                                            <th scope="col" class="py-3 px-4">Nama Area Kelompok</th>
                                            <th scope="col" class="py-3 px-4">Ketua Kelompok</th>
                                            <th scope="col" class="py-3 px-3 text-center">Penilai</th>
                                            <th scope="col" class="py-3 px-3 text-center">Jabatan</th>
                                            <th scope="col" class="py-3 px-3 text-center">Karyawan</th>
                                            <th scope="col" class="py-3 px-4 text-center">Target (Unit)</th>
                                            <th scope="col" class="py-3 px-4 text-center">Realisasi</th>
                                            <th scope="col" class="py-3 px-4 text-center">Progres (%)</th>
                                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach($groupsSummary as $groupItem)
                                            @php
                                                $prog = $groupItem->calculated_progress;
                                                $isActiveGroup = ($selectedGroup && $groupItem->id == $selectedGroup->id);
                                            @endphp
                                            <tr class="hover:bg-slate-50/80 transition cursor-pointer {{ $isActiveGroup ? 'bg-blue-50/40' : '' }}"
                                                onclick="window.location='{{ route('wola.reports.index', ['tab' => 'summary', 'group_id' => $groupItem->id]) }}'">
                                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">
                                                    {{ $groupItem->number }}
                                                </td>
                                                <td class="py-3 px-4 font-bold text-slate-900">
                                                    <a href="{{ route('wola.reports.index', ['tab' => 'summary', 'group_id' => $groupItem->id]) }}" class="hover:text-blue-600 transition">
                                                        {{ $groupItem->name }}
                                                    </a>
                                                    @if($isActiveGroup)
                                                        <span class="ms-1.5 px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700">Terpilih</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-slate-600">
                                                    {{ $groupItem->leader ? $groupItem->leader->name : 'Belum ditentukan' }}
                                                </td>
                                                <td class="py-3 px-3 text-center text-slate-700 font-semibold">
                                                    {{ $prog['assessor_count'] }}
                                                </td>
                                                <td class="py-3 px-3 text-center text-slate-700 font-semibold">
                                                    {{ $prog['job_count'] }}
                                                </td>
                                                <td class="py-3 px-3 text-center text-slate-700 font-semibold">
                                                    {{ $prog['employee_count'] }}
                                                </td>
                                                <td class="py-3 px-4 text-center font-mono text-slate-600">
                                                    {{ number_format($prog['target']) }}
                                                </td>
                                                <td class="py-3 px-4 text-center font-mono font-bold text-blue-600">
                                                    {{ number_format($prog['checked']) }}
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $prog['percentage'] }}%"></div>
                                                        </div>
                                                        <span class="font-extrabold text-xs text-slate-800">{{ $prog['percentage'] }}%</span>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full
                                                        @if($prog['status_color'] === 'green') bg-emerald-50 text-emerald-700 border border-emerald-200
                                                        @elseif($prog['status_color'] === 'yellow') bg-amber-50 text-amber-700 border border-amber-200
                                                        @elseif($prog['status_color'] === 'red') bg-rose-50 text-rose-700 border border-rose-200
                                                        @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                                                        {{ $prog['status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- RINCIAN PROGRES AREA SPESIFIK (TAMPILAN UTAMA KETUA & DRILL-DOWN ADMINISTRATOR) -->
                    @if($selectedGroup)
                        <!-- 1. Header Banner Capaian Area -->
                        <div class="bg-white p-6 rounded-2xl shadow-xs border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $isKetuaOnly ? 'Wilayah Kerja Anda' : 'Rincian Area Terpilih' }}
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        ● Status: <strong class="text-slate-700">{{ $selectedGroupProgress['status'] }}</strong>
                                    </span>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 mt-1">
                                    {{ $selectedGroup->name }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Sebanyak <strong>{{ number_format($selectedGroupProgress['checked']) }}</strong> dari total target <strong>{{ number_format($selectedGroupProgress['target']) }}</strong> unit checklist telah diverifikasi.
                                </p>
                            </div>

                            <div class="flex items-center space-x-4 shrink-0">
                                <div class="text-right">
                                    <span class="text-3xl font-black text-blue-600 tracking-tight">{{ $selectedGroupProgress['percentage'] }}%</span>
                                    <span class="text-xs text-slate-400 block font-semibold">Tingkat Penyelesaian</span>
                                </div>
                                <div class="w-32 bg-slate-100 rounded-full h-3 overflow-hidden">
                                    <div class="bg-blue-600 h-3 rounded-full transition-all duration-700" style="width: {{ $selectedGroupProgress['percentage'] }}%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Ringkasan 3 Parameter Area -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-center justify-between shadow-xs">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tim Penilai</span>
                                    <h4 class="text-lg font-black text-slate-800 mt-0.5">{{ $selectedGroup->assessors->count() }} Orang</h4>
                                </div>
                                <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-center justify-between shadow-xs">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Posisi Jabatan</span>
                                    <h4 class="text-lg font-black text-slate-800 mt-0.5">{{ $jobSummaries->count() }} Posisi</h4>
                                </div>
                                <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </span>
                            </div>

                            <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-center justify-between shadow-xs">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan Target</span>
                                    <h4 class="text-lg font-black text-slate-800 mt-0.5">{{ $employeeSummaries->count() }} Orang</h4>
                                </div>
                                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <!-- 3. Tabel Rincian Capaian Posisi Jabatan (10 Posisi) -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <h4 class="font-extrabold text-sm text-slate-900">
                                        Progres Seluruh Posisi Jabatan ({{ $jobSummaries->count() }} Jabatan)
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Status verifikasi checklist penilaian WOLA untuk setiap posisi jabatan di area ini
                                    </p>
                                </div>
                                <span class="text-xs font-bold text-slate-400">
                                    Total Target: {{ number_format($selectedGroupProgress['target']) }} unit
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                                    <thead class="bg-slate-50 font-bold text-slate-500">
                                        <tr>
                                            <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                                            <th scope="col" class="py-3 px-4">Posisi Jabatan</th>
                                            <th scope="col" class="py-3 px-3 text-center">Target Orang</th>
                                            <th scope="col" class="py-3 px-3 text-center">Ceklis Terisi</th>
                                            <th scope="col" class="py-3 px-3 text-center">Target Ceklis</th>
                                            <th scope="col" class="py-3 px-4 text-center min-w-[140px]">Capaian Progres</th>
                                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse($jobSummaries as $index => $js)
                                            <tr class="hover:bg-slate-50/70 transition">
                                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-400">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="py-3 px-4 font-bold text-slate-900">
                                                    {{ $js['name'] }}
                                                </td>
                                                <td class="py-3 px-3 text-center font-semibold text-slate-700">
                                                    {{ $js['target_employees'] }} Orang
                                                </td>
                                                <td class="py-3 px-3 text-center font-bold text-blue-600 font-mono">
                                                    {{ number_format($js['checked_units']) }}
                                                </td>
                                                <td class="py-3 px-3 text-center font-mono text-slate-500">
                                                    {{ number_format($js['target_units']) }}
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $js['percentage'] }}%"></div>
                                                        </div>
                                                        <span class="font-extrabold text-xs text-slate-800">{{ $js['percentage'] }}%</span>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full
                                                        @if($js['color'] === 'emerald') bg-emerald-50 text-emerald-700 border border-emerald-200
                                                        @elseif($js['color'] === 'amber') bg-amber-50 text-amber-700 border border-amber-200
                                                        @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                                        {{ $js['status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-8 text-center text-slate-400">
                                                    Belum ada posisi jabatan yang terdaftar pada area ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 4. Tabel Status Evaluasi Karyawan Sasaran & Keterangan Penilai (28 Karyawan) -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden"
                             x-data="{
                                 searchEmp: '',
                                 filterStatus: 'all',
                                 matchesEmp(name, nrp, status) {
                                     const matchQuery = this.searchEmp === '' || 
                                         name.toLowerCase().includes(this.searchEmp.toLowerCase()) || 
                                         nrp.toLowerCase().includes(this.searchEmp.toLowerCase());
                                     if (!matchQuery) return false;
                                     if (this.filterStatus === 'all') return true;
                                     if (this.filterStatus === 'completed') return status === 'Lengkap (100%)';
                                     if (this.filterStatus === 'partial') return status === 'Sebagian';
                                     if (this.filterStatus === 'none') return status === 'Belum Dinilai';
                                     return true;
                                 }
                             }">
                            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="font-extrabold text-sm text-slate-900">
                                        Status Evaluasi Karyawan Sasaran & Keterangan Penilai ({{ $employeeSummaries->count() }} Karyawan)
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Rincian seluruh karyawan target beserta daftar anggota tim penilai yang telah melakukan evaluasi
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="relative w-48 sm:w-56">
                                        <input type="text" x-model="searchEmp" placeholder="Cari nama / NRP..."
                                               class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-blue-500">
                                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>

                                    <div class="inline-flex p-1 bg-slate-100 rounded-xl text-xs font-bold">
                                        <button type="button" @click="filterStatus = 'all'"
                                                :class="filterStatus === 'all' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500'"
                                                class="px-2.5 py-1 rounded-lg transition">Semua</button>
                                        <button type="button" @click="filterStatus = 'completed'"
                                                :class="filterStatus === 'completed' ? 'bg-white text-emerald-600 shadow-xs' : 'text-slate-500'"
                                                class="px-2.5 py-1 rounded-lg transition">Lengkap</button>
                                        <button type="button" @click="filterStatus = 'partial'"
                                                :class="filterStatus === 'partial' ? 'bg-white text-amber-600 shadow-xs' : 'text-slate-500'"
                                                class="px-2.5 py-1 rounded-lg transition">Sebagian</button>
                                        <button type="button" @click="filterStatus = 'none'"
                                                :class="filterStatus === 'none' ? 'bg-white text-rose-600 shadow-xs' : 'text-slate-500'"
                                                class="px-2.5 py-1 rounded-lg transition">Belum</button>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                                    <thead class="bg-slate-50 font-bold text-slate-500">
                                        <tr>
                                            <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                                            <th scope="col" class="py-3 px-3 font-mono">EMPLOYEENO</th>
                                            <th scope="col" class="py-3 px-4 min-w-[160px]">FULLNAME</th>
                                            <th scope="col" class="py-3 px-3">DIVISION & DEPT</th>
                                            <th scope="col" class="py-3 px-3 text-center">Ceklis Terisi</th>
                                            <th scope="col" class="py-3 px-4 min-w-[260px]">Penilai yang Menilai</th>
                                            <th scope="col" class="py-3 px-4 text-center">Status Kelengkapan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse($employeeSummaries as $index => $es)
                                            <tr x-show="matchesEmp('{{ addslashes($es['fullname']) }}', '{{ $es['nrp'] }}', '{{ $es['status'] }}')"
                                                class="hover:bg-slate-50/70 transition">
                                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-400">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="py-3 px-3 font-mono font-bold text-blue-600">
                                                    {{ $es['nrp'] }}
                                                </td>
                                                <td class="py-3 px-4 font-bold text-slate-900">
                                                    {{ $es['fullname'] }}
                                                </td>
                                                <td class="py-3 px-3 text-slate-500">
                                                    {{ $es['dept'] }} • {{ $es['section'] }}
                                                </td>
                                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-800">
                                                    {{ $es['checked_count'] }} <span class="text-slate-400 font-normal">/ {{ $es['expected_count'] }}</span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        @forelse($es['assessors'] as $assr)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold
                                                                {{ $assr['is_leader'] ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}"
                                                                title="{{ $assr['name'] }} telah mengisi {{ $assr['checked_count'] }} dari {{ $assr['total_jobs'] }} jabatan">
                                                                <span>{{ $assr['name'] }}</span>
                                                                @if($assr['is_leader'])
                                                                    <span class="ms-1 text-[9px] text-blue-500 font-extrabold">(Ketua)</span>
                                                                @endif
                                                                <span class="ms-1 text-slate-400 font-mono text-[10px]">[{{ $assr['checked_count'] }}/{{ $assr['total_jobs'] }}]</span>
                                                            </span>
                                                        @empty
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                                Belum ada penilai
                                                            </span>
                                                        @endforelse
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full
                                                        @if($es['color'] === 'emerald') bg-emerald-50 text-emerald-700 border border-emerald-200
                                                        @elseif($es['color'] === 'amber') bg-amber-50 text-amber-700 border border-amber-200
                                                        @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                                        {{ $es['status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-8 text-center text-slate-400">
                                                    Belum ada karyawan sasaran yang terdaftar pada area ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 5. Tabel Kontribusi Tim Penilai -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div>
                                    <h4 class="font-extrabold text-sm text-slate-900">
                                        Kontribusi Anggota Tim Penilai ({{ $assessorSummaries->count() }} Penilai)
                                    </h4>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Rincian capaian pengisian ceklis dari masing-masing penilai yang ditugaskan pada area ini
                                    </p>
                                </div>
                                <span class="text-xs font-bold text-slate-400">
                                    {{ $assessorSummaries->count() }} Penilai Aktif
                                </span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                                    <thead class="bg-slate-50 font-bold text-slate-500">
                                        <tr>
                                            <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                                            <th scope="col" class="py-3 px-4">FULLNAME (Penilai)</th>
                                            <th scope="col" class="py-3 px-3 text-center">Peran</th>
                                            <th scope="col" class="py-3 px-3 font-mono">EMPLOYEENO</th>
                                            <th scope="col" class="py-3 px-4">DIVISION & DEPT</th>
                                            <th scope="col" class="py-3 px-3 text-center">Ceklis Selesai</th>
                                            <th scope="col" class="py-3 px-3 text-center">Target Penilai</th>
                                            <th scope="col" class="py-3 px-4 text-center min-w-[140px]">Progres (%)</th>
                                            <th scope="col" class="py-3 px-4 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @forelse($assessorSummaries as $index => $asItem)
                                            @php
                                                $asObj = $asItem['assessor'];
                                                $asProg = $asItem['progress'];
                                            @endphp
                                            <tr class="hover:bg-slate-50/70 transition">
                                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-400">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="py-3 px-4 font-bold text-slate-900">
                                                    {{ $asObj->employee ? $asObj->employee->fullname : '-' }}
                                                </td>
                                                <td class="py-3 px-3 text-center">
                                                    @if($asObj->is_leader)
                                                        <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-blue-100 text-blue-800 border border-blue-200">
                                                            Ketua
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 text-[10px] font-semibold uppercase rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                            Penilai
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-3 font-mono text-slate-600">
                                                    {{ $asObj->employee ? $asObj->employee->nrp : '-' }}
                                                </td>
                                                <td class="py-3 px-4 text-slate-500">
                                                    {{ $asObj->employee ? $asObj->employee->dept . ' • ' . $asObj->employee->section : '-' }}
                                                </td>
                                                <td class="py-3 px-3 text-center font-mono font-bold text-blue-600">
                                                    {{ number_format($asProg['checked']) }}
                                                </td>
                                                <td class="py-3 px-3 text-center font-mono text-slate-500">
                                                    {{ number_format($asProg['target']) }}
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $asProg['percentage'] }}%"></div>
                                                        </div>
                                                        <span class="font-extrabold text-xs text-slate-800">{{ $asProg['percentage'] }}%</span>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full
                                                        @if($asProg['percentage'] >= 100) bg-emerald-50 text-emerald-700 border border-emerald-200
                                                        @elseif($asProg['percentage'] > 0) bg-amber-50 text-amber-700 border border-amber-200
                                                        @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                                        {{ $asProg['percentage'] >= 100 ? 'Selesai 100%' : ($asProg['percentage'] > 0 ? 'Sedang Berjalan' : 'Belum Mengisi') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="py-8 text-center text-slate-400">
                                                    Belum ada penilai yang terdaftar pada area ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                </div>
            @endif

            <!-- ============================================================= -->
            <!-- TAB 4: SUMMARY KELOMPOK (HASIL TAMPILAN DASHBOARD ADMIN)      -->
            <!-- ============================================================= -->
            @if($tab === 'group_summary' && $dashboardData)
                <div class="space-y-6">

                    <!-- 1. Header Banner Capaian & Action Bar -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                    Hasil Monitoring Dashboard Admin
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    ● Status Progres: <strong class="text-slate-800 dark:text-slate-200">{{ $dashboardData['adminEmployeeProgressStatus'] }}</strong>
                                </span>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                                Summary Kelompok & Hasil Evaluasi WOLA
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Ringkasan komprehensif capaian 12 kelompok kerja, daftar karyawan yang sudah dinilai, serta posisi jabatan target
                            </p>
                        </div>

                        <div class="flex items-center space-x-3 shrink-0">
                            @if(Auth::user()->hasRole(['superadmin', 'admin']))
                                <a href="{{ route('wola.reports.export.dashboard-excel') }}"
                                   class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5 cursor-pointer"
                                   title="Unduh file Excel lengkap dengan Sheet Dashboard (Pivot Rekapitulasi & 2 Diagram Batang Native)">
                                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Download Excel (Dashboard & Chart)</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- 2. VISUALISASI GRAFIK EVALUASI & CAPAIAN PROGRES (3 SEJAJAR SESUAI DASHBOARD ADMIN) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-6 items-stretch">
                        <!-- 1. Grafik Batang Capaian Karyawan Target Dinilai Antar Kelompok -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                                    <div class="flex items-start gap-2">
                                        <span class="w-3 h-3 rounded-full bg-blue-600 shadow-sm shrink-0 mt-1"></span>
                                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white leading-snug">
                                            Perbandingan Capaian Karyawan Dinilai Antar Kelompok
                                        </h3>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shrink-0 whitespace-nowrap self-start">
                                        {{ $dashboardData['adminEmployeeProgressPercentage'] }}% ({{ $dashboardData['adminAssessedEmployeesCount'] }}/{{ $dashboardData['adminTotalTargetEmployees'] }})
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Persentase dan jumlah karyawan target yang telah dinilai di seluruh area proyek WOLA MUSASHI
                                </p>
                            </div>

                            <div class="w-full relative mt-auto pt-4" style="height: 270px; min-height: 270px;">
                                <canvas id="reportGroupsProgressBarChart"></canvas>
                            </div>

                            <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs text-slate-500">
                                <span>Total Target: <strong>{{ number_format($dashboardData['adminTotalTargetEmployees']) }} Orang</strong></span>
                                <span class="font-bold text-blue-600 dark:text-blue-400">{{ $dashboardData['adminAssessedEmployeesCount'] }} Dinilai</span>
                            </div>
                        </div>

                        <!-- 2. Grafik Batang Total Job Dinilai Antar Kelompok -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                                    <div class="flex items-start gap-2">
                                        <span class="w-3 h-3 rounded-full bg-purple-600 shadow-sm shrink-0 mt-1"></span>
                                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white leading-snug">
                                            Perbandingan Capaian Posisi Job Dinilai Antar Kelompok
                                        </h3>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800 shrink-0 whitespace-nowrap self-start">
                                        {{ $dashboardData['adminJobPct'] }}% ({{ $dashboardData['adminAssessedJobsCount'] }}/{{ $dashboardData['adminTotalJobs'] }})
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Persentase Job target yang telah dinilai di seluruh area proyek WOLA MUSASHI
                                </p>
                            </div>

                            <div class="w-full relative mt-auto pt-4" style="height: 270px; min-height: 270px;">
                                <canvas id="reportGroupsJobsBarChart"></canvas>
                            </div>

                            <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs text-slate-500">
                                <span>Total Posisi: <strong>{{ number_format($dashboardData['adminTotalJobs']) }} Job</strong></span>
                                <span class="font-bold text-purple-600 dark:text-purple-400">{{ $dashboardData['adminAssessedJobsCount'] }} Dinilai</span>
                            </div>
                        </div>

                        <!-- 3. Grafik Donut Distribusi Status -->
                        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between md:col-span-2 xl:col-span-1">
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                                    <div class="flex items-start gap-2">
                                        <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm shrink-0 mt-1"></span>
                                        <h3 class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white leading-snug">
                                            Distribusi Status Kelompok
                                        </h3>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 shrink-0 whitespace-nowrap self-start">
                                        {{ count($dashboardData['allGroups']) }} Kelompok
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Sebaran status penyelesaian 12 kelompok kerja
                                </p>
                            </div>

                            <div class="w-full relative my-auto flex items-center justify-center pt-2" style="height: 240px; min-height: 240px;">
                                <canvas id="reportStatusDoughnutChart"></canvas>
                            </div>

                            <!-- Legend Custom -->
                            <div class="grid grid-cols-2 gap-2 pt-3 mt-2 border-t border-slate-100 dark:border-slate-700/60 text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Selesai ({{ $dashboardData['completedCount'] }})</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 shrink-0"></span>
                                    <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Berjalan ({{ $dashboardData['inProgressCount'] }})</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                                    <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Tertinggal ({{ $dashboardData['laggingCount'] }})</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 rounded-full bg-slate-400 shrink-0"></span>
                                    <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Belum Mulai ({{ $dashboardData['notStartedCount'] }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Tabel Rekapitulasi Capaian Seluruh 12 Kelompok Kerja -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                    Tabel Summary Rekapitulasi 12 Kelompok Kerja
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Hasil pemantauan capaian target dan realisasi checklist WOLA di seluruh area kerja PT Musashi
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                Total 12 Kelompok Terdaftar
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-900/70 font-bold text-slate-600 dark:text-slate-400">
                                    <tr>
                                        <th scope="col" class="py-3 px-4 w-12 text-center">No</th>
                                        <th scope="col" class="py-3 px-4">Nama Area Kelompok</th>
                                        <th scope="col" class="py-3 px-4">Ketua Kelompok</th>
                                        <th scope="col" class="py-3 px-3 text-center">Penilai</th>
                                        <th scope="col" class="py-3 px-3 text-center">Jabatan</th>
                                        <th scope="col" class="py-3 px-3 text-center">Target Karyawan</th>
                                        <th scope="col" class="py-3 px-3 text-center">Karyawan Dinilai</th>
                                        <th scope="col" class="py-3 px-4 text-center">Target Ceklis</th>
                                        <th scope="col" class="py-3 px-4 text-center">Realisasi Ceklis</th>
                                        <th scope="col" class="py-3 px-4 text-center">Capaian (%)</th>
                                        <th scope="col" class="py-3 px-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                    @foreach($dashboardData['groupsWithProgress'] as $groupItem)
                                        @php
                                            $prog = $groupItem->calculated_progress;
                                        @endphp
                                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition">
                                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                                {{ $groupItem->number }}
                                            </td>
                                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                                Kelompok {{ $groupItem->number }}: {{ $groupItem->name }}
                                            </td>
                                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                                {{ $groupItem->leader ? $groupItem->leader->name : 'Belum ditentukan' }}
                                            </td>
                                            <td class="py-3 px-3 text-center text-slate-700 dark:text-slate-300 font-semibold">
                                                {{ $prog['assessor_count'] }}
                                            </td>
                                            <td class="py-3 px-3 text-center text-slate-700 dark:text-slate-300 font-semibold">
                                                {{ $prog['job_count'] }}
                                            </td>
                                            <td class="py-3 px-3 text-center text-slate-700 dark:text-slate-300 font-semibold">
                                                {{ $prog['employee_count'] }}
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $prog['assessed_employees_count'] }}
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono text-slate-600 dark:text-slate-400">
                                                {{ number_format($prog['target']) }}
                                            </td>
                                            <td class="py-3 px-4 text-center font-mono font-bold text-blue-600 dark:text-blue-400">
                                                {{ number_format($prog['checked']) }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <div class="w-16 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $prog['percentage'] }}%"></div>
                                                    </div>
                                                    <span class="font-extrabold text-xs text-slate-800 dark:text-slate-200">{{ $prog['percentage'] }}%</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full
                                                    @if($prog['status_color'] === 'green') bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                                    @elseif($prog['status_color'] === 'yellow') bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                                    @elseif($prog['status_color'] === 'red') bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800
                                                    @else bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 @endif">
                                                    {{ $prog['status'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. TABEL RINCIAN KARYAWAN SUDAH DINILAI (LINTAS KELOMPOK) -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700"
                         x-data="{
                             selectedGroup: 'all',
                             search: '',
                             visibleCount: {{ $dashboardData['adminAssessedEmployeesList']->count() }},
                             matches(groupId, text) {
                                 const gOk = this.selectedGroup === 'all' || this.selectedGroup == groupId;
                                 const sOk = !this.search || text.toLowerCase().includes(this.search.toLowerCase().trim());
                                 return gOk && sOk;
                             },
                             updateNumbers() {
                                 this.$nextTick(() => {
                                     let counter = 1;
                                     const rows = this.$el.querySelectorAll('tbody tr.data-row-emp');
                                     rows.forEach(row => {
                                         if (row.style.display !== 'none') {
                                             const numEl = row.querySelector('.row-num-display-emp');
                                             if (numEl) numEl.textContent = counter;
                                             counter++;
                                         }
                                     });
                                     this.visibleCount = counter - 1;
                                 });
                             },
                             resetFilter() {
                                 this.selectedGroup = 'all';
                                 this.search = '';
                                 this.updateNumbers();
                             }
                         }"
                         x-init="updateNumbers()">

                        <!-- Header Tabel Karyawan -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                        Daftar Karyawan Sudah Dinilai (Hasil Dashboard)
                                    </h3>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Rincian seluruh karyawan target yang telah dievaluasi oleh tim penilai WOLA
                                </p>
                            </div>
                            <span class="px-3 py-1 self-start sm:self-center rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                                {{ $dashboardData['adminAssessedEmployeesList']->count() }} / {{ $dashboardData['adminTotalTargetEmployees'] }} Karyawan
                            </span>
                        </div>

                        <!-- Filter Bar Tabel Karyawan -->
                        <div class="mt-4 p-3.5 bg-slate-50/80 dark:bg-slate-900/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2.5 flex-1">
                                <div class="flex items-center space-x-2">
                                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300 whitespace-nowrap">Kelompok:</label>
                                    <select x-model="selectedGroup" @change="updateNumbers()"
                                            class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
                                        <option value="all">Semua Kelompok ({{ $dashboardData['adminAssessedEmployeesList']->count() }} Orang)</option>
                                        @foreach($dashboardData['allGroups'] as $g)
                                            @php
                                                $gEmpCount = $dashboardData['adminAssessedEmployeesList']->where('group_id', $g->id)->count();
                                            @endphp
                                            <option value="{{ $g->id }}">
                                                Kelompok {{ $g->number }}: {{ $g->name }} ({{ $gEmpCount }} dinilai)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="relative flex-1 min-w-[200px] max-w-sm">
                                    <input type="text" x-model="search" @input="updateNumbers()"
                                           placeholder="Cari NRP, nama karyawan, penilai..."
                                           class="w-full px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500">
                                </div>

                                <button type="button" x-show="selectedGroup !== 'all' || search !== ''" @click="resetFilter()"
                                        class="px-2.5 py-1.5 text-[11px] font-bold text-slate-500 hover:text-rose-600 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl transition cursor-pointer">
                                    Reset
                                </button>
                            </div>

                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1 self-end md:self-center shrink-0">
                                <span>Menampilkan:</span>
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-extrabold" x-text="visibleCount"></span>
                                <span>orang</span>
                            </div>
                        </div>

                        <!-- Isi Tabel Karyawan -->
                        <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl mt-4">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-900/70 font-bold text-slate-600 dark:text-slate-400">
                                    <tr>
                                        <th scope="col" class="py-3 px-3 text-center w-12">No.</th>
                                        <th scope="col" class="py-3 px-3.5">Kelompok</th>
                                        <th scope="col" class="py-3 px-3.5">Karyawan Sasaran</th>
                                        <th scope="col" class="py-3 px-3 text-center">Job Dinilai</th>
                                        <th scope="col" class="py-3 px-3">Penilai</th>
                                        <th scope="col" class="py-3 px-3 text-center">Total Ceklis</th>
                                        <th scope="col" class="py-3 px-3 text-center">Status Seluruh Penilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                    @forelse($dashboardData['adminAssessedEmployeesList'] as $index => $item)
                                        <tr class="data-row-emp hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition"
                                            x-show="matches('{{ $item->group_id }}', '{{ strtolower(addslashes($item->employee->nrp . ' ' . $item->employee->fullname . ' ' . $item->group_name . ' Kelompok ' . $item->group_number . ' ' . implode(' ', $item->distinct_assessors->all()))) }}')"
                                            style="display: table-row;">
                                            <td class="row-num-display-emp py-3 px-3 text-center font-mono font-bold text-slate-500 dark:text-slate-400">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="py-3 px-3.5 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 me-1.5">
                                                    Kelompok {{ $item->group_number }}
                                                </span>
                                                <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $item->group_name }}</span>
                                            </td>
                                            <td class="py-3 px-3.5">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-mono text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded text-[11px] border border-emerald-200 dark:border-emerald-800">
                                                        {{ $item->employee->nrp }}
                                                    </span>
                                                    <span class="font-bold text-slate-900 dark:text-white">{{ $item->employee->fullname }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-slate-200">
                                                {{ $item->assessed_jobs_count }} Job
                                            </td>
                                            <td class="py-3 px-3">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($item->distinct_assessors as $assName)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                            {{ $assName }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center whitespace-nowrap font-bold text-emerald-700 dark:text-emerald-400">
                                                {{ $item->total_checklists }} Ceklis
                                            </td>
                                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                                @if($item->is_full)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                                        <svg class="w-3 h-3 me-1 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Lengkap Semua Penilai
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        Sebagian Penilai ({{ $item->distinct_assessors->count() }}/{{ $item->group_assessors_count ?? $item->distinct_assessors->count() }})
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-xs text-slate-400">
                                                Belum ada karyawan yang dinilai.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 5. TABEL RINCIAN POSISI JOB SUDAH DINILAI (LINTAS KELOMPOK) -->
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700"
                         x-data="{
                             selectedGroup: 'all',
                             search: '',
                             visibleCount: {{ $dashboardData['adminAssessedJobsList']->count() }},
                             matches(groupId, text) {
                                 const gOk = this.selectedGroup === 'all' || this.selectedGroup == groupId;
                                 const sOk = !this.search || text.toLowerCase().includes(this.search.toLowerCase().trim());
                                 return gOk && sOk;
                             },
                             updateNumbers() {
                                 this.$nextTick(() => {
                                     let counter = 1;
                                     const rows = this.$el.querySelectorAll('tbody tr.data-row-job');
                                     rows.forEach(row => {
                                         if (row.style.display !== 'none') {
                                             const numEl = row.querySelector('.row-num-display-job');
                                             if (numEl) numEl.textContent = counter;
                                             counter++;
                                         }
                                     });
                                     this.visibleCount = counter - 1;
                                 });
                             },
                             resetFilter() {
                                 this.selectedGroup = 'all';
                                 this.search = '';
                                 this.updateNumbers();
                             }
                         }"
                         x-init="updateNumbers()">

                        <!-- Header Tabel Job -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                        Daftar Posisi Job Sudah Dinilai (Hasil Dashboard)
                                    </h3>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Rincian posisi jabatan di seluruh kelompok yang telah dievaluasi terhadap karyawan target
                                </p>
                            </div>
                            <span class="px-3 py-1 self-start sm:self-center rounded-xl text-xs font-black bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 whitespace-nowrap">
                                {{ $dashboardData['adminAssessedJobsList']->count() }} / {{ $dashboardData['adminTotalJobs'] }} Job
                            </span>
                        </div>

                        <!-- Filter Bar Tabel Job -->
                        <div class="mt-4 p-3.5 bg-slate-50/80 dark:bg-slate-900/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2.5 flex-1">
                                <div class="flex items-center space-x-2">
                                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300 whitespace-nowrap">Kelompok:</label>
                                    <select x-model="selectedGroup" @change="updateNumbers()"
                                            class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500">
                                        <option value="all">Semua Kelompok ({{ $dashboardData['adminAssessedJobsList']->count() }} Job)</option>
                                        @foreach($dashboardData['allGroups'] as $g)
                                            @php
                                                $gJobCount = $dashboardData['adminAssessedJobsList']->where('group_id', $g->id)->count();
                                            @endphp
                                            <option value="{{ $g->id }}">
                                                Kelompok {{ $g->number }}: {{ $g->name }} ({{ $gJobCount }} dinilai)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="relative flex-1 min-w-[200px] max-w-sm">
                                    <input type="text" x-model="search" @input="updateNumbers()"
                                           placeholder="Cari posisi jabatan, penilai..."
                                           class="w-full px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500">
                                </div>

                                <button type="button" x-show="selectedGroup !== 'all' || search !== ''" @click="resetFilter()"
                                        class="px-2.5 py-1.5 text-[11px] font-bold text-slate-500 hover:text-rose-600 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl transition cursor-pointer">
                                    Reset
                                </button>
                            </div>

                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1 self-end md:self-center shrink-0">
                                <span>Menampilkan:</span>
                                <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 font-extrabold" x-text="visibleCount"></span>
                                <span>posisi job</span>
                            </div>
                        </div>

                        <!-- Isi Tabel Job -->
                        <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl mt-4">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-900/70 font-bold text-slate-600 dark:text-slate-400">
                                    <tr>
                                        <th scope="col" class="py-3 px-3 text-center w-12">No.</th>
                                        <th scope="col" class="py-3 px-3.5">Kelompok</th>
                                        <th scope="col" class="py-3 px-3.5">Posisi Jabatan</th>
                                        <th scope="col" class="py-3 px-3 text-center">Karyawan Dinilai</th>
                                        <th scope="col" class="py-3 px-3">Penilai Aktif</th>
                                        <th scope="col" class="py-3 px-3 text-center">Total Ceklis</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                    @forelse($dashboardData['adminAssessedJobsList'] as $index => $item)
                                        <tr class="data-row-job hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition"
                                            x-show="matches('{{ $item->group_id }}', '{{ strtolower(addslashes($item->job->name . ' ' . $item->group_name . ' Kelompok ' . $item->group_number . ' ' . implode(' ', $item->distinct_assessors->all()))) }}')"
                                            style="display: table-row;">
                                            <td class="row-num-display-job py-3 px-3 text-center font-mono font-bold text-slate-500 dark:text-slate-400">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="py-3 px-3.5 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 me-1.5">
                                                    Kelompok {{ $item->group_number }}
                                                </span>
                                                <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $item->group_name }}</span>
                                            </td>
                                            <td class="py-3 px-3.5 font-bold text-slate-900 dark:text-white">
                                                {{ $item->job->name }}
                                            </td>
                                            <td class="py-3 px-3 text-center font-bold text-purple-700 dark:text-purple-400">
                                                {{ $item->assessed_employees_count }} / {{ $item->target_count }} ({{ $item->percentage }}%)
                                            </td>
                                            <td class="py-3 px-3">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($item->distinct_assessors as $assName)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-50 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                            {{ $assName }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center whitespace-nowrap font-bold text-purple-700 dark:text-purple-400">
                                                {{ $item->total_checklists }} Ceklis
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-8 text-center text-xs text-slate-400">
                                                Belum ada posisi jabatan yang dinilai.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            @endif

        </div>
    </div>

    @if($tab === 'group_summary' && $dashboardData)
    <!-- Script Inisialisasi Chart.js untuk Tab Summary Kelompok -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.Chart === 'undefined') return;

            const chartLabelsFull = {!! json_encode($dashboardData['chartLabels']) !!};
            const chartLabelsShort = {!! json_encode($dashboardData['chartLabelsShort']) !!};

            // Data Karyawan
            const empDataArr = {!! json_encode($dashboardData['chartData']) !!};
            const empColorsArr = {!! json_encode($dashboardData['chartColors']) !!};
            const empAssessedArr = {!! json_encode($dashboardData['chartEmpAssessed']) !!};
            const empTotalArr = {!! json_encode($dashboardData['chartEmpTotal']) !!};

            // Data Posisi Job
            const jobDataArr = {!! json_encode($dashboardData['chartJobData']) !!};
            const jobColorsArr = {!! json_encode($dashboardData['chartJobColors']) !!};
            const jobAssessedArr = {!! json_encode($dashboardData['chartJobAssessed']) !!};
            const jobTotalArr = {!! json_encode($dashboardData['chartJobTotal']) !!};

            const isDark = document.documentElement.classList.contains('dark');

            // 1. Grafik Batang Capaian Karyawan Dinilai Antar Kelompok
            const barCanvas = document.getElementById('reportGroupsProgressBarChart');
            if (barCanvas) {
                const barCtx = barCanvas.getContext('2d');
                new window.Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: chartLabelsShort,
                        datasets: [{
                            label: 'Capaian Karyawan (%)',
                            data: empDataArr,
                            backgroundColor: empColorsArr,
                            borderRadius: 6,
                            borderSkipped: false,
                            minBarLength: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 24, bottom: 0, left: 0, right: 4 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: function(items) { return chartLabelsFull[items[0].dataIndex] || items[0].label; },
                                    label: function(context) {
                                        const idx = context.dataIndex;
                                        return `Karyawan Dinilai: ${context.parsed.y}% (${empAssessedArr[idx]} dari ${empTotalArr[idx]} orang)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                min: 0,
                                max: 100,
                                ticks: {
                                    stepSize: 20,
                                    callback: function(v) { return v + '%'; },
                                    font: { size: 10, weight: '600' },
                                    color: isDark ? '#94A3B8' : '#64748B',
                                    padding: 6
                                },
                                grid: { color: isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.2)' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10, weight: 'bold' }, color: isDark ? '#CBD5E1' : '#475569', padding: 6 }
                            }
                        }
                    },
                    plugins: [{
                        id: 'reportEmpBarTopLabels',
                        afterDatasetsDraw(chart) {
                            const { ctx } = chart;
                            const isDarkMode = document.documentElement.classList.contains('dark');
                            chart.data.datasets.forEach((dataset, i) => {
                                const meta = chart.getDatasetMeta(i);
                                meta.data.forEach((bar, index) => {
                                    const pct = dataset.data[index];
                                    const assessed = empAssessedArr[index];
                                    const total = empTotalArr[index];
                                    if (assessed > 0) {
                                        const text = `${pct}% (${assessed}/${total})`;
                                        ctx.save();
                                        ctx.font = 'bold 9.5px Figtree, sans-serif';
                                        ctx.fillStyle = isDarkMode ? '#93C5FD' : '#1E40AF';
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'bottom';
                                        ctx.fillText(text, bar.x, Math.max(bar.y - 4, 14));
                                        ctx.restore();
                                    }
                                });
                            });
                        }
                    }]
                });
            }

            // 2. Grafik Batang Capaian Posisi Job Dinilai Antar Kelompok
            const jobCanvas = document.getElementById('reportGroupsJobsBarChart');
            if (jobCanvas) {
                const jobBarCtx = jobCanvas.getContext('2d');
                new window.Chart(jobBarCtx, {
                    type: 'bar',
                    data: {
                        labels: chartLabelsShort,
                        datasets: [{
                            label: 'Posisi Job Dinilai (%)',
                            data: jobDataArr,
                            backgroundColor: jobColorsArr,
                            borderRadius: 6,
                            borderSkipped: false,
                            minBarLength: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 24, bottom: 0, left: 0, right: 4 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    title: function(items) { return chartLabelsFull[items[0].dataIndex] || items[0].label; },
                                    label: function(context) {
                                        const idx = context.dataIndex;
                                        return `Posisi Job Dinilai: ${context.parsed.y}% (${jobAssessedArr[idx]} dari ${jobTotalArr[idx]} posisi jabatan)`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                min: 0,
                                max: 100,
                                ticks: {
                                    stepSize: 20,
                                    callback: function(v) { return v + '%'; },
                                    font: { size: 10, weight: '600' },
                                    color: isDark ? '#94A3B8' : '#64748B',
                                    padding: 6
                                },
                                grid: { color: isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.2)' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10, weight: 'bold' }, color: isDark ? '#CBD5E1' : '#475569', padding: 6 }
                            }
                        }
                    },
                    plugins: [{
                        id: 'reportJobBarTopLabels',
                        afterDatasetsDraw(chart) {
                            const { ctx } = chart;
                            const isDarkMode = document.documentElement.classList.contains('dark');
                            chart.data.datasets.forEach((dataset, i) => {
                                const meta = chart.getDatasetMeta(i);
                                meta.data.forEach((bar, index) => {
                                    const pct = dataset.data[index];
                                    const assessed = jobAssessedArr[index];
                                    const total = jobTotalArr[index];
                                    if (assessed > 0) {
                                        const text = `${pct}% (${assessed}/${total})`;
                                        ctx.save();
                                        ctx.font = 'bold 9.5px Figtree, sans-serif';
                                        ctx.fillStyle = isDarkMode ? '#DDD6FE' : '#6B21A8';
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'bottom';
                                        ctx.fillText(text, bar.x, Math.max(bar.y - 4, 14));
                                        ctx.restore();
                                    }
                                });
                            });
                        }
                    }]
                });
            }

            // 3. Grafik Donut Distribusi Status Kelompok
            const doughnutCanvas = document.getElementById('reportStatusDoughnutChart');
            if (doughnutCanvas) {
                const doughnutCtx = doughnutCanvas.getContext('2d');
                new window.Chart(doughnutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Selesai', 'Berjalan', 'Tertinggal', 'Belum Mulai'],
                        datasets: [{
                            data: [
                                {{ $dashboardData['completedCount'] }},
                                {{ $dashboardData['inProgressCount'] }},
                                {{ $dashboardData['laggingCount'] }},
                                {{ $dashboardData['notStartedCount'] }}
                            ],
                            backgroundColor: ['#10B981', '#3B82F6', '#F43F5E', '#94A3B8'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    </script>
    @endif

    @if(Auth::user()->hasRole('superadmin'))
        <!-- Modal Konfirmasi Hapus Data Penilaian (Khusus Superadmin) -->
        <div id="deleteAssessmentsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden" role="dialog" aria-modal="true">
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="p-6 bg-rose-50 dark:bg-rose-950/40 border-b border-rose-100 dark:border-rose-900/50 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-600/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">
                                Hapus Data Checklist Penilaian
                            </h3>
                            <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-rose-200 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300">
                                Superadmin
                            </span>
                        </div>
                        <p class="text-xs text-rose-700 dark:text-rose-300 mt-1">
                            Pembersihan riwayat checklist & catatan evaluasi penilaian WOLA
                        </p>
                    </div>
                    <button type="button" onclick="closeDeleteAssessmentsModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('wola.reports.assessments.destroy-all') }}" class="p-6 space-y-5">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="detail_group_id" value="{{ $detailGroupId }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 25) }}">

                    <!-- Ringkasan Data Saat Ini -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-slate-700/80 space-y-2 text-xs">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                            <span>Total Checklist di Database:</span>
                            <span class="font-bold font-mono text-slate-900 dark:text-white text-sm">
                                {{ $totalAllAssessments ?? \App\Models\Assessment::count() }} data
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-300">
                            <span>Filter Kelompok Saat Ini:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">
                                {{ ($detailGroupId === 'all' || empty($detailGroupId)) ? '🌐 Semua Kelompok (Area 1-12)' : (optional($allGroups->firstWhere('id', $detailGroupId))->name ?? 'Kelompok ' . $detailGroupId) }}
                            </span>
                        </div>
                    </div>

                    <!-- Pilihan Cakupan Penghapusan -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Pilih Cakupan Data yang Dihapus:</label>
                        <div class="space-y-2">
                            @if($detailGroupId !== 'all' && !empty($detailGroupId))
                                <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/40 cursor-pointer transition">
                                    <input type="radio" name="scope" value="group" checked class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">
                                            Hanya Kelompok Ini ({{ optional($allGroups->firstWhere('id', $detailGroupId))->name ?? 'Kelompok ' . $detailGroupId }})
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Hanya menghapus data checklist penilaian pada kelompok yang sedang difilter.
                                        </div>
                                    </div>
                                </label>
                            @endif

                            <label class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/40 dark:bg-rose-950/20 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer transition">
                                <input type="radio" name="scope" value="all" {{ ($detailGroupId === 'all' || empty($detailGroupId)) ? 'checked' : '' }} class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                <div>
                                    <div class="text-xs font-bold text-rose-900 dark:text-rose-200">
                                        Seluruh Kelompok (Semua Area 1-12)
                                    </div>
                                    <div class="text-[11px] text-rose-700/80 dark:text-rose-400">
                                        Mengosongkan seluruh checklist penilaian dan catatan evaluasi di sistem.
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Peringatan Keamanan -->
                    <div class="p-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl flex items-start gap-2.5 text-amber-800 dark:text-amber-300 text-xs">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span><strong>Perhatian:</strong> Tindakan ini permanen. Perubahan ini juga otomatis disinkronkan ke Google Spreadsheet jika integrasi aktif.</span>
                    </div>

                    <!-- Checkbox Konfirmasi -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="confirmDeleteAssessmentsCheck" onchange="toggleDeleteAssessmentsSubmit(this.checked)"
                               class="rounded text-rose-600 focus:ring-rose-500 border-slate-300 dark:border-slate-600">
                        <label for="confirmDeleteAssessmentsCheck" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                            Saya yakin ingin menghapus data checklist penilaian ini
                        </label>
                    </div>

                    <!-- Tombol Aksi Modal -->
                    <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-end gap-2">
                        <button type="button" onclick="closeDeleteAssessmentsModal()"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitDeleteAssessments" disabled
                                class="px-5 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-extrabold text-xs rounded-xl shadow-md shadow-rose-600/30 transition opacity-50 cursor-not-allowed flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Ya, Hapus Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openDeleteAssessmentsModal() {
                const m = document.getElementById('deleteAssessmentsModal');
                if (m) m.classList.remove('hidden');
            }
            function closeDeleteAssessmentsModal() {
                const m = document.getElementById('deleteAssessmentsModal');
                if (m) m.classList.add('hidden');
            }
            function toggleDeleteAssessmentsSubmit(checked) {
                const btn = document.getElementById('btnSubmitDeleteAssessments');
                if (!btn) return;
                btn.disabled = !checked;
                if (checked) {
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        </script>
    @endif
</x-app-layout>
