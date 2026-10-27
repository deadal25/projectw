<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('wola.groups.show', $group) }}"
                   class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <div>
                        <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                            {{ __('Pilih Penilai') }}
                        </h2>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Pilih penilai untuk memulai proses checklist penilaian WOLA.
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('wola.groups.show', [$group, 'tab' => 'assessors']) }}"
                   class="inline-flex items-center text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-blue-600 transition">
                    <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Kelola Penilai
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6">

            @if(!$isKetuaOnly && $allGroups->count() > 1)
                <!-- Selektor 12 Kelompok Khusus Administrator -->
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
                                        Mode Administrator: Pilih Area Kelompok
                                    </span>
                                    <span class="hidden sm:inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/60 dark:text-blue-300">
                                        Pilih Penilai
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                    Beralih antar kelompok kerja untuk menentukan penilai lembar kerja WOLA
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
                                <a href="{{ route('wola.groups.assessments.index', $g) }}"
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

            <!-- Ringkasan Kelompok Banner -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Progres Kelompok Ini</span>
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $group->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $groupProgress['checked'] }} dari {{ $groupProgress['target'] }} checklist selesai dicatat
                        </p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-40 sm:w-64 bg-slate-100 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-500" style="width: {{ $groupProgress['percentage'] }}%"></div>
                        </div>
                        <span class="text-lg font-extrabold text-blue-600 dark:text-blue-400">{{ $groupProgress['percentage'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Pengisian -->
            <div class="p-4 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 text-xs text-blue-900 dark:text-blue-200 flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <strong>Alur Penilaian:</strong> Pilih kartu penilai di bawah ini. Anda dapat menceklis atas nama penilai tersebut, kemudian bebas memilih pengisian <strong>Mode Per Jabatan</strong> atau <strong>Mode Per Karyawan</strong> dan dapat berpindah mode kapan saja.
                </div>
            </div>

            <!-- Grid Kartu Penilai -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($assessors as $assessor)
                    @php
                        $progress = $assessor->getProgressData();
                    @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border-2 transition-all p-6 flex flex-col justify-between
                        {{ $assessor->is_leader ? 'border-blue-500/50 shadow-md shadow-blue-500/5' : 'border-slate-200 dark:border-slate-700 shadow-sm' }}">
                        <div>
                            <!-- Header Kartu Penilai -->
                            <div class="flex items-center justify-between mb-4">
                                @if($assessor->is_leader)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200 shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 01.894.553l1.618 3.279 3.619.526a1 1 0 01.554 1.705l-2.618 2.552.618 3.604a1 1 0 01-1.451 1.054L10 13.567l-3.236 1.702a1 1 0 01-1.451-1.054l.618-3.604-2.618-2.552a1 1 0 01.554-1.705l3.619-.526L9.106 2.553A1 1 0 0110 2z" clip-rule="evenodd"/></svg>
                                        Ketua Kelompok
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        Anggota Penilai
                                    </span>
                                @endif
                                <span class="font-mono text-xs font-bold text-slate-500">NRP: {{ $assessor->employee->nrp }}</span>
                            </div>

                            <!-- Profil Penilai -->
                            <div class="flex items-center space-x-3.5 mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 font-extrabold text-base flex items-center justify-center shrink-0 border border-blue-600/20">
                                    {{ strtoupper(substr($assessor->employee->fullname, 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="font-extrabold text-slate-900 dark:text-white text-base leading-snug">
                                        {{ $assessor->employee->fullname }}
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $assessor->employee->dept }} • {{ $assessor->employee->section }}
                                    </p>
                                </div>
                            </div>

                            <!-- Progress Penilai -->
                            <div class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60 mb-5">
                                <div class="flex justify-between items-center text-xs mb-1.5">
                                    <span class="text-slate-500 font-medium">Progres Penilaian</span>
                                    <span class="font-extrabold text-slate-800 dark:text-white">{{ $progress['percentage'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $progress['percentage'] }}%"></div>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-400 mt-2">
                                    <span>Tersimpan</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $progress['checked'] }} dari {{ $progress['target'] }} ceklis</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Mulai Menilai -->
                        <a href="{{ route('wola.groups.assessments.index', [$group, 'assessor_id' => $assessor->id]) }}"
                           class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 active:scale-[0.99] text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center justify-center space-x-2">
                            <span>Mulai Menilai Atas Nama {{ explode(' ', $assessor->employee->fullname)[0] }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                @empty
                    <div class="col-span-3 py-12 text-center text-slate-400 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-8">
                        <p class="font-bold text-base text-slate-700 dark:text-slate-300">Belum ada penilai di kelompok ini.</p>
                        <p class="text-xs mt-1">Tambahkan penilai terlebih dahulu di halaman detail kelompok.</p>
                        <a href="{{ route('wola.groups.show', [$group, 'tab' => 'assessors']) }}"
                           class="inline-flex items-center px-4 py-2 mt-4 bg-blue-600 text-white text-xs font-bold rounded-xl shadow-sm">
                            + Tambah Penilai Sekarang
                        </a>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
