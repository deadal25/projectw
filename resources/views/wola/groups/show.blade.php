<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                @if(auth()->user()->hasRole(['superadmin', 'admin']))
                    <a href="{{ route('wola.groups.index') }}"
                       class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                @endif
                <div>
                    <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                        {{ $group->name }}
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Ketua: <strong class="text-slate-700 dark:text-slate-300">{{ $group->leader ? $group->leader->name : 'Belum ditetapkan' }}</strong>
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('wola.groups.assessments.index', $group) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/30 transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Mulai Penilaian (Ceklis)</span>
                </a>

                @if(auth()->user()->hasRole(['superadmin', 'admin']))
                    <a href="{{ route('wola.groups.edit', $group) }}"
                       class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit Kelompok
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        tab: '{{ $activeTab }}',
        showAddAssessorModal: false,
        showEditAssessorModal: false,
        editAssessorData: {
            id: null,
            fullname: '',
            nrp: '',
            dept: '',
            plant: '',
            is_leader: false,
            updateUrl: ''
        },
        openEditAssessor(id, fullname, nrp, dept, plant, isLeader, url) {
            this.editAssessorData = {
                id: id,
                fullname: fullname,
                nrp: nrp,
                dept: dept,
                plant: plant,
                is_leader: isLeader,
                updateUrl: url
            };
            this.showEditAssessorModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <!-- Dashboard Pemantauan Progres Area -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Target Checklist -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Target Checklist</span>
                        <div class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ number_format($progressData['target']) }} <span class="text-xs font-semibold text-slate-400">Unit</span>
                        </div>
                        <div class="mt-1">
                            @if($progressData['is_custom_target'])
                                <span class="inline-flex items-center text-[11px] font-bold text-indigo-600 dark:text-indigo-400">
                                    Target Khusus (Manual)
                                </span>
                            @else
                                <span class="inline-flex items-center text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                    Target Standar Sistem
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Karyawan Dinilai (Realisasi) -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Karyawan Dinilai</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline justify-between">
                            <div class="text-2xl font-black text-slate-900 dark:text-white">
                                {{ $assessedEmployeesCount }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalTargetEmployees }} Orang</span>
                            </div>
                            <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                                {{ $employeeProgressPercentage }}%
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 mt-2 overflow-hidden">
                            <div class="h-1.5 rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $employeeProgressPercentage }}%"></div>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-400">
                            Karyawan sasaran telah diverifikasi
                        </div>
                    </div>
                </div>

                <!-- Capaian Progres (%) -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Capaian Progres</span>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="flex items-baseline justify-between">
                            <div class="text-2xl font-black text-blue-600 dark:text-blue-400">
                                {{ $progressData['percentage'] }}%
                            </div>
                            <span class="text-[11px] font-semibold text-slate-400">
                                {{ $progressData['assessed_employees_count'] }}/{{ $progressData['employee_count'] }} Orang
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 mt-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-500
                                @if($progressData['status_color'] === 'green') bg-emerald-500
                                @elseif($progressData['status_color'] === 'blue') bg-blue-500
                                @elseif($progressData['status_color'] === 'yellow') bg-amber-500
                                @elseif($progressData['status_color'] === 'red') bg-rose-500
                                @else bg-slate-400 @endif"
                                 style="width: {{ $progressData['percentage'] }}%"></div>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-400">
                            Karyawan yang sudah dinilai: <strong class="text-slate-700 dark:text-slate-300">{{ $assessedEmployeesCount }}</strong> dari <strong class="text-slate-700 dark:text-slate-300">{{ $totalTargetEmployees }}</strong> Orang
                        </div>
                    </div>
                </div>

                <!-- Status Operasional -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-xs font-bold uppercase tracking-wider">Status Pemantauan</span>
                        <div class="w-8 h-8 rounded-xl bg-slate-50 dark:bg-slate-900/40 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                @if($progressData['status_color'] === 'green') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300
                                @elseif($progressData['status_color'] === 'blue') bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300
                                @elseif($progressData['status_color'] === 'yellow') bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300
                                @elseif($progressData['status_color'] === 'red') bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300
                                @else bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 @endif">
                                {{ $progressData['status'] }}
                            </span>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-400">
                            {{ $progressData['job_count'] }} Posisi Jabatan • {{ $progressData['assessor_count'] }} Penilai
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-slate-200 dark:border-slate-700">
                <nav class="flex space-x-6 text-sm font-bold">
                    <button type="button" @click="tab = 'jobs'"
                            :class="tab === 'jobs' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                            class="py-3 px-1 border-b-2 transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>1. Daftar Jabatan ({{ $group->jobs->count() }})</span>
                    </button>

                    <button type="button" @click="tab = 'assessors'"
                            :class="tab === 'assessors' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                            class="py-3 px-1 border-b-2 transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>2. Tim Penilai ({{ $group->assessors->count() }})</span>
                    </button>

                    <button type="button" @click="tab = 'employees'"
                            :class="tab === 'employees' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                            class="py-3 px-1 border-b-2 transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>3. Karyawan Sasaran Dinilai ({{ $targetEmployees->count() }})</span>
                    </button>
                </nav>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 1: DAFTAR JABATAN (JOBS) -->
            <!-- ============================================================= -->
            <div x-show="tab === 'jobs'" class="space-y-6">
                <!-- Form Tambah Jabatan Baru & Override Target -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">
                            + Tambah Jabatan Baru untuk Kelompok Ini
                        </h3>
                        <form method="POST" action="{{ route('wola.groups.jobs.add', $group) }}" class="flex flex-col sm:flex-row items-center gap-3">
                            @csrf
                            <div class="w-full sm:flex-1">
                                <input type="text" name="name" required placeholder="Contoh: Sub Leader Production Control"
                                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <div class="w-full sm:w-24">
                                <input type="number" name="sort_order" value="{{ ($group->jobs->max('sort_order') ?? 0) + 1 }}" placeholder="Urutan"
                                       class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" title="Nomor Urutan Tampilan">
                            </div>
                            <div class="w-full sm:w-32">
                                <input type="number" name="target_employees" min="0" placeholder="Target Orang"
                                       class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" title="Berapa orang target yang memegang/dievaluasi pada posisi ini">
                            </div>
                            <button type="submit"
                                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md transition shrink-0">
                                Simpan Jabatan
                            </button>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-1">
                                Target Khusus (Override)
                            </h3>
                            <p class="text-xs text-slate-400 mb-3">Atur target checklist khusus (kosongkan untuk standar sistem).</p>
                        </div>
                        <form method="POST" action="{{ route('wola.groups.target.update', $group) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="number" name="custom_target" value="{{ $group->custom_target }}" placeholder="Standar Sistem"
                                   class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-white">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shrink-0 shadow-sm">
                                Simpan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tabel Jabatan -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden"
                     x-data="{ activeEditJobId: null }">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4 w-16 text-center">Urutan</th>
                                <th class="py-3 px-4">Nama Jabatan</th>
                                <th class="py-3 px-4 w-44 text-center">Target Orang</th>
                                <th class="py-3 px-4">Capaian Progres</th>
                                <th class="py-3 px-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($group->jobs as $job)
                                @php
                                    $jobProgress = $job->getProgressData();
                                @endphp
                                <!-- Baris Data Jabatan -->
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40 transition-colors"
                                    :class="activeEditJobId === {{ $job->id }} ? 'bg-blue-50/70 dark:bg-blue-950/40 ring-1 ring-inset ring-blue-500' : ''">
                                    
                                    <td class="py-3 px-4 text-center font-mono text-slate-400 text-xs font-bold">
                                        {{ $job->sort_order }}
                                    </td>

                                    <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                        <span>{{ $job->name }}</span>
                                    </td>

                                    <!-- Target Orang (Klik untuk langsung buka form edit target) -->
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" 
                                                @click="activeEditJobId = (activeEditJobId === {{ $job->id }} ? null : {{ $job->id }})"
                                                class="group inline-flex items-center space-x-1 transition cursor-pointer"
                                                title="Klik untuk mengubah target orang jabatan ini">
                                            @if($job->target_employees !== null)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-black rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shadow-sm group-hover:bg-indigo-100 group-hover:scale-105 transition">
                                                    {{ $job->target_employees }} Orang
                                                    <svg class="w-3 h-3 ms-1 text-indigo-500 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-mono text-slate-500 dark:text-slate-400 rounded-lg bg-slate-100 dark:bg-slate-800 border border-dashed border-slate-300 dark:border-slate-700 group-hover:border-blue-400 group-hover:text-blue-600 transition">
                                                    {{ $group->groupEmployees()->count() }} Orang <span class="text-[9px] ms-1 text-slate-400">(all)</span>
                                                    <svg class="w-3 h-3 ms-1 opacity-40 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </span>
                                            @endif
                                        </button>
                                    </td>

                                    <!-- Capaian Progres Jabatan -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-28 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $jobProgress['percentage'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $jobProgress['percentage'] }}%</span>
                                            <span class="text-[11px] text-slate-400">({{ $jobProgress['checked'] }}/{{ $jobProgress['target'] }})</span>
                                        </div>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <!-- Tombol Edit Pensil -->
                                            <button type="button" 
                                                    @click="activeEditJobId = (activeEditJobId === {{ $job->id }} ? null : {{ $job->id }})" 
                                                    :class="activeEditJobId === {{ $job->id }} ? 'text-blue-600 bg-blue-100 dark:bg-blue-900/60' : 'text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                                    class="p-2 rounded-lg transition" 
                                                    title="Edit Nama, Urutan, dan Target Orang">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                            <form method="POST" action="{{ route('wola.groups.jobs.remove', [$group, $job]) }}"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition" title="Hapus Jabatan">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Baris Edit Inline Luas (Buka saat activeEditJobId === job.id) -->
                                <tr x-show="activeEditJobId === {{ $job->id }}" 
                                    x-cloak 
                                    class="bg-blue-50/90 dark:bg-blue-950/70 border-y-2 border-blue-500 shadow-inner">
                                    <td colspan="5" class="p-4 sm:p-5">
                                        <form method="POST" action="{{ route('wola.groups.jobs.update', [$group, $job]) }}" class="space-y-3">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="flex items-center justify-between pb-2 border-b border-blue-200 dark:border-blue-900/80">
                                                <div class="flex items-center space-x-2">
                                                    <h4 class="text-xs font-extrabold text-blue-950 dark:text-blue-100 uppercase tracking-wider">
                                                        Edit Posisi Jabatan & Target Orang
                                                    </h4>
                                                </div>
                                                <span class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold font-mono">
                                                    ID: #{{ $job->id }}
                                                </span>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-1 items-end">
                                                <div class="sm:col-span-2">
                                                    <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 mb-1">
                                                        Urutan
                                                    </label>
                                                    <input type="number" name="sort_order" value="{{ $job->sort_order }}" required 
                                                           class="w-full px-2.5 py-2 text-xs font-mono font-bold border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 shadow-sm">
                                                </div>
                                                <div class="sm:col-span-6">
                                                    <label class="block text-[10px] uppercase font-bold text-slate-600 dark:text-slate-300 mb-1">
                                                        Nama Jabatan
                                                    </label>
                                                    <input type="text" name="name" value="{{ $job->name }}" required 
                                                           class="w-full px-3.5 py-2 text-xs font-bold border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 shadow-sm">
                                                </div>
                                                <div class="sm:col-span-4">
                                                    <label class="block text-[10px] uppercase font-extrabold text-indigo-700 dark:text-indigo-300 mb-1 flex items-center justify-between">
                                                        <span>Target Orang (Karyawan)</span>
                                                        <span class="text-[9px] font-normal text-slate-400 lowercase">(kosong = ikuti kelompok)</span>
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number" name="target_employees" value="{{ $job->target_employees }}" min="0" placeholder="Semua ({{ $group->groupEmployees()->count() }})"
                                                               class="w-full pl-3 pr-14 py-2 text-xs font-black border-2 border-indigo-400 dark:border-indigo-600 rounded-xl bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-300 focus:ring-2 focus:ring-indigo-500 shadow-sm">
                                                        <span class="absolute right-3 top-2 text-[11px] text-slate-400 font-bold pointer-events-none">Orang</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                                    Target checklist jabatan ini otomatis menyesuaikan jumlah tim penilai dan target karyawan yang ditetapkan.
                                                </p>
                                                <div class="flex items-center space-x-2 shrink-0 w-full sm:w-auto justify-end">
                                                    <button type="button" @click="activeEditJobId = null" 
                                                            class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                                                        Batal
                                                    </button>
                                                    <button type="submit" 
                                                            class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-black shadow-md transition flex items-center space-x-1.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Simpan Perubahan</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada jabatan di kelompok ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: TIM PENILAI (ASSESSORS) -->
            <!-- ============================================================= -->
            <div x-show="tab === 'assessors'" class="space-y-6">

                <!-- Kartu Integrasi Manajemen Akun & Ketua -->
                <div class="bg-gradient-to-r from-blue-900/10 via-slate-900/5 to-transparent dark:from-blue-950/40 dark:via-slate-900/40 p-5 rounded-2xl border border-blue-200 dark:border-blue-900/50 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black shadow-md shadow-blue-500/30 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                    Tim Penilai: {{ $group->name }}
                                </h3>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Terhubung Manajemen Akun
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Akun Ketua: <strong class="text-slate-800 dark:text-slate-200">{{ $group->leader ? $group->leader->name : 'Belum ditetapkan di Manajemen Akun' }}</strong>
                                @if($group->leader)
                                    <span class="text-slate-400">({{ $group->leader->email }})</span>
                                @endif
                                • Total: <strong class="text-blue-600 dark:text-blue-400">{{ $group->assessors->count() }} Orang Penilai</strong>
                            </p>
                        </div>
                    </div>

                    @if(auth()->user()->hasRole(['superadmin', 'admin']) || (auth()->user()->hasRole('ketua') && auth()->user()->group_id == $group->id))
                        <div class="flex items-center gap-2">
                            <!-- Tombol Tambah Penilai Cepat (Manual) -->
                            <button type="button"
                                    @click="showAddAssessorModal = true"
                                    class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 transition shrink-0">
                                <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Penilai
                            </button>

                            <!-- Link ke Manajemen Akun -->
                            <a href="{{ route('wola.users.index', ['tab' => 'groups']) }}"
                               class="inline-flex items-center px-3.5 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 text-xs font-bold rounded-xl transition shrink-0"
                               title="Buka Struktur Kelompok di Manajemen Akun">
                                <svg class="w-4 h-4 me-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Manajemen Akun
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Tabel Penilai -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">Status & NRP</th>
                                <th class="py-3 px-4">Nama Lengkap Penilai</th>
                                <th class="py-3 px-4">Departemen & Plant</th>
                                <th class="py-3 px-4">Capaian Ceklis Penilai</th>
                                @if(auth()->user()->hasRole(['superadmin', 'admin']))
                                    <th class="py-3 px-4 text-center w-40">Aksi Penilai</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($group->assessors as $assessor)
                                @php
                                    $assessorProgress = $assessor->getProgressData();
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center space-x-2">
                                            @if($assessor->is_leader)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                                    ★ Ketua
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                                    Penilai
                                                </span>
                                            @endif
                                            <span class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">{{ $assessor->employee?->nrp ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                        {{ $assessor->employee?->fullname ?? 'Karyawan Tidak Ditemukan' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-500">
                                        {{ $assessor->employee?->dept ?? '-' }} • {{ $assessor->employee?->plant ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-28 bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $assessorProgress['percentage'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $assessorProgress['percentage'] }}%</span>
                                            <span class="text-[11px] text-slate-400">({{ $assessorProgress['checked'] }}/{{ $assessorProgress['target'] }})</span>
                                        </div>
                                    </td>
                                    @if(auth()->user()->hasRole(['superadmin', 'admin']) || (auth()->user()->hasRole('ketua') && auth()->user()->group_id == $group->id))
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <!-- Tombol Edit Penilai Manual -->
                                                <button type="button"
                                                        @click="openEditAssessor({{ $assessor->id }}, '{{ addslashes($assessor->employee?->fullname ?? '') }}', '{{ addslashes($assessor->employee?->nrp ?? '') }}', '{{ addslashes($assessor->employee?->dept ?? '') }}', '{{ addslashes($assessor->employee?->plant ?? '') }}', {{ $assessor->is_leader ? 'true' : 'false' }}, '{{ route('wola.groups.assessors.update', [$group, $assessor]) }}')"
                                                        class="p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                                        title="Edit Data Penilai">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                </button>

                                                <!-- Tombol Hapus Penilai (Hanya untuk Anggota Penilai, Ketua Tidak Dapat Dihapus) -->
                                                @if(!$assessor->is_leader)
                                                    <form method="POST" action="{{ route('wola.groups.assessors.remove', [$group, $assessor]) }}"
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus penilai {{ $assessor->employee?->fullname }} dari kelompok?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white transition shadow-xs inline-flex items-center justify-center" title="Hapus Penilai">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="px-2 py-1 text-[10px] font-semibold text-slate-400 bg-slate-100 dark:bg-slate-800 rounded-lg italic" title="Ketua ditetapkan dari Manajemen Akun">
                                                        Ketua (Terkunci)
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->hasRole(['superadmin', 'admin']) ? 5 : 4 }}" class="py-8 text-center text-slate-400">
                                        Belum ada penilai di kelompok ini. Klik tombol <strong>Tambah Penilai</strong> untuk menambahkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ========================================================= -->
                <!-- MODAL TAMBAH PENILAI MANUAL -->
                <!-- ========================================================= -->
                <div x-show="showAddAssessorModal"
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700"
                         @click.away="showAddAssessorModal = false">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                    Tambah Penilai Baru
                                </h3>
                                <p class="text-xs text-slate-500">Kelompok {{ $group->number }} — {{ $group->name }}</p>
                            </div>
                            <button type="button" @click="showAddAssessorModal = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                        </div>

                        <form method="POST" action="{{ route('wola.groups.assessors.add', $group) }}" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Nama Lengkap Penilai <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="fullname" required
                                       placeholder="Contoh: Nanang Siswanto"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        EMPLOYEENO / NRP
                                    </label>
                                    <input type="text" name="nrp"
                                           placeholder="Contoh: 622 (Opsional)"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Plant
                                    </label>
                                    <input type="text" name="plant" value="CIKARANG"
                                           placeholder="Contoh: CIKARANG"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Departemen
                                </label>
                                <input type="text" name="dept" value="{{ $group->name }}"
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            @if($availableAssessors->isNotEmpty())
                                <div x-data="{ pickFromMaster: false }" class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                                    <button type="button" @click="pickFromMaster = !pickFromMaster"
                                            class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline">
                                        <span x-show="!pickFromMaster">+ Atau Pilih dari Master Karyawan</span>
                                        <span x-show="pickFromMaster">✕ Tutup Pilihan Master</span>
                                    </button>
                                    <div x-show="pickFromMaster" class="mt-2">
                                        <select name="employee_id"
                                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white">
                                            <option value="">-- Pilih jika ada di master --</option>
                                            @foreach($availableAssessors as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->nrp }} - {{ $emp->fullname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif



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

                <!-- ========================================================= -->
                <!-- MODAL EDIT PENILAI MANUAL -->
                <!-- ========================================================= -->
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
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Nama Lengkap Penilai <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="fullname" x-model="editAssessorData.fullname" required
                                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        EMPLOYEENO / NRP
                                    </label>
                                    <input type="text" name="nrp" x-model="editAssessorData.nrp"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Plant
                                    </label>
                                    <input type="text" name="plant" x-model="editAssessorData.plant"
                                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
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

            <!-- ============================================================= -->
            <!-- TAB 3: KARYAWAN SASARAN DINILAI (TARGET EMPLOYEES) -->
            <!-- ============================================================= -->
            <div x-show="tab === 'employees'" class="space-y-6"
                 x-data="{
                     searchModalOpen: false,
                     searchQuery: '',
                     deptFilter: '',
                     searchResults: [],
                     selectedEmployees: [],
                     loading: false,
                     selectAll: false,
                     
                     async search() {
                         this.loading = true;
                         try {
                             const url = new URL('{{ route('wola.groups.employees.search', $group) }}');
                             if (this.searchQuery) url.searchParams.append('q', this.searchQuery);
                             if (this.deptFilter) url.searchParams.append('dept', this.deptFilter);
                             const res = await fetch(url);
                             this.searchResults = await res.json();
                             this.selectAll = false;
                         } catch (e) {
                             console.error(e);
                         } finally {
                             this.loading = false;
                         }
                     },

                     toggleSelectAll() {
                         if (this.selectAll) {
                             this.selectedEmployees = this.searchResults.map(e => e.id);
                         } else {
                             this.selectedEmployees = [];
                         }
                     }
                 }">

                <!-- Toolbar Atas -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Daftar Karyawan yang Dinilai di Area Ini
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Total saat ini: <strong class="text-blue-600 dark:text-blue-400 font-bold">{{ $targetEmployees->count() }} orang</strong>
                        </p>
                    </div>

                    @if(auth()->user()->hasRole(['superadmin', 'admin']))
                        <!-- Tombol Buka Modal Cari Master Karyawan -->
                        <button type="button" @click="searchModalOpen = true; search();"
                                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/30 transition">
                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            + Tambah Karyawan dari Master
                        </button>
                    @endif
                </div>

                <!-- Tabel Karyawan Sasaran yang Terdaftar di Kelompok Ini -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4 font-mono">EMPLOYEENO</th>
                                <th class="py-3 px-4">FULLNAME</th>
                                <th class="py-3 px-4">DIVISION</th>
                                <th class="py-3 px-4">DEPT</th>
                                <th class="py-3 px-4">PLANT</th>
                                @if(auth()->user()->hasRole(['superadmin', 'admin']))
                                    <th class="py-3 px-4 text-center w-24">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($targetEmployees as $index => $emp)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40">
                                    <td class="py-3 px-4 text-center text-xs font-mono text-slate-400">{{ $index + 1 }}</td>
                                    <td class="py-3 px-4 font-mono font-bold text-blue-600 dark:text-blue-400">{{ $emp->nrp }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">{{ $emp->fullname }}</td>
                                    <td class="py-3 px-4 font-medium text-slate-700 dark:text-slate-300">{{ $emp->division ?: $emp->section }}</td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ $emp->dept }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                            {{ $emp->plant }}
                                        </span>
                                    </td>
                                    @if(auth()->user()->hasRole(['superadmin', 'admin']))
                                        <td class="py-3 px-4 text-center">
                                            <form method="POST" action="{{ route('wola.groups.employees.remove', [$group, $emp]) }}"
                                                  onsubmit="return confirm('Keluarkan {{ $emp->fullname }} dari sasaran penilaian kelompok ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white transition shadow-xs inline-flex items-center justify-center" title="Keluarkan Karyawan">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->hasRole(['superadmin', 'admin']) ? 7 : 6 }}" class="py-12 text-center text-slate-400">
                                        Belum ada karyawan yang dinilai di kelompok ini.@if(auth()->user()->hasRole(['superadmin', 'admin'])) Klik tombol "+ Tambah Karyawan dari Master" di atas untuk memilih karyawan.@endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(auth()->user()->hasRole(['superadmin', 'admin']))
                    <!-- MODAL PENCARIAN & CHECKBOX MASTER KARYAWAN -->
                    <div x-show="searchModalOpen" style="display: none;"
                         class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden"
                             @click.away="searchModalOpen = false">
                            
                            <!-- Modal Header -->
                            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pilih Karyawan dari Master</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Centang karyawan yang akan dinilai di Kelompok {{ $group->number }} ({{ $group->name }}).</p>
                                </div>
                                <button type="button" @click="searchModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                            </div>

                            <!-- Search Bar Filter -->
                            <div class="p-6 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-3">
                                <div class="flex-1">
                                    <input type="text" x-model="searchQuery" @input.debounce.300ms="search()"
                                           placeholder="Ketik NRP atau nama karyawan..."
                                           class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <!-- Modal Table Content -->
                            <div class="overflow-y-auto flex-1 p-6">
                                <form id="sync-employees-form" method="POST" action="{{ route('wola.groups.employees.sync', $group) }}">
                                    @csrf

                                    <div class="flex items-center justify-between mb-3 text-xs font-bold text-slate-600 dark:text-slate-400">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()"
                                                   class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                                            <span class="ms-2">Pilih Semua yang Tampil</span>
                                        </label>
                                        <span x-text="selectedEmployees.length + ' Karyawan dipilih'"></span>
                                    </div>

                                    <template x-if="loading">
                                        <div class="py-12 text-center text-slate-400">
                                            <p class="text-sm">Memuat data master karyawan...</p>
                                        </div>
                                    </template>

                                    <template x-if="!loading && searchResults.length === 0">
                                        <div class="py-12 text-center text-slate-400">
                                            <p class="text-sm font-semibold">Tidak ada karyawan baru yang ditemukan.</p>
                                        </div>
                                    </template>

                                    <template x-if="!loading && searchResults.length > 0">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 uppercase tracking-wider font-bold">
                                                    <th class="py-2.5 px-3 w-10 text-center">Pilih</th>
                                                    <th class="py-2.5 px-3">AREA WLA</th>
                                                    <th class="py-2.5 px-3 font-mono">EMPLOYEENO</th>
                                                    <th class="py-2.5 px-3">FULLNAME</th>
                                                    <th class="py-2.5 px-3">DIVISION</th>
                                                    <th class="py-2.5 px-3">DEPT</th>
                                                    <th class="py-2.5 px-3">PLANT</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                <template x-for="emp in searchResults" :key="emp.id">
                                                    <tr class="hover:bg-blue-50/50 dark:hover:bg-slate-800/50 cursor-pointer"
                                                        @click="if (!selectedEmployees.includes(emp.id)) { selectedEmployees.push(emp.id); } else { selectedEmployees = selectedEmployees.filter(id => id !== emp.id); }">
                                                        <td class="py-2 px-3 text-center" @click.stop>
                                                            <input type="checkbox" name="employee_ids[]" :value="emp.id" x-model="selectedEmployees"
                                                                   class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                                                        </td>
                                                        <td class="py-2 px-3 font-semibold text-blue-700 dark:text-blue-300" x-text="emp.area_wla || '-'"></td>
                                                        <td class="py-2 px-3 font-mono font-bold text-blue-600 dark:text-blue-400" x-text="emp.nrp"></td>
                                                        <td class="py-2 px-3 font-semibold text-slate-900 dark:text-white" x-text="emp.fullname"></td>
                                                        <td class="py-2 px-3 text-slate-700 dark:text-slate-300" x-text="emp.division || emp.section || '-'"></td>
                                                        <td class="py-2 px-3 text-slate-600 dark:text-slate-400" x-text="emp.dept"></td>
                                                        <td class="py-2 px-3" x-text="emp.plant"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </template>
                                </form>
                            </div>

                            <!-- Modal Footer -->
                            <div class="p-6 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/60">
                                <span class="text-xs text-slate-500 font-semibold" x-text="selectedEmployees.length + ' orang akan ditambahkan'"></span>
                                <div class="flex items-center space-x-3">
                                    <button type="button" @click="searchModalOpen = false"
                                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition">
                                        Batal
                                    </button>
                                    <button type="submit" form="sync-employees-form" :disabled="selectedEmployees.length === 0"
                                            class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-xs font-bold shadow-md transition">
                                        Tambahkan Karyawan Terpilih
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>
