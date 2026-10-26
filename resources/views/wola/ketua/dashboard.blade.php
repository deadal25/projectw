<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div>
                    <h2 class="font-extrabold text-2xl text-slate-900 dark:text-white leading-tight">
                        {{ $group->name }}
                    </h2>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Dashboard Evaluasi Beban Kerja (WOLA)
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if(!$isKetuaOnly && $allGroups->count() > 1)
                    <!-- Group Switcher Dropdown (Khusus Administrator) -->
                    <form method="GET" action="{{ route('wola.ketua.dashboard') }}" class="flex items-center space-x-2 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-xl border border-slate-200 dark:border-slate-700">
                        <label for="group_id" class="text-xs font-bold text-slate-600 dark:text-slate-300 ps-1">Pilih Area:</label>
                        <select name="group_id" id="group_id" onchange="this.form.submit()"
                                class="text-xs py-1 px-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                            @foreach($allGroups as $g)
                                <option value="{{ $g->id }}" {{ $g->id == $group->id ? 'selected' : '' }}>
                                    Kelompok {{ $g->number }}: {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <div class="px-3 py-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Ketua Kelompok:</span>
                    <strong class="text-xs font-bold text-slate-800 dark:text-slate-200">
                        {{ $group->leader ? $group->leader->name : 'Belum ditentukan' }}
                    </strong>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        showAddAssessorModal: false,
        showEditAssessorModal: false,
        editAssessorData: {
            id: null,
            fullname: '',
            nrp: '',
            dept: '',
            plant: '',
            updateUrl: ''
        },
        openEditAssessor(id, name, nrp, dept, plant, url) {
            this.editAssessorData = {
                id: id,
                fullname: name,
                nrp: nrp,
                dept: dept,
                plant: plant,
                updateUrl: url
            };
            this.showEditAssessorModal = true;
        }
    }">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6 sm:space-y-8">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">✕</button>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 dark:text-rose-400">✕</button>
                </div>
            @endif

            @if(!$isKetuaOnly && $allGroups->count() > 1)
                <!-- 0. BAR SELEKTOR CEPAT KELOMPOK (KHUSUS ADMIN) -->
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm border border-slate-200/90 dark:border-slate-700/80">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3.5">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800/60 shadow-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                        Mode Administrator: Pilih Area Kelompok Kerja
                                    </span>
                                    <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                        Admin Quick Switch
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    Pilih nomor kelompok kerja di bawah untuk beralih area pemantauan secara instan
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-700 text-xs shrink-0 self-start sm:self-center">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            <span class="text-slate-500 dark:text-slate-400">Area Terpilih:</span>
                            <strong class="text-blue-600 dark:text-blue-400 font-extrabold">{{ $group->name }}</strong>
                        </div>
                    </div>

                    <!-- Tombol Pill Kelompok (1 sampai N) -->
                    <div class="p-1.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2 overflow-x-auto p-1.5 scrollbar-thin">
                            @foreach($allGroups as $g)
                                @php
                                    $isActive = ($g->id == $group->id);
                                @endphp
                                <a href="{{ route('wola.ketua.dashboard', ['group_id' => $g->id]) }}"
                                   title="Kelompok {{ $g->number }}: {{ $g->name }}"
                                   class="min-w-[46px] h-9 px-3 rounded-lg text-xs font-black whitespace-nowrap transition-all duration-150 flex items-center justify-center space-x-1 shrink-0
                                   {{ $isActive 
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/25 ring-2 ring-blue-500/40 dark:ring-blue-400/50' 
                                        : 'bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 hover:border-blue-300 dark:hover:border-blue-500 hover:text-blue-600 shadow-2xs' }}">
                                    <span class="text-[10px] font-bold opacity-75 {{ $isActive ? 'text-blue-100' : 'text-slate-400 dark:text-slate-500' }}">K-</span>
                                    <span>{{ $g->number }}</span>
                                    @if($isActive)
                                        <svg class="w-3.5 h-3.5 text-white ms-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- ============================================================= -->
            <!-- 1. KARTU PROGRES UTAMA KELOMPOK -->
            <!-- ============================================================= -->
            <div class="bg-white dark:bg-slate-800 p-5 sm:p-7 2xl:p-8 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Capaian Progres Area</span>
                            <span class="px-3 py-0.5 text-xs font-bold rounded-full
                                @if($employeeStatusColor === 'green') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300
                                @elseif($employeeStatusColor === 'blue') bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300
                                @elseif($employeeStatusColor === 'red') bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300
                                @else bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 @endif">
                                ● {{ $employeeProgressStatus }}
                            </span>
                        </div>
                        <h3 class="text-3xl sm:text-4xl 2xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $employeeProgressPercentage }}%
                            <span class="text-base sm:text-lg font-bold text-slate-400 font-normal">selesai</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Sebanyak <strong>{{ number_format($assessedEmployeesCount) }}</strong> dari total target <strong>{{ number_format($totalTargetEmployees) }}</strong> karyawan target telah diverifikasi.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('wola.groups.show', $group) }}"
                           class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition">
                            <svg class="w-4 h-4 me-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Daftar Anggota Tim</span>
                        </a>

                        <a href="{{ route('wola.groups.assessments.index', $group) }}"
                           class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                            <span>Mulai / Lanjutkan Penilaian</span>
                            <svg class="w-4 h-4 ms-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Progress Bar Besar & 3 Faktor Target -->
                <div class="mt-6 space-y-4">
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3.5 overflow-hidden">
                        <div class="bg-blue-600 h-3.5 rounded-full transition-all duration-700 ease-out"
                             style="width: {{ $employeeProgressPercentage }}%"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 pt-2">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tim Penilai</span>
                                <h4 class="text-lg font-black text-slate-800 dark:text-white mt-0.5">{{ $totalAssessors }} Orang</h4>
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
                                <h4 class="text-lg font-black text-slate-800 dark:text-white mt-0.5">
                                    {{ $jobs->count() }} <span class="text-xs text-slate-400 font-semibold">Posisi Jabatan</span>
                                </h4>
                            </div>
                            <span class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Karyawan Target</span>
                                <h4 class="text-lg font-black text-slate-800 dark:text-white mt-0.5">{{ $totalTargetEmployees }} Orang</h4>
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
            <!-- RINGKASAN HASIL EVALUASI BEBAN KERJA (4 INDIKATOR HASIL) -->
            <!-- ============================================================= -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- 1. Karyawan Sudah Dinilai (Klik untuk langsung menuju tabel rincian) -->
                <a href="#tabel-karyawan-dinilai" 
                   onclick="document.getElementById('tabel-karyawan-dinilai')?.scrollIntoView({ behavior: 'smooth' });"
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
                                    {{ $employeeProgressPercentage }}% Dinilai
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
                            {{ $assessedEmployeesCount }} <span class="text-xs text-slate-400 font-semibold">/ {{ $totalTargetEmployees }} Orang</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $employeeProgressPercentage }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                            <span>Sudah dievaluasi penilai</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center space-x-1">
                                <span>{{ $assessedEmployeesCount }} Dinilai</span>
                            </span>
                        </p>
                    </div>
                </a>

                <!-- 2. Total Job (Klik untuk langsung menuju tabel rincian) -->
                <a href="#tabel-job-dinilai" 
                   onclick="document.getElementById('tabel-job-dinilai')?.scrollIntoView({ behavior: 'smooth' });"
                   class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md hover:border-purple-400 dark:hover:border-purple-500 transition group cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold shadow-xs group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            @php
                                $jobPct = $jobs->count() > 0 ? round(($assessedJobsCount / $jobs->count()) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center space-x-1.5">
                                <span class="px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 tracking-tight">
                                    {{ $jobPct }}% Dinilai
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
                            {{ $assessedJobsCount }} <span class="text-xs text-slate-400 font-semibold">/ {{ $jobs->count() }} Posisi</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-purple-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $jobPct }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                            <span>Posisi jabatan telah dinilai</span>
                            <span class="font-bold text-purple-600 dark:text-purple-400 flex items-center space-x-1">
                                <span>{{ $assessedJobsCount }} dari {{ $jobs->count() }} Job</span>
                            </span>
                        </p>
                    </div>
                </a>

                <!-- 3. Total Verifikasi Ceklis (Akumulasi 3 Penilai sama seperti di wola/groups/1) -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                            </span>
                            <span class="px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 tracking-tight">
                                {{ $groupProgress['percentage'] }}% Selesai
                            </span>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Total Verifikasi Ceklis</span>
                        <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ number_format($totalVerifiedAssessments) }} <span class="text-xs text-slate-400 font-semibold">/ {{ number_format($groupProgress['target']) }} Checklist</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $groupProgress['percentage'] }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between" title="Rincian Penilai: {{ $assessorsBreakdown }}">
                            <span>Akumulasi {{ $totalAssessors }} penilai</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalVerifiedAssessments) }} dari {{ number_format($groupProgress['target']) }} Checklist</span>
                        </p>
                    </div>
                </div>

                <!-- 4. Karyawan Lengkap Ceklis (Seluruh Penilai) -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700 flex flex-col justify-between hover:shadow-md transition group">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </span>
                            <span class="px-3.5 py-1.5 rounded-xl text-sm sm:text-base font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 tracking-tight">
                                {{ $fullAssessedPercentage }}% Lengkap
                            </span>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3">Lengkap {{ $totalAssessors }} Penilai</span>
                        <h4 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">
                            {{ $fullAssessedEmployeesCount }} <span class="text-xs text-slate-400 font-semibold">/ {{ $totalTargetEmployees }} Orang</span>
                        </h4>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $fullAssessedPercentage }}%"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 flex items-center justify-between">
                            <span>Lengkap dari {{ $totalAssessors }} penilai</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $fullAssessedEmployeesCount }} Selesai</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2. TIM PENILAI & PROGRES INDIVIDUAL -->
            <!-- ============================================================= -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 dark:text-white">Progres Penilai Mandiri</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Kelola tim penilai kelompok Anda dan pantau kontribusi pengisian dari masing-masing anggota penilai</p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="button"
                                @click="showAddAssessorModal = true"
                                class="inline-flex items-center px-3.5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer">
                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Penilai
                        </button>
                        <span class="text-xs font-bold text-slate-400">{{ $assessors->count() }} Penilai Terdaftar</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                    @forelse($assessors as $assessor)
                        @php
                            $assessorProgress = $assessor->getProgressData();
                        @endphp
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 flex flex-col justify-between hover:border-blue-400 dark:hover:border-blue-500 transition">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400 font-semibold">
                                        NRP: {{ $assessor->employee?->nrp ?? '-' }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        @if($assessor->is_leader)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                                <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 01.894.553l1.618 3.279 3.619.526a1 1 0 01.554 1.705l-2.618 2.552.618 3.604a1 1 0 01-1.451 1.054L10 13.567l-3.236 1.702a1 1 0 01-1.451-1.054l.618-3.604-2.618-2.552a1 1 0 01.554-1.705l3.619-.526L9.106 2.553A1 1 0 0110 2z" clip-rule="evenodd"/></svg>
                                                Ketua
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-semibold uppercase rounded-full bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                                Penilai
                                            </span>
                                        @endif

                                        @if(!$assessor->is_leader)
                                            <!-- Tombol Edit Penilai -->
                                            <button type="button"
                                                    @click="openEditAssessor({{ $assessor->id }}, '{{ addslashes($assessor->employee?->fullname ?? '') }}', '{{ addslashes($assessor->employee?->nrp ?? '') }}', '{{ addslashes($assessor->employee?->dept ?? '') }}', '{{ addslashes($assessor->employee?->plant ?? '') }}', '{{ route('wola.groups.assessors.update', [$group, $assessor]) }}')"
                                                    class="p-1 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-slate-200/70 dark:hover:bg-slate-700 transition"
                                                    title="Edit Data Penilai">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>

                                            <!-- Tombol Hapus Penilai -->
                                            <form method="POST" action="{{ route('wola.groups.assessors.remove', [$group, $assessor]) }}"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus penilai {{ $assessor->employee?->fullname }} dari kelompok ini?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                                                        title="Hapus Penilai">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm line-clamp-1" title="{{ $assessor->employee?->fullname ?? '-' }}">
                                    {{ $assessor->employee?->fullname ?? '-' }}
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ $assessor->employee?->dept ?? $group->name }} • {{ $assessor->employee?->plant ?? 'CIKARANG' }}
                                </p>
                                @if($assessor->is_leader)
                                    <p class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold mt-1">
                                        Akun Utama Ketua (Dikelola di Manajemen Akun)
                                    </p>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800">
                                <div class="flex justify-between items-center text-xs mb-1.5">
                                    <span class="text-slate-500">Penyelesaian</span>
                                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ $assessorProgress['percentage'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ $assessorProgress['percentage'] }}%"></div>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-400 mt-1.5">
                                    <span>Target: {{ $assessorProgress['target'] }}</span>
                                    <span>{{ $assessorProgress['checked'] }} terisi</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 py-6 text-center text-xs text-slate-400">
                            Belum ada penilai yang ditambahkan ke kelompok ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2.B DAFTAR RINCIAN PENILAIAN (DUA TABEL SEJAJAR: KARYAWAN 55% & JOB 45%) -->
            <!-- ============================================================= -->
            <div class="grid grid-cols-1 xl:grid-cols-[55fr_45fr] gap-6 items-start">

                <!-- TABEL 1: DAFTAR KARYAWAN SUDAH DINILAI -->
                <div id="tabel-karyawan-dinilai" class="scroll-mt-24 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <!-- Header Tabel 1 -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                    Daftar Karyawan Sudah Dinilai
                                </h3>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Karyawan sasaran yang telah dievaluasi oleh satu atau lebih penilai.
                            </p>
                        </div>
                        <span class="px-3 py-1 self-start sm:self-center rounded-xl text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                            {{ $assessedEmployeesList->count() }} / {{ $totalTargetEmployees }} Orang
                        </span>
                    </div>

                    <!-- Isi Tabel 1 -->
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl mt-4">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="py-3 px-3.5">Karyawan Sasaran</th>
                                    <th scope="col" class="py-3 px-3 text-center">Job Dinilai</th>
                                    <th scope="col" class="py-3 px-3">Penilai</th>
                                    <th scope="col" class="py-3 px-3 text-center">Total Ceklis</th>
                                    <th scope="col" class="py-3 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                @forelse($assessedEmployeesList as $index => $item)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                        <!-- Kolom Karyawan Sasaran (Tanpa Nomor Urut, Maksimal 2 Baris) -->
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
                                                            // Cari titik potong terbaik agar seimbang dan pas maksimal 2 baris (contoh: NUGROHO EDY S -> NUGROHO / EDY S)
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

                                        <!-- Kolom Job Dinilai (Tanpa kode J01, J18) -->
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                            <div class="inline-flex flex-col items-center">
                                                <span class="font-bold text-slate-900 dark:text-white text-xs">
                                                    {{ $item->assessed_jobs_count }} <span class="text-slate-400 font-normal">/ {{ $jobs->count() }} Job</span>
                                                </span>
                                                <div class="w-16 bg-slate-100 dark:bg-slate-700 rounded-full h-1 mt-1 overflow-hidden">
                                                    <div class="bg-emerald-500 h-1 rounded-full" style="width: {{ $jobs->count() > 0 ? round(($item->assessed_jobs_count / $jobs->count()) * 100) : 0 }}%"></div>
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

                                                <!-- Popup Dialog Box (Ukuran pas max-w-md sesuai card) -->
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
                                                                    {{ $item->employee->nrp }} &bull; {{ $item->employee->fullname }}
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
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                            <a href="{{ route('wola.groups.assessments.index', $group) }}" 
                                               class="inline-flex items-center space-x-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition"
                                               title="Buka form penilaian">
                                                <span>Buka</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                            <div class="flex flex-col items-center justify-center space-y-1">
                                                <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                                <span>Belum ada karyawan yang dinilai oleh penilai.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABEL 2: DAFTAR JOB SUDAH DINILAI -->
                <div id="tabel-job-dinilai" class="scroll-mt-24 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
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
                                Posisi jabatan yang telah dievaluasi terhadap karyawan sasaran di kelompok ini.
                            </p>
                        </div>
                        <span class="px-3 py-1 self-start sm:self-center rounded-xl text-xs font-black bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 whitespace-nowrap">
                            {{ $assessedJobsList->count() }} / {{ $jobs->count() }} Job
                        </span>
                    </div>

                    <!-- Isi Tabel 2 -->
                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl mt-4">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400">
                                <tr>
                                    <th scope="col" class="py-3 px-3.5">Posisi Jabatan</th>
                                    <th scope="col" class="py-3 px-3 text-center">Karyawan Dinilai</th>
                                    <th scope="col" class="py-3 px-3">Penilai Aktif</th>
                                    <th scope="col" class="py-3 px-3 text-center">Total Ceklis</th>
                                    <th scope="col" class="py-3 px-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                                @forelse($assessedJobsList as $index => $item)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                        <!-- Kolom Posisi Jabatan (Hanya Nama Jabatan Saja) -->
                                        <td class="py-3 px-3.5 align-middle">
                                            <span class="font-bold text-slate-900 dark:text-white leading-snug">
                                                {{ $item->job->name }}
                                            </span>
                                        </td>
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

                                                <!-- Popup Dialog Box (Ukuran pas max-w-md sesuai card) -->
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
                                                                    Posisi: {{ $item->job->name }}
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
                                        <td class="py-3 px-3 text-center align-middle whitespace-nowrap">
                                            <a href="{{ route('wola.groups.assessments.index', $group) }}" 
                                               class="inline-flex items-center space-x-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-purple-600 hover:bg-purple-700 text-white shadow-xs transition"
                                               title="Buka form penilaian">
                                                <span>Buka</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-xs text-slate-400">
                                            <div class="flex flex-col items-center justify-center space-y-1">
                                                <svg class="w-7 h-7 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Belum ada posisi jabatan yang dinilai oleh penilai.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- ============================================================= -->
            <!-- 3. MATRIKS STATUS PENILAIAN (HEATMAP KARYAWAN × JABATAN) -->
            <!-- ============================================================= -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700"
                 x-data="{
                     search: '',
                     filterMode: 'all',
                     matches(empName, empNrp, completedRatio) {
                         const matchQuery = this.search === '' || 
                             empName.toLowerCase().includes(this.search.toLowerCase()) || 
                             empNrp.toLowerCase().includes(this.search.toLowerCase());
                         
                         if (!matchQuery) return false;
                         if (this.filterMode === 'completed') return completedRatio >= 1;
                         if (this.filterMode === 'incomplete') return completedRatio < 1;
                         return true;
                     }
                 }">
                
                <!-- Header Heatmap & Legend -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="p-1 rounded bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </span>
                            <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                Matriks Status Penilaian (Heatmap)
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Status verifikasi ceklis untuk setiap Karyawan Sasaran terhadap seluruh posisi jabatan yang diukur.
                        </p>
                    </div>

                    <!-- Indikator Legend & Statistik Ringkasan -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <div class="flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span>Lengkap: {{ $completedCells }}</span>
                        </div>
                        <div class="flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span>Sebagian: {{ $partialCells }}</span>
                        </div>
                        <div class="flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                            <span>Belum: {{ $unassessedCells }}</span>
                        </div>
                    </div>
                </div>

                <!-- Kontrol Pencarian & Filter Baris Karyawan -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 my-4">
                    <div class="relative w-full sm:w-80">
                        <input type="text" x-model="search" placeholder="Cari nama karyawan atau NRP..."
                               class="w-full pl-9 pr-3.5 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900 rounded-xl text-xs font-bold overflow-x-auto max-w-full">
                        <button type="button" @click="filterMode = 'all'"
                                :class="filterMode === 'all' ? 'bg-white dark:bg-slate-800 text-blue-600 shadow-sm' : 'text-slate-500'"
                                class="px-3 py-1 rounded-lg transition whitespace-nowrap">Semua Karyawan</button>
                        <button type="button" @click="filterMode = 'incomplete'"
                                :class="filterMode === 'incomplete' ? 'bg-white dark:bg-slate-800 text-amber-600 shadow-sm' : 'text-slate-500'"
                                class="px-3 py-1 rounded-lg transition whitespace-nowrap">Belum Lengkap</button>
                        <button type="button" @click="filterMode = 'completed'"
                                :class="filterMode === 'completed' ? 'bg-white dark:bg-slate-800 text-emerald-600 shadow-sm' : 'text-slate-500'"
                                class="px-3 py-1 rounded-lg transition whitespace-nowrap">Sudah Lengkap</button>
                    </div>
                </div>

                <!-- Tabel Heatmap Responsif -->
                <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="py-3 px-4 sticky left-0 z-10 bg-slate-50 dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700 min-w-[200px]">
                                    EMPLOYEENO & FULLNAME
                                </th>
                                @foreach($jobs as $job)
                                    <th scope="col" class="py-3 px-3 text-center whitespace-nowrap min-w-[115px]" title="{{ $job->name }}">
                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 max-w-[120px] mx-auto truncate">{{ $job->name }}</div>
                                        @if($job->target_employees !== null)
                                            <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $job->target_employees }} Orang</div>
                                        @endif
                                    </th>
                                @endforeach
                                <th scope="col" class="py-3 px-4 text-center whitespace-nowrap min-w-[125px]">
                                    Progres Baris
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800">
                            @forelse($targetEmployees as $emp)
                                @php
                                    // Hitung jumlah jabatan dinilai untuk karyawan ini (baik 1 penilai maupun lebih tetap terhitung)
                                    $empAssessedJobs = 0;
                                    $empCompletedCells = 0;
                                    $empTotalChecked = 0;

                                    foreach ($jobs as $j) {
                                        $cnt = $heatmapMatrix[$emp->id][$j->id]['count'] ?? 0;
                                        $empTotalChecked += $cnt;
                                        if ($cnt > 0) {
                                            $empAssessedJobs++;
                                        }
                                        if ($cnt >= $totalAssessors && $totalAssessors > 0) {
                                            $empCompletedCells++;
                                        }
                                    }
                                    $empRatio = $jobs->count() > 0 ? ($empAssessedJobs / $jobs->count()) : 0;
                                @endphp
                                <tr x-show="matches('{{ addslashes($emp->fullname) }}', '{{ $emp->nrp }}', {{ $empRatio }})"
                                    class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                    
                                    <!-- Sticky Kolom Karyawan: EMPLOYEENO & FULLNAME (Format: 1183 DIKKI MESWANTORI) -->
                                    <td class="py-3 px-4 sticky left-0 z-10 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 whitespace-nowrap">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                                            <span class="font-mono text-blue-600 dark:text-blue-400 font-bold">{{ $emp->nrp }}</span>
                                            <span>{{ $emp->fullname }}</span>
                                        </div>
                                    </td>

                                    <!-- Sel per Jabatan -->
                                    @foreach($jobs as $job)
                                        @php
                                            $cellData = $heatmapMatrix[$emp->id][$job->id] ?? null;
                                            $cnt = $cellData ? $cellData['count'] : 0;
                                            $assessorNames = $cellData ? $cellData['assessors'] : [];
                                            $isFull = ($cnt >= $totalAssessors && $totalAssessors > 0);
                                            $isPartial = ($cnt > 0 && !$isFull);
                                            
                                            // Tooltip info string
                                            $empLabel = ($emp->nrp ? $emp->nrp . ' ' : '') . $emp->fullname;
                                            $tooltipText = "Karyawan: {$empLabel}\nJabatan: {$job->name}\nStatus: {$cnt} dari {$totalAssessors} Penilai";
                                            if (count($assessorNames) > 0) {
                                                $tooltipText .= "\nPenilai: " . implode(', ', $assessorNames);
                                            } else {
                                                $tooltipText .= "\nBelum ada penilai";
                                            }
                                        @endphp
                                        <td class="p-1.5 text-center align-middle" title="{{ $tooltipText }}">
                                            <div class="relative group cursor-pointer inline-flex items-center justify-center w-full py-1.5 px-2 rounded-lg text-xs font-bold transition
                                                @if($isFull)
                                                    bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800/80 hover:bg-emerald-200
                                                @elseif($isPartial)
                                                    bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-800/80 hover:bg-amber-200
                                                @else
                                                    bg-slate-100 text-slate-400 dark:bg-slate-800/60 dark:text-slate-500 border border-slate-200 dark:border-slate-700/60 hover:bg-slate-200
                                                @endif">
                                                
                                                @if($isFull)
                                                    <span class="flex items-center space-x-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        <span>{{ $cnt }}/{{ $totalAssessors }}</span>
                                                    </span>
                                                @elseif($isPartial)
                                                    <span>{{ $cnt }}/{{ $totalAssessors }}</span>
                                                @else
                                                    <span>-</span>
                                                @endif

                                                <!-- Hover Popover Interaktif -->
                                                <div class="opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-56 p-3 bg-slate-900 text-white text-[11px] rounded-xl shadow-xl z-30 font-normal">
                                                    <div class="font-bold text-xs text-blue-400 border-b border-slate-700 pb-1 mb-1">
                                                        {{ $job->name }}
                                                    </div>
                                                    <div class="text-slate-300">
                                                        Target: <span class="text-white font-semibold">{{ $empLabel }}</span>
                                                    </div>
                                                    <div class="mt-1 text-slate-300">
                                                        Ceklis: <span class="font-bold {{ $isFull ? 'text-emerald-400' : ($isPartial ? 'text-amber-400' : 'text-slate-400') }}">{{ $cnt }} dari {{ $totalAssessors }} Penilai</span>
                                                    </div>
                                                    @if(count($assessorNames) > 0)
                                                        <div class="mt-1.5 pt-1.5 border-t border-slate-800 text-[10px] text-slate-400">
                                                            <span class="text-emerald-400 font-semibold block mb-0.5">Sudah Menilai:</span>
                                                            @foreach($assessorNames as $an)
                                                                <div>• {{ $an }}</div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <div class="w-2 h-2 bg-slate-900 rotate-45 absolute -bottom-1 left-1/2 -translate-x-1/2"></div>
                                                </div>
                                            </div>
                                        </td>
                                    @endforeach

                                    <!-- Kolom Progres Baris -->
                                    <td class="py-2.5 px-4 text-center whitespace-nowrap" title="{{ $empAssessedJobs }} dari {{ $jobs->count() }} posisi jabatan telah dinilai (minimal 1 penilai)">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <span class="font-bold text-xs {{ $empAssessedJobs > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-700 dark:text-slate-300' }}">
                                                {{ $empAssessedJobs }} / {{ $jobs->count() }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">jabatan</span>
                                        </div>
                                        <div class="w-20 mx-auto bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 mt-1 overflow-hidden">
                                            <div class="h-1.5 rounded-full transition-all duration-500 {{ $empRatio >= 1 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ round($empRatio * 100) }}%"></div>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5 font-medium">
                                            {{ round($empRatio * 100) }}%
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $jobs->count() + 2 }}" class="py-8 text-center text-slate-400">
                                        Belum ada karyawan target yang terdaftar pada kelompok ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 dark:text-slate-400 gap-2">
                    <div>
                        Menampilkan <strong>{{ $targetEmployees->count() }}</strong> karyawan target di area {{ $group->name }} dengan <strong>{{ $jobs->count() }}</strong> jabatan.
                    </div>
                    <div class="flex items-center space-x-4">
                        <span>Arahkan kursor (hover) pada kotak sel untuk melihat detail nama penilai.</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- MODAL TAMBAH PENILAI (KHUSUS KETUA & ADMIN) -->
        <!-- ========================================================================= -->
        <div x-show="showAddAssessorModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700"
                 @click.away="showAddAssessorModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Tambah Anggota Penilai
                        </h3>
                        <p class="text-xs text-slate-500">Kelompok {{ $group->number }} — {{ $group->name }}</p>
                    </div>
                    <button type="button" @click="showAddAssessorModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <form method="POST" action="{{ route('wola.groups.assessors.add', $group) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Nama Lengkap Penilai <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="fullname" required
                               placeholder="Ketik nama lengkap penilai (contoh: ISNANTO / BUDI SANTOSO)..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <p class="text-[11px] text-slate-400 mt-1">Bebas diketik langsung sesuai kebutuhan tim penilai kelompok Anda.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                EMPLOYEENO / NRP <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="nrp"
                                   placeholder="Contoh: 1183 atau kosongkan..."
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Plant <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" name="plant" value="CIKARANG"
                                   placeholder="CIKARANG atau KARAWANG..."
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Departemen <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <input type="text" name="dept" value="{{ $group->name }}"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" @click="showAddAssessorModal = false"
                                class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md transition">
                            Simpan Penilai
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL EDIT PENILAI -->
        <!-- ========================================================================= -->
        <div x-show="showEditAssessorModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700"
                 @click.away="showEditAssessorModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Edit Data Penilai
                        </h3>
                        <p class="text-xs text-slate-500">Kelompok {{ $group->number }} — {{ $group->name }}</p>
                    </div>
                    <button type="button" @click="showEditAssessorModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <form method="POST" :action="editAssessorData.updateUrl" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Nama Lengkap Penilai <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="fullname" x-model="editAssessorData.fullname" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                EMPLOYEENO / NRP
                            </label>
                            <input type="text" name="nrp" x-model="editAssessorData.nrp"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Plant
                            </label>
                            <input type="text" name="plant" x-model="editAssessorData.plant"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Departemen
                        </label>
                        <input type="text" name="dept" x-model="editAssessorData.dept"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" @click="showEditAssessorModal = false"
                                class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
