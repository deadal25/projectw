<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Dashboard WOLA - Administrator') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Monitoring & Evaluasi Beban Kerja WOLA MUSASHI
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wider
                    @if(auth()->user()->hasRole('superadmin')) bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300
                    @else bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 @endif">
                    Role: {{ auth()->user()->getRoleNames()->first() }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6 sm:space-y-8">

            <!-- ============================================================= -->
            <!-- 1. KARTU PROGRES UTAMA PEMANTAUAN (12 KELOMPOK KERJA) -->
            <!-- ============================================================= -->
            <div class="bg-white dark:bg-slate-800 p-5 sm:p-7 2xl:p-8 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Capaian Progres Monitoring</span>
                            <span class="px-3 py-0.5 text-xs font-bold rounded-full
                                @if($adminEmployeeStatusColor === 'green') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300
                                @elseif($adminEmployeeStatusColor === 'blue') bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300
                                @elseif($adminEmployeeStatusColor === 'red') bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300
                                @else bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 @endif">
                                ● {{ $adminEmployeeProgressStatus }}
                            </span>
                        </div>
                        <h3 class="text-3xl sm:text-4xl 2xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $adminEmployeeProgressPercentage }}%
                            <span class="text-base sm:text-lg font-bold text-slate-400 font-normal">selesai</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Sebanyak <strong>{{ number_format($adminAssessedEmployeesCount) }}</strong> dari total target <strong>{{ number_format($adminTotalTargetEmployees) }}</strong> karyawan target telah diverifikasi (Seluruh Kelompok).
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                        @if(Auth::user()->hasRole('superadmin'))
                        <a href="{{ route('wola.google-sheets.index') }}"
                           class="inline-flex items-center px-3.5 sm:px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 dark:text-emerald-300 font-bold text-xs rounded-xl border border-emerald-200 dark:border-emerald-800 transition"
                           title="Integrasi & Sinkronisasi Google Spreadsheet">
                            <svg class="w-4 h-4 me-1.5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Google Sheets Sync</span>
                        </a>
                        @endif

                        <a href="{{ route('wola.groups.index') }}"
                           class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition">
                            <svg class="w-4 h-4 me-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Daftar Kelompok</span>
                        </a>

                        <a href="{{ route('wola.reports.index') }}"
                           class="inline-flex items-center px-5 sm:px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                            <span>Pusat Laporan & Matriks</span>
                            <svg class="w-4 h-4 ms-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Progress Bar Besar & 3 Faktor Target (X 12) -->
                <div class="mt-6 space-y-4">
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3.5 overflow-hidden">
                        <div class="bg-blue-600 h-3.5 rounded-full transition-all duration-700 ease-out"
                             style="width: {{ $adminEmployeeProgressPercentage }}%"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 pt-2">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tim Penilai</span>
                                <h4 class="text-base sm:text-lg font-black text-slate-800 dark:text-white mt-0.5">{{ $adminTotalAssessors }} Orang</h4>
                            </div>
                            <span class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jabatan Diukur</span>
                                <h4 class="text-base sm:text-lg font-black text-slate-800 dark:text-white mt-0.5">
                                    {{ $adminTotalJobs }} <span class="text-xs text-slate-400 font-semibold">Posisi</span>
                                </h4>
                            </div>
                            <span class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 flex items-center justify-between sm:col-span-2 md:col-span-1">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan Target</span>
                                <h4 class="text-base sm:text-lg font-black text-slate-800 dark:text-white mt-0.5">{{ $adminTotalTargetEmployees }} Orang</h4>
                            </div>
                            <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- RINGKASAN HASIL EVALUASI BEBAN KERJA (4 INDIKATOR HASIL X 12) -->
            <!-- ============================================================= -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- 1. Karyawan Sudah Dinilai (Klik untuk langsung menuju tabel rincian) -->
                <a href="#tabel-karyawan-dinilai-admin" 
                   onclick="document.getElementById('tabel-karyawan-dinilai-admin')?.scrollIntoView({ behavior: 'smooth' });"
                   class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md hover:border-emerald-400 dark:hover:border-emerald-500 transition group cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shadow-xs group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <div class="flex items-center space-x-1.5">
                                <span class="px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 tracking-tight">
                                    {{ $adminEmployeeProgressPercentage }}% Dinilai
                                </span>
                                <span class="text-slate-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 group-hover:translate-y-0.5 transition" title="Lihat tabel rincian">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            Karyawan Sudah Dinilai &darr;
                        </span>
                        <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ $adminAssessedEmployeesCount }} <span class="text-xs text-slate-400 font-semibold">/ {{ $adminTotalTargetEmployees }} Orang</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $adminEmployeeProgressPercentage }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                            <span>Sudah dievaluasi penilai</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center space-x-1">
                                <span>{{ $adminAssessedEmployeesCount }} Dinilai</span>
                            </span>
                        </p>
                    </div>
                </a>

                <!-- 2. Total Job (Klik untuk langsung menuju tabel rincian) -->
                <a href="#tabel-job-dinilai-admin" 
                   onclick="document.getElementById('tabel-job-dinilai-admin')?.scrollIntoView({ behavior: 'smooth' });"
                   class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md hover:border-purple-400 dark:hover:border-purple-500 transition group cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shadow-xs group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <div class="flex items-center space-x-1.5">
                                <span class="px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 tracking-tight">
                                    {{ $adminJobPct }}% Dinilai
                                </span>
                                <span class="text-slate-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 group-hover:translate-y-0.5 transition" title="Lihat tabel rincian">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                            Total Job &darr;
                        </span>
                        <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ $adminAssessedJobsCount }} <span class="text-xs text-slate-400 font-semibold">/ {{ $adminTotalJobs }} Posisi</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-purple-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $adminJobPct }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                            <span>Posisi jabatan telah dinilai</span>
                            <span class="font-bold text-purple-600 dark:text-purple-400 flex items-center space-x-1">
                                <span>{{ $adminAssessedJobsCount }} dari {{ $adminTotalJobs }} Job</span>
                            </span>
                        </p>
                    </div>
                </a>

                <!-- 3. Total Verifikasi Ceklis -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </span>
                            <span class="px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 tracking-tight">
                                {{ $adminChecklistPercentage }}% Selesai
                            </span>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Total Verifikasi Ceklis</span>
                        <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ number_format($adminVerifiedChecklists) }} <span class="text-xs text-slate-400 font-semibold">/ {{ number_format($adminTargetChecklists) }} Checklist</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $adminChecklistPercentage }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                            <span>Akumulasi verifikasi penilai seluruh kelompok</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ number_format($adminVerifiedChecklists) }} dari {{ number_format($adminTargetChecklists) }} Checklist</span>
                        </p>
                    </div>
                </div>

                <!-- 4. Lengkap 3 Penilai -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </span>
                            <span class="px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 tracking-tight">
                                {{ $adminFullAssessedPercentage }}% Lengkap
                            </span>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Lengkap 3 Penilai</span>
                        <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ $adminFullAssessedEmployeesCount }} <span class="text-xs text-slate-400 font-semibold">/ {{ $adminTotalTargetEmployees }} Orang</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $adminFullAssessedPercentage }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                            <span>Lengkap dari 3 penilai</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $adminFullAssessedEmployeesCount }} Selesai</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2. VISUALISASI GRAFIK EVALUASI & CAPAIAN PROGRES (3 SEJAJAR / RESPONSIVE) -->
            <!-- ============================================================= -->
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
                                {{ $adminEmployeeProgressPercentage }}% ({{ $adminAssessedEmployeesCount }}/{{ $adminTotalTargetEmployees }})
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Persentase dan jumlah karyawan target yang telah dinilai di seluruh area proyek WOLA MUSASHI
                        </p>
                    </div>

                    <!-- Canvas grafik pas menempel di atas batas total target -->
                    <div class="w-full relative mt-auto pt-4" style="height: 270px; min-height: 270px;">
                        <canvas id="groupsProgressBarChart"></canvas>
                    </div>

                    <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs text-slate-500">
                        <span>Total Target: <strong>{{ number_format($adminTotalTargetEmployees) }} Orang</strong></span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $adminAssessedEmployeesCount }} Dinilai</span>
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
                                {{ $adminJobPct }}% ({{ $adminAssessedJobsCount }}/{{ $adminTotalJobs }})
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Persentase Job target yang telah dinilai di seluruh area proyek WOLA MUSASHI
                        </p>
                    </div>

                    <!-- Canvas grafik pas menempel di atas batas total posisi -->
                    <div class="w-full relative mt-auto pt-4" style="height: 270px; min-height: 270px;">
                        <canvas id="groupsJobsBarChart"></canvas>
                    </div>

                    <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-xs text-slate-500">
                        <span>Total Posisi: <strong>{{ number_format($adminTotalJobs) }} Job</strong></span>
                        <span class="font-bold text-purple-600 dark:text-purple-400">{{ $adminAssessedJobsCount }} Dinilai</span>
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
                                {{ $allGroups->count() }} Kelompok
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Sebaran status penyelesaian 12 kelompok kerja
                        </p>
                    </div>

                    <div class="w-full relative my-auto flex items-center justify-center pt-2" style="height: 240px; min-height: 240px;">
                        <canvas id="statusDoughnutChart"></canvas>
                    </div>

                    <!-- Legend Custom -->
                    <div class="grid grid-cols-2 gap-2 pt-3 mt-2 border-t border-slate-100 dark:border-slate-700/60 text-xs">
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Selesai ({{ $completedCount }})</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500 shrink-0"></span>
                            <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Berjalan ({{ $inProgressCount }})</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                            <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Tertinggal ({{ $laggingCount }})</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full bg-slate-400 shrink-0"></span>
                            <span class="text-slate-600 dark:text-slate-300 font-medium truncate">Belum Mulai ({{ $notStartedCount }})</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2.5 TABEL RINCIAN EVALUASI (STACKED: KARYAWAN & POSISI JOB)   -->
            <!-- ============================================================= -->
            <div class="space-y-6">

                <!-- ========================================================= -->
                <!-- TABEL 1: DAFTAR KARYAWAN SUDAH DINILAI (LINTAS KELOMPOK) -->
                <!-- ========================================================= -->
                <div id="tabel-karyawan-dinilai-admin" 
                     class="scroll-mt-24 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700"
                     x-data="{
                         selectedGroup: 'all',
                         search: '',
                         visibleCount: {{ $adminAssessedEmployeesList->count() }},
                         matches(groupId, text) {
                             const gOk = this.selectedGroup === 'all' || this.selectedGroup == groupId;
                             const sOk = !this.search || text.toLowerCase().includes(this.search.toLowerCase().trim());
                             return gOk && sOk;
                         },
                         updateNumbers() {
                             this.$nextTick(() => {
                                 let counter = 1;
                                 const rows = this.$el.querySelectorAll('tbody tr.data-row');
                                 rows.forEach(row => {
                                     if (row.style.display !== 'none') {
                                         const numEl = row.querySelector('.row-num-display');
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

                    <!-- Header Tabel 1 -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                    Daftar Karyawan Sudah Dinilai
                                </h3>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Karyawan sasaran dari seluruh kelompok yang telah dievaluasi oleh satu atau lebih penilai.
                            </p>
                        </div>
                        <span class="px-3 py-1 self-start sm:self-center rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                            {{ $adminAssessedEmployeesList->count() }} / {{ $adminTotalTargetEmployees }} Karyawan Dinilai
                        </span>
                    </div>

                    <!-- Filter Bar Tabel 1 -->
                    <div class="mt-4 p-3.5 bg-slate-50/80 dark:bg-slate-900/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2.5 flex-1">
                            <!-- Filter Kelompok -->
                            <div class="flex items-center space-x-2">
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-300 whitespace-nowrap flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    Kelompok:
                                </label>
                                <select x-model="selectedGroup" 
                                        @change="updateNumbers()"
                                        class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 shadow-2xs">
                                    <option value="all">Semua Kelompok ({{ $adminAssessedEmployeesList->count() }} Orang)</option>
                                    @foreach($allGroups as $g)
                                        @php
                                            $gEmpCount = $adminAssessedEmployeesList->where('group_id', $g->id)->count();
                                        @endphp
                                        <option value="{{ $g->id }}">
                                            Kelompok {{ $g->number }}: {{ $g->name }} ({{ $gEmpCount }} dinilai)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search Input -->
                            <div class="relative flex-1 min-w-[200px] max-w-sm">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       x-model="search" 
                                       @input="updateNumbers()"
                                       placeholder="Cari NRP, nama karyawan, penilai..." 
                                       class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-emerald-500 shadow-2xs">
                            </div>

                            <!-- Tombol Reset Filter -->
                            <button type="button" 
                                    x-show="selectedGroup !== 'all' || search !== ''"
                                    @click="resetFilter()"
                                    class="px-2.5 py-1.5 text-[11px] font-bold text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl transition cursor-pointer flex items-center gap-1 shadow-2xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reset
                            </button>
                        </div>

                        <!-- Info Counter Live -->
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1 self-end md:self-center shrink-0">
                            <span>Menampilkan:</span>
                            <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-extrabold" x-text="visibleCount"></span>
                            <span>dari {{ $adminAssessedEmployeesList->count() }} orang</span>
                        </div>
                    </div>

                    <!-- Isi Tabel 1 -->
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl mt-4">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="py-3 px-3 text-center w-12">No.</th>
                                    <th scope="col" class="py-3 px-3.5">Kelompok</th>
                                    <th scope="col" class="py-3 px-3.5">Karyawan Sasaran</th>
                                    <th scope="col" class="py-3 px-3 text-center">Job Dinilai</th>
                                    <th scope="col" class="py-3 px-3">Penilai</th>
                                    <th scope="col" class="py-3 px-3 text-center">Total Ceklis</th>
                                    <th scope="col" class="py-3 px-3 text-center">Status Lengkap</th>
                                    <th scope="col" class="py-3 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                @forelse($adminAssessedEmployeesList as $index => $item)
                                    <tr class="data-row hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition"
                                        x-show="matches('{{ $item->group_id }}', '{{ strtolower(addslashes($item->employee->nrp . ' ' . $item->employee->fullname . ' ' . $item->group_name . ' Kelompok ' . $item->group_number . ' ' . implode(' ', $item->distinct_assessors->all()))) }}')"
                                        style="display: table-row;">
                                        
                                        <!-- Kolom No. Urut (Dihitung Dinamis untuk Admin) -->
                                        <td class="row-num-display py-3 px-3 text-center align-middle font-mono font-bold text-slate-500 dark:text-slate-400 text-xs">
                                            {{ $index + 1 }}
                                        </td>

                                        <!-- Kolom Nama Kelompok Berapa -->
                                        <td class="py-3 px-3.5 align-middle whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shrink-0">
                                                    Kelompok {{ $item->group_number }}
                                                </span>
                                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[150px]" title="{{ $item->group_name }}">
                                                    {{ $item->group_name }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Kolom Karyawan Sasaran (Maksimal 2 Baris) -->
                                        <td class="py-3 px-3.5 align-middle">
                                            <div class="flex items-center space-x-2.5">
                                                <span class="font-mono text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded text-[11px] border border-emerald-200 dark:border-emerald-800 whitespace-nowrap shrink-0">
                                                    {{ $item->employee->nrp }}
                                                </span>
                                                <div class="font-bold text-slate-900 dark:text-white leading-tight">
                                                    @php
                                                        $words = array_values(array_filter(explode(' ', trim($item->employee->fullname))));
                                                        $line1 = '';
                                                        $line2 = '';

                                                        if (count($words) <= 1) {
                                                            $line1 = $item->employee->fullname;
                                                        } elseif (count($words) == 2) {
                                                            if (strlen($item->employee->fullname) > 12) {
                                                                $line1 = $words[0];
                                                                $line2 = $words[1];
                                                            } else {
                                                                $line1 = $item->employee->fullname;
                                                            }
                                                        } else {
                                                            $bestSplit = 1;
                                                            $minMaxLen = PHP_INT_MAX;

                                                            for ($i = 1; $i < count($words); $i++) {
                                                                $l1 = implode(' ', array_slice($words, 0, $i));
                                                                $l2 = implode(' ', array_slice($words, $i));
                                                                $maxLen = max(strlen($l1), strlen($l2));
                                                                if ($maxLen < $minMaxLen) {
                                                                    $minMaxLen = $maxLen;
                                                                    $bestSplit = $i;
                                                                }
                                                            }

                                                            $line1 = implode(' ', array_slice($words, 0, $bestSplit));
                                                            $line2 = implode(' ', array_slice($words, $bestSplit));
                                                        }
                                                    @endphp
                                                    <div class="whitespace-nowrap">{{ $line1 }}</div>
                                                    @if(!empty($line2))
                                                        <div class="whitespace-nowrap text-slate-800 dark:text-slate-200">{{ $line2 }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kolom Job Dinilai -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                            <div class="inline-flex flex-col items-center">
                                                <span class="font-bold text-slate-900 dark:text-white text-xs">
                                                    {{ $item->assessed_jobs_count }} <span class="text-slate-400 font-normal">/ {{ $item->group_jobs_count }} Job</span>
                                                </span>
                                                <div class="w-16 bg-slate-100 dark:bg-slate-700 rounded-full h-1 mt-1 overflow-hidden">
                                                    <div class="bg-emerald-500 h-1 rounded-full" style="width: {{ $item->group_jobs_count > 0 ? round(($item->assessed_jobs_count / $item->group_jobs_count) * 100) : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kolom Penilai -->
                                        <td class="py-3 px-3 align-middle">
                                            <div class="flex flex-wrap gap-1 max-w-[180px]">
                                                @foreach($item->distinct_assessors as $assName)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80">
                                                        {{ $assName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>

                                        <!-- Kolom Total Ceklis (Bisa diklik, muncul Popup Rincian Nama Job) -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap" x-data="{ openModal: false }">
                                            <button type="button" 
                                                    @click="openModal = true"
                                                    class="inline-flex items-center space-x-1.5 px-2.5 py-1 text-xs font-black rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 dark:hover:bg-emerald-900 border border-emerald-300 dark:border-emerald-800 shadow-2xs transition hover:scale-105 cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                                    title="Klik untuk melihat nama-nama job yang dinilai">
                                                <span>{{ $item->total_checklists }} Ceklis</span>
                                                <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>

                                            <!-- Popup Modal Rincian Nama Job Dinilai -->
                                            <div x-show="openModal" 
                                                 x-cloak
                                                 style="display: none;"
                                                 @keydown.escape.window="openModal = false"
                                                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                                                
                                                <!-- Backdrop click to close -->
                                                <div class="fixed inset-0" @click="openModal = false"></div>

                                                <!-- Popup Dialog Box -->
                                                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md overflow-hidden text-left z-10 transition-all"
                                                     @click.stop>
                                                    
                                                    <!-- Header Popup -->
                                                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/80 dark:bg-slate-900/40">
                                                        <div class="flex items-center space-x-2.5">
                                                            <span class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                                </svg>
                                                            </span>
                                                            <div>
                                                                <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">
                                                                    Daftar Job Dinilai
                                                                </h4>
                                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                                                    Kelompok {{ $item->group_number }} &bull; {{ $item->employee->nrp }} &bull; {{ $item->employee->fullname }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <button type="button" 
                                                                @click="openModal = false"
                                                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                                                                title="Tutup">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <!-- List Nama-nama Job (Tampil 5 dan bisa discroll) -->
                                                    <div class="p-3 max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60">
                                                        @forelse($item->distinct_jobs as $jIdx => $dj)
                                                            <div class="py-2.5 px-2 flex items-start justify-between space-x-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 rounded-xl transition">
                                                                <div class="flex items-start space-x-2.5">
                                                                    <span class="text-slate-400 font-mono text-[11px] pt-0.5">{{ $jIdx + 1 }}.</span>
                                                                    <div>
                                                                        <div>
                                                                            <span class="font-bold text-xs text-slate-900 dark:text-white">
                                                                                {{ $dj->name }}
                                                                            </span>
                                                                        </div>
                                                                        @if(count($dj->assessors) > 0)
                                                                            <div class="text-[10px] text-slate-400 mt-1 flex items-center space-x-1">
                                                                                <span>Penilai:</span>
                                                                                <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ implode(', ', $dj->assessors->all()) }}</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                                                    {{ $dj->checklists_count }} Ceklis
                                                                </span>
                                                            </div>
                                                        @empty
                                                            <div class="py-6 text-center text-xs text-slate-400">
                                                                Belum ada rincian job yang dinilai.
                                                            </div>
                                                        @endforelse
                                                    </div>

                                                    <!-- Footer Popup -->
                                                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs">
                                                        <span class="text-slate-500 dark:text-slate-400 font-medium text-[11px]">
                                                            Total <strong class="text-slate-800 dark:text-slate-200">{{ count($item->distinct_jobs) }} Posisi Job</strong> ({{ $item->total_checklists }} Ceklis)
                                                        </span>
                                                        <button type="button" 
                                                                @click="openModal = false"
                                                                class="px-3.5 py-1 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-lg text-xs transition cursor-pointer">
                                                            Tutup
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kolom Status Lengkap Penilai -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
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

                                        <!-- Kolom Aksi -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                            <a href="{{ route('wola.groups.assessments.index', $item->group_id) }}" 
                                               class="inline-flex items-center space-x-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition"
                                               title="Buka form penilaian kelompok {{ $item->group_number }}">
                                                <span>Buka</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-xs text-slate-400">
                                            <div class="flex flex-col items-center justify-center space-y-1">
                                                <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                <span>Belum ada karyawan yang dinilai oleh penilai di seluruh kelompok.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                                <!-- Row Kosong Jika Filter Tidak Cocok -->
                                <tr x-show="visibleCount === 0" style="display: none;">
                                    <td colspan="8" class="py-8 text-center text-xs text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-1">
                                            <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <span>Tidak ada karyawan yang sesuai dengan kriteria filter atau pencarian saat ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- TABEL 2: DAFTAR POSISI JOB SUDAH DINILAI (LINTAS KELOMPOK) -->
                <!-- ========================================================= -->
                <div id="tabel-job-dinilai-admin" 
                     class="scroll-mt-24 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700"
                     x-data="{
                         selectedGroup: 'all',
                         search: '',
                         visibleCount: {{ $adminAssessedJobsList->count() }},
                         matches(groupId, text) {
                             const gOk = this.selectedGroup === 'all' || this.selectedGroup == groupId;
                             const sOk = !this.search || text.toLowerCase().includes(this.search.toLowerCase().trim());
                             return gOk && sOk;
                         },
                         updateNumbers() {
                             this.$nextTick(() => {
                                 let counter = 1;
                                 const rows = this.$el.querySelectorAll('tbody tr.data-row');
                                 rows.forEach(row => {
                                     if (row.style.display !== 'none') {
                                         const numEl = row.querySelector('.row-num-display');
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

                    <!-- Header Tabel 2 -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                    Daftar Posisi Job Sudah Dinilai
                                </h3>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Posisi jabatan dari seluruh kelompok yang telah dievaluasi terhadap karyawan sasaran.
                            </p>
                        </div>
                        <span class="px-3 py-1 self-start sm:self-center rounded-xl text-xs font-black bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 whitespace-nowrap">
                            {{ $adminAssessedJobsList->count() }} / {{ $adminTotalJobs }} Job Dinilai
                        </span>
                    </div>

                    <!-- Filter Bar Tabel 2 -->
                    <div class="mt-4 p-3.5 bg-slate-50/80 dark:bg-slate-900/50 rounded-xl border border-slate-200/80 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2.5 flex-1">
                            <!-- Filter Kelompok -->
                            <div class="flex items-center space-x-2">
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-300 whitespace-nowrap flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    Kelompok:
                                </label>
                                <select x-model="selectedGroup" 
                                        @change="updateNumbers()"
                                        class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-500 shadow-2xs">
                                    <option value="all">Semua Kelompok ({{ $adminAssessedJobsList->count() }} Job)</option>
                                    @foreach($allGroups as $g)
                                        @php
                                            $gJobCount = $adminAssessedJobsList->where('group_id', $g->id)->count();
                                        @endphp
                                        <option value="{{ $g->id }}">
                                            Kelompok {{ $g->number }}: {{ $g->name }} ({{ $gJobCount }} dinilai)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search Input -->
                            <div class="relative flex-1 min-w-[200px] max-w-sm">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       x-model="search" 
                                       @input="updateNumbers()"
                                       placeholder="Cari posisi jabatan, penilai..." 
                                       class="w-full pl-8 pr-3 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 shadow-2xs">
                            </div>

                            <!-- Tombol Reset Filter -->
                            <button type="button" 
                                    x-show="selectedGroup !== 'all' || search !== ''"
                                    @click="resetFilter()"
                                    class="px-2.5 py-1.5 text-[11px] font-bold text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl transition cursor-pointer flex items-center gap-1 shadow-2xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reset
                            </button>
                        </div>

                        <!-- Info Counter Live -->
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 flex items-center gap-1 self-end md:self-center shrink-0">
                            <span>Menampilkan:</span>
                            <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 font-extrabold" x-text="visibleCount"></span>
                            <span>dari {{ $adminAssessedJobsList->count() }} job</span>
                        </div>
                    </div>

                    <!-- Isi Tabel 2 -->
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl mt-4">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="py-3 px-3 text-center w-12">No.</th>
                                    <th scope="col" class="py-3 px-3.5">Kelompok</th>
                                    <th scope="col" class="py-3 px-3.5">Posisi Jabatan</th>
                                    <th scope="col" class="py-3 px-3 text-center">Karyawan Dinilai</th>
                                    <th scope="col" class="py-3 px-3">Penilai Aktif</th>
                                    <th scope="col" class="py-3 px-3 text-center">Total Ceklis</th>
                                    <th scope="col" class="py-3 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                @forelse($adminAssessedJobsList as $index => $item)
                                    <tr class="data-row hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition"
                                        x-show="matches('{{ $item->group_id }}', '{{ strtolower(addslashes($item->job->name . ' ' . $item->group_name . ' Kelompok ' . $item->group_number . ' ' . implode(' ', $item->distinct_assessors->all()))) }}')"
                                        style="display: table-row;">
                                        
                                        <!-- Kolom No. Urut (Dihitung Dinamis untuk Admin) -->
                                        <td class="row-num-display py-3 px-3 text-center align-middle font-mono font-bold text-slate-500 dark:text-slate-400 text-xs">
                                            {{ $index + 1 }}
                                        </td>

                                        <!-- Kolom Nama Kelompok Berapa -->
                                        <td class="py-3 px-3.5 align-middle whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shrink-0">
                                                    Kelompok {{ $item->group_number }}
                                                </span>
                                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[150px]" title="{{ $item->group_name }}">
                                                    {{ $item->group_name }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Kolom Posisi Jabatan (Hanya Nama Jabatan Saja) -->
                                        <td class="py-3 px-3.5 align-middle">
                                            <span class="font-bold text-slate-900 dark:text-white leading-snug">
                                                {{ $item->job->name }}
                                            </span>
                                        </td>

                                        <!-- Kolom Karyawan Dinilai -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                            <div class="inline-flex flex-col items-center">
                                                <span class="font-bold text-slate-900 dark:text-white text-xs">
                                                    {{ $item->assessed_employees_count }} <span class="text-slate-400 font-normal">/ {{ $item->target_count }} Org</span>
                                                    <span class="text-purple-600 dark:text-purple-400 font-black text-[11px] ml-1">({{ $item->percentage }}%)</span>
                                                </span>
                                                <div class="w-16 bg-slate-100 dark:bg-slate-700 rounded-full h-1 mt-1 overflow-hidden">
                                                    <div class="bg-purple-600 h-1 rounded-full" style="width: {{ min(100, $item->percentage) }}%"></div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kolom Penilai Aktif -->
                                        <td class="py-3 px-3 align-middle">
                                            <div class="flex flex-wrap gap-1 max-w-[180px]">
                                                @foreach($item->distinct_assessors as $assName)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-50 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800/80">
                                                        {{ $assName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>

                                        <!-- Kolom Total Ceklis (Bisa diklik, muncul Popup Rincian Karyawan Dinilai) -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap" x-data="{ openModal: false }">
                                            <button type="button" 
                                                    @click="openModal = true"
                                                    class="inline-flex items-center space-x-1.5 px-2.5 py-1 text-xs font-black rounded-lg bg-purple-50 hover:bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 dark:hover:bg-purple-900 border border-purple-300 dark:border-purple-800 shadow-2xs transition hover:scale-105 cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500"
                                                    title="Klik untuk melihat daftar karyawan yang dinilai">
                                                <span>{{ $item->total_checklists }} Ceklis</span>
                                                <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>

                                            <!-- Popup Modal Rincian Karyawan Dinilai -->
                                            <div x-show="openModal" 
                                                 x-cloak
                                                 style="display: none;"
                                                 @keydown.escape.window="openModal = false"
                                                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                                                
                                                <!-- Backdrop click to close -->
                                                <div class="fixed inset-0" @click="openModal = false"></div>

                                                <!-- Popup Dialog Box -->
                                                <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md overflow-hidden text-left z-10 transition-all"
                                                     @click.stop>
                                                    
                                                    <!-- Header Popup -->
                                                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/80 dark:bg-slate-900/40">
                                                        <div class="flex items-center space-x-2.5">
                                                            <span class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-400 flex items-center justify-center font-bold">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                                </svg>
                                                            </span>
                                                            <div>
                                                                <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">
                                                                    Daftar Karyawan Dinilai
                                                                </h4>
                                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                                                    Kelompok {{ $item->group_number }} &bull; Posisi: {{ $item->job->name }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <button type="button" 
                                                                @click="openModal = false"
                                                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                                                                title="Tutup">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <!-- List Nama-nama Karyawan (Tampil 5 dan bisa discroll) -->
                                                    <div class="p-3 max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60">
                                                        @forelse($item->distinct_employees as $eIdx => $de)
                                                            <div class="py-2.5 px-2 flex items-start justify-between space-x-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 rounded-xl transition">
                                                                <div class="flex items-start space-x-2.5">
                                                                    <span class="text-slate-400 font-mono text-[11px] pt-0.5">{{ $eIdx + 1 }}.</span>
                                                                    <div>
                                                                        <div class="flex items-center space-x-1.5 flex-wrap">
                                                                            <span class="font-mono text-purple-700 dark:text-purple-400 font-bold bg-purple-50 dark:bg-purple-950/60 px-1.5 py-0.5 rounded text-[10px] border border-purple-200 dark:border-purple-800">
                                                                                {{ $de->nrp }}
                                                                            </span>
                                                                            <span class="font-bold text-xs text-slate-900 dark:text-white">
                                                                                {{ $de->fullname }}
                                                                            </span>
                                                                        </div>
                                                                        @if(count($de->assessors) > 0)
                                                                            <div class="text-[10px] text-slate-400 mt-1 flex items-center space-x-1">
                                                                                <span>Penilai:</span>
                                                                                <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ implode(', ', $de->assessors->all()) }}</span>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                                                    {{ $de->checklists_count }} Ceklis
                                                                </span>
                                                            </div>
                                                        @empty
                                                            <div class="py-6 text-center text-xs text-slate-400">
                                                                Belum ada rincian karyawan yang dinilai.
                                                            </div>
                                                        @endforelse
                                                    </div>

                                                    <!-- Footer Popup -->
                                                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs">
                                                        <span class="text-slate-500 dark:text-slate-400 font-medium text-[11px]">
                                                            Total <strong class="text-slate-800 dark:text-slate-200">{{ count($item->distinct_employees) }} Karyawan</strong> ({{ $item->total_checklists }} Ceklis)
                                                        </span>
                                                        <button type="button" 
                                                                @click="openModal = false"
                                                                class="px-3.5 py-1 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold rounded-lg text-xs transition cursor-pointer">
                                                            Tutup
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Kolom Aksi -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                            <a href="{{ route('wola.groups.assessments.index', $item->group_id) }}" 
                                               class="inline-flex items-center space-x-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-purple-600 hover:bg-purple-700 text-white shadow-xs transition"
                                               title="Buka form penilaian kelompok {{ $item->group_number }}">
                                                <span>Buka</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-xs text-slate-400">
                                            <div class="flex flex-col items-center justify-center space-y-1">
                                                <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Belum ada posisi jabatan yang dinilai oleh penilai di seluruh kelompok.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                                <!-- Row Kosong Jika Filter Tidak Cocok -->
                                <tr x-show="visibleCount === 0" style="display: none;">
                                    <td colspan="7" class="py-8 text-center text-xs text-slate-400">
                                        <div class="flex flex-col items-center justify-center space-y-1">
                                            <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <span>Tidak ada posisi jabatan yang sesuai dengan kriteria filter atau pencarian saat ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>            <!-- ============================================================= -->
            <!-- 3. FILTER, SORTIR & PENCARIAN GRID KELOMPOK -->
            <!-- ============================================================= -->
            <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                <form method="GET" action="{{ route('wola.admin.dashboard') }}" class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                    <!-- Pencarian -->
                    <div class="w-full xl:w-72">
                        <input type="text" name="search" value="{{ $search }}"
                                placeholder="Cari nama area atau ketua..."
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filter Status Pills -->
                        <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900 rounded-xl text-xs font-bold overflow-x-auto max-w-full">
                            <a href="{{ route('wola.admin.dashboard', ['status' => 'all', 'search' => $search, 'sort' => $sortBy]) }}"
                               class="px-2.5 py-1 rounded-lg transition whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-sm' : 'text-slate-500' }}">Semua</a>
                            <a href="{{ route('wola.admin.dashboard', ['status' => 'completed', 'search' => $search, 'sort' => $sortBy]) }}"
                               class="px-2.5 py-1 rounded-lg transition whitespace-nowrap {{ $statusFilter === 'completed' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-sm' : 'text-slate-500' }}">Selesai</a>
                            <a href="{{ route('wola.admin.dashboard', ['status' => 'in_progress', 'search' => $search, 'sort' => $sortBy]) }}"
                               class="px-2.5 py-1 rounded-lg transition whitespace-nowrap {{ $statusFilter === 'in_progress' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-sm' : 'text-slate-500' }}">Berjalan</a>
                            <a href="{{ route('wola.admin.dashboard', ['status' => 'lagging', 'search' => $search, 'sort' => $sortBy]) }}"
                               class="px-2.5 py-1 rounded-lg transition whitespace-nowrap {{ $statusFilter === 'lagging' ? 'bg-white dark:bg-slate-800 text-rose-600 shadow-sm' : 'text-slate-500' }}">Tertinggal</a>
                        </div>

                        <!-- Dropdown Sortir -->
                        <select name="sort" onchange="this.form.submit()"
                                class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-blue-500">
                            <option value="number_asc" {{ $sortBy === 'number_asc' ? 'selected' : '' }}>Nomor Kelompok (1 - 12)</option>
                            <option value="progress_desc" {{ $sortBy === 'progress_desc' ? 'selected' : '' }}>Progres Tertinggi → Terendah</option>
                            <option value="progress_asc" {{ $sortBy === 'progress_asc' ? 'selected' : '' }}>Progres Terendah → Tertinggi</option>
                            <option value="name_asc" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>Nama Area (A - Z)</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- ============================================================= -->
            <!-- 4. GRID 12+ KARTU KELOMPOK KERJA -->
            <!-- ============================================================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-5 sm:gap-6">
                @forelse($filteredGroups as $group)
                    @php
                        $prog = $group->calculated_progress;
                        $leader = $group->leader ? $group->leader->name : 'Belum ditetapkan';
                    @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-blue-500 dark:hover:border-blue-500 transition-all p-6 flex flex-col justify-between">
                        <div>
                            <!-- Header Kartu -->
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    Kelompok {{ $group->number }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($prog['status_color'] === 'green') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300
                                    @elseif($prog['status_color'] === 'yellow') bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300
                                    @elseif($prog['status_color'] === 'red') bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300
                                    @else bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 @endif">
                                    {{ $prog['status'] }}
                                </span>
                            </div>

                            <h3 class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight line-clamp-1" title="{{ $group->name }}">
                                {{ $group->name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Ketua: <strong class="text-slate-700 dark:text-slate-300">{{ $leader }}</strong>
                            </p>

                            <!-- Breakdown 3 Elemen Target -->
                            <div class="grid grid-cols-3 gap-2 py-3 my-3 border-y border-slate-100 dark:border-slate-700/60 text-center">
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-2 rounded-xl">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Penilai</span>
                                    <span class="text-sm font-extrabold text-slate-800 dark:text-white">{{ $prog['assessor_count'] }}</span>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-2 rounded-xl">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Jabatan</span>
                                    <span class="text-sm font-extrabold text-slate-800 dark:text-white">{{ $prog['job_count'] }}</span>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-2 rounded-xl">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Karyawan</span>
                                    <span class="text-sm font-extrabold text-slate-800 dark:text-white">{{ $prog['employee_count'] }}</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-500 font-medium">Karyawan Sasaran Dinilai</span>
                                    <span class="font-extrabold text-blue-600 dark:text-blue-400">{{ $prog['percentage'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $prog['percentage'] }}%"></div>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-400 mt-1.5">
                                    <span>Target: {{ $prog['employee_count'] }} Karyawan</span>
                                    <span>{{ $prog['assessed_employees_count'] }}/{{ $prog['employee_count'] }} selesai</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi Bawah -->
                        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <a href="{{ route('wola.groups.show', $group) }}"
                               class="text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-600 transition">
                                Kelola Komponen
                            </a>

                            <a href="{{ route('wola.groups.assessments.index', $group) }}"
                               class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-sm transition">
                                <span>Buka Penilaian</span>
                                <svg class="w-3.5 h-3.5 ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 py-12 text-center text-slate-400 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-8">
                        Tidak ada kelompok yang sesuai dengan kriteria filter saat ini.
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <!-- Script Inisialisasi Chart.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.Chart === 'undefined') {
                console.error('Chart.js belum dimuat.');
                return;
            }

            const chartLabelsFull = {!! json_encode($chartLabels) !!};
            const chartLabelsShort = {!! json_encode($chartLabelsShort) !!};

            // Data Karyawan
            const empDataArr = {!! json_encode($chartData) !!};
            const empColorsArr = {!! json_encode($chartColors) !!};
            const empAssessedArr = {!! json_encode($chartEmpAssessed) !!};
            const empTotalArr = {!! json_encode($chartEmpTotal) !!};

            // Data Posisi Job
            const jobDataArr = {!! json_encode($chartJobData) !!};
            const jobColorsArr = {!! json_encode($chartJobColors) !!};
            const jobAssessedArr = {!! json_encode($chartJobAssessed) !!};
            const jobTotalArr = {!! json_encode($chartJobTotal) !!};

            const isDark = document.documentElement.classList.contains('dark');

            // 1. Grafik Batang Capaian Karyawan Target Dinilai
            const empBarCtx = document.getElementById('groupsProgressBarChart').getContext('2d');
            new window.Chart(empBarCtx, {
                type: 'bar',
                data: {
                    labels: chartLabelsShort,
                    datasets: [{
                        label: 'Karyawan Dinilai (%)',
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
                    layout: {
                        padding: {
                            top: 24,
                            bottom: 0,
                            left: 0,
                            right: 4
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(items) {
                                    return chartLabelsFull[items[0].dataIndex] || items[0].label;
                                },
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const pct = context.parsed.y;
                                    const assessed = empAssessedArr[idx];
                                    const total = empTotalArr[idx];
                                    return `Karyawan Dinilai: ${pct}% (${assessed} dari ${total} orang target)`;
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
                                callback: function(value) { return value + '%'; },
                                font: { size: 10, weight: '600' },
                                color: isDark ? '#94A3B8' : '#64748B',
                                padding: 6
                            },
                            grid: {
                                color: function(context) {
                                    if (context.tick.value === 0) {
                                        return isDark ? 'rgba(148, 163, 184, 0.4)' : 'rgba(100, 116, 139, 0.35)';
                                    }
                                    return isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.2)';
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 0,
                                minRotation: 0,
                                font: { size: 10, weight: 'bold' },
                                color: isDark ? '#CBD5E1' : '#475569',
                                padding: 6
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'empBarTopLabels',
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
                                    ctx.fillStyle = isDarkMode ? '#F1F5F9' : '#1E293B';
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

            // 2. Grafik Batang Capaian Posisi Job Dinilai
            const jobBarCtx = document.getElementById('groupsJobsBarChart').getContext('2d');
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
                    layout: {
                        padding: {
                            top: 24,
                            bottom: 0,
                            left: 0,
                            right: 4
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: function(items) {
                                    return chartLabelsFull[items[0].dataIndex] || items[0].label;
                                },
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const pct = context.parsed.y;
                                    const assessed = jobAssessedArr[idx];
                                    const total = jobTotalArr[idx];
                                    return `Posisi Job Dinilai: ${pct}% (${assessed} dari ${total} posisi jabatan)`;
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
                                callback: function(value) { return value + '%'; },
                                font: { size: 10, weight: '600' },
                                color: isDark ? '#94A3B8' : '#64748B',
                                padding: 6
                            },
                            grid: {
                                color: function(context) {
                                    if (context.tick.value === 0) {
                                        return isDark ? 'rgba(148, 163, 184, 0.4)' : 'rgba(100, 116, 139, 0.35)';
                                    }
                                    return isDark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(148, 163, 184, 0.2)';
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 0,
                                minRotation: 0,
                                font: { size: 10, weight: 'bold' },
                                color: isDark ? '#CBD5E1' : '#475569',
                                padding: 6
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'jobBarTopLabels',
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

            // 3. Grafik Donut Distribusi Status
            const doughnutCtx = document.getElementById('statusDoughnutChart').getContext('2d');
            new window.Chart(doughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Selesai', 'Berjalan', 'Tertinggal', 'Belum Mulai'],
                    datasets: [{
                        data: [{{ $completedCount }}, {{ $inProgressCount }}, {{ $laggingCount }}, {{ $notStartedCount }}],
                        backgroundColor: ['#10B981', '#3B82F6', '#F43F5E', '#94A3B8'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
</x-app-layout>
