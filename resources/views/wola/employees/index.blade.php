<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Master Karyawan') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola data seluruh karyawan MUSASHi
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Tombol Export Excel -->
                <a href="{{ route('wola.employees.export', request()->query()) }}"
                   class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Excel
                </a>

                <!-- Tombol Buka Modal Import -->
                <button type="button" @click="$dispatch('open-modal', 'import-employee-modal')"
                        class="inline-flex items-center px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-sm border border-slate-700 transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Excel / CSV
                </button>

                <!-- Tombol Sinkronisasi Google Sheets (Khusus Superadmin) -->
                @if(Auth::user()->hasRole('superadmin'))
                    <a href="{{ route('wola.google-sheets.index') }}"
                       class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5"
                       title="Tarik dan sinkronkan data dari Google Spreadsheet">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Sync Google Sheets</span>
                    </a>
                @endif

                <!-- Tombol Hapus per Kategori (Area / Divisi / Dept) -->
                @if(Auth::user()->hasRole(['superadmin', 'admin']) && $totalEmployees > 0)
                    <button type="button" @click="openCategoryModal('area_wla', '')"
                            class="inline-flex items-center px-4 py-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                            title="Hapus seluruh data karyawan dalam satu Area, Divisi, atau Dept">
                        <svg class="w-4 h-4 me-1.5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Hapus per Kategori</span>
                    </button>
                @endif

                <!-- Tombol Hapus Semua Karyawan (Khusus Admin/Superadmin) -->
                @if(Auth::user()->hasRole(['superadmin', 'admin']) && $totalEmployees > 0)
                    <button type="button" @click="$dispatch('open-modal', 'delete-all-employees-modal')"
                            style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                            class="inline-flex items-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-md transition"
                            title="Hapus seluruh data karyawan master">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Hapus Semua</span>
                    </button>
                @endif

                <!-- Tombol Tambah Karyawan -->
                <a href="{{ route('wola.employees.create') }}"
                   class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/30 transition">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Karyawan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
             selectedIds: [],
             selectAll: false,
             currentPageIds: {{ json_encode($employees->pluck('id')->values()) }},
             toggleSelectAll() {
                 if (this.selectAll) {
                     this.selectedIds = [...new Set([...this.selectedIds, ...this.currentPageIds])];
                 } else {
                     this.selectedIds = this.selectedIds.filter(id => !this.currentPageIds.includes(id));
                 }
             },
             updateSelectAllState() {
                 this.selectAll = this.currentPageIds.length > 0 && this.currentPageIds.every(id => this.selectedIds.includes(id));
             },
             clearSelection() {
                 this.selectedIds = [];
                 this.selectAll = false;
             },
             categoryType: 'area_wla',
             selectedArea: '',
             selectedDivision: '',
             selectedDept: '',
             areaCounts: {{ json_encode($areaCounts) }},
             divisionCounts: {{ json_encode($divisionCounts) }},
             deptCounts: {{ json_encode($deptCounts) }},
             openCategoryModal(type = 'area_wla', val = '') {
                 this.categoryType = type;
                 if (type === 'area_wla') this.selectedArea = val;
                 if (type === 'division') this.selectedDivision = val;
                 if (type === 'dept') this.selectedDept = val;
                 $dispatch('open-modal', 'delete-category-modal');
             },
             get selectedCategoryValue() {
                 if (this.categoryType === 'area_wla') return this.selectedArea;
                 if (this.categoryType === 'division') return this.selectedDivision;
                 if (this.categoryType === 'dept') return this.selectedDept;
                 return '';
             },
             getCategoryCount() {
                 const val = this.selectedCategoryValue;
                 if (!val) return 0;
                 if (this.categoryType === 'area_wla') return this.areaCounts[val] || 0;
                 if (this.categoryType === 'division') return this.divisionCounts[val] || 0;
                 if (this.categoryType === 'dept') return this.deptCounts[val] || 0;
                 return 0;
             }
         }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- 4 Kartu Ringkasan Statistik Master Karyawan (Sejajar 4 Kolom) -->
            <style>
                @media (min-width: 640px) {
                    .stat-cards-grid {
                        display: grid !important;
                        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                    }
                }
            </style>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-4 gap-4 stat-cards-grid">
                <!-- 1. Total Karyawan -->
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">Total Karyawan</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ number_format($totalEmployees) }}</p>
                    </div>
                </div>

                <!-- 2. Area WLA -->
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">AREA WLA</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ $totalAreas }} Area</p>
                    </div>
                </div>

                <!-- 3. Dept -->
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">DEPT</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ $totalDepts }} Dept</p>
                    </div>
                </div>

                <!-- 4. Plant -->
                <div class="bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center space-x-3.5 sm:space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">PLANT</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white truncate">{{ $totalPlants }} Plant</p>
                    </div>
                </div>
            </div>

            <!-- Panel Filter & Pencarian Cepat -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <form method="GET" action="{{ route('wola.employees.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    
                    <!-- Input Cari EMPLOYEENO / FULLNAME -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Cari EMPLOYEENO / FULLNAME</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="Contoh: 653 atau ISNANTO..."
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                            @if($search)
                                <a href="{{ route('wola.employees.index', array_filter(['area_wla' => $areaWla, 'division' => $division, 'dept' => $dept, 'plant' => $plant])) }}"
                                   class="absolute right-3 top-2.5 text-xs text-slate-400 hover:text-slate-600">✕</a>
                            @endif
                        </div>
                    </div>

                    <!-- Filter AREA WLA -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">AREA WLA</label>
                        <select name="area_wla" onchange="this.form.submit()"
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Semua AREA WLA</option>
                            @foreach($areas as $a)
                                @php
                                    $matchedGroup = isset($groups) ? $groups->firstWhere('name', $a) : null;
                                @endphp
                                <option value="{{ $a }}" {{ $areaWla === $a ? 'selected' : '' }}>
                                    {{ $matchedGroup ? 'Kelompok ' . $matchedGroup->number . ': ' . $a : $a }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter DIVISION -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">DIVISION</label>
                        <select name="division" onchange="this.form.submit()"
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Semua DIVISION</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div }}" {{ $division === $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter DEPT -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">DEPT</label>
                        <select name="dept" onchange="this.form.submit()"
                                class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Semua DEPT</option>
                            @foreach($depts as $d)
                                <option value="{{ $d }}" {{ $dept === $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter PLANT -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">PLANT</label>
                        <div class="flex space-x-2">
                            <select name="plant" onchange="this.form.submit()"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="">Semua PLANT</option>
                                @foreach($plants as $p)
                                    <option value="{{ $p }}" {{ $plant === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>

                            @if($search || $areaWla || $division || $dept || $plant)
                                <a href="{{ route('wola.employees.index') }}"
                                   class="px-3 py-2.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition flex items-center shrink-0"
                                   title="Reset Filter">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>

                </form>
            </div>

            <!-- Banner Aksi Cepat Hapus Filter Aktif -->
            @if(Auth::user()->hasRole(['superadmin', 'admin']) && ($areaWla || $dept || $division))
                @php
                    $activeFilterType = $areaWla ? 'area_wla' : ($dept ? 'dept' : 'division');
                    $activeFilterValue = $areaWla ?: ($dept ?: $division);
                    $activeFilterLabel = $areaWla ? 'Area WLA' : ($dept ? 'Departemen' : 'Divisi');
                    $activeFilterCount = $employees->total();
                @endphp
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900 dark:text-amber-200 shadow-2xs">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <span class="font-bold">Filter Kategori Aktif:</span>
                            @if($areaWla) <span class="bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 px-2 py-0.5 rounded-md font-bold">Area: {{ $areaWla }}</span> @endif
                            @if($division) <span class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300 px-2 py-0.5 rounded-md font-bold">Divisi: {{ $division }}</span> @endif
                            @if($dept) <span class="bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300 px-2 py-0.5 rounded-md font-bold">Dept: {{ $dept }}</span> @endif
                            <span class="text-slate-500 dark:text-slate-400 ms-1">({{ number_format($activeFilterCount) }} karyawan ditemukan)</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button type="button" @click="$dispatch('open-modal', 'delete-active-filter-modal')"
                                style="background-color: #dc2626 !important; color: #ffffff !important;"
                                class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-red-600 hover:bg-red-700 text-white shadow-xs transition flex items-center space-x-1 cursor-pointer"
                                title="Hapus seluruh karyawan pada {{ $activeFilterLabel }} {{ $activeFilterValue }}">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus Seluruh {{ $activeFilterLabel }} Ini</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Floating Action Bar saat Ada Karyawan yang Dicentang -->
            <div x-show="selectedIds.length > 0" x-transition
                 class="bg-slate-900 dark:bg-slate-950 text-white p-4 rounded-2xl shadow-xl border border-slate-700 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <span class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-sm text-white shrink-0" x-text="selectedIds.length"></span>
                    <div>
                        <p class="font-bold text-sm text-white"><span x-text="selectedIds.length"></span> Karyawan Terpilih</p>
                        <p class="text-xs text-slate-400">Karyawan yang dicentang siap untuk dihapus sekaligus dari database.</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 shrink-0">
                    <button type="button" @click="clearSelection()"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                        Batal Pilih
                    </button>
                    <button type="button" @click="$dispatch('open-modal', 'delete-bulk-modal')"
                            style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 shadow-md flex items-center space-x-1.5 transition cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Data Karyawan (Sesuai Kolom Data Sales & Marketing (2W & 4W.xlsx) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                @if(Auth::user()->hasRole(['superadmin', 'admin']))
                                    <th class="py-3.5 px-3 w-10 text-center">
                                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()"
                                               class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 cursor-pointer"
                                               title="Pilih Semua di Halaman Ini">
                                    </th>
                                @endif
                                <th class="py-3.5 px-4">AREA WLA</th>
                                <th class="py-3.5 px-4 font-mono">EMPLOYEENO</th>
                                <th class="py-3.5 px-4">FULLNAME</th>
                                <th class="py-3.5 px-4">DIVISION</th>
                                <th class="py-3.5 px-4">DEPT</th>
                                <th class="py-3.5 px-4">PLANT</th>
                                <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                            @forelse($employees as $employee)
                                @php
                                    $displayArea = $employee->area_wla ?: ($employee->groupEmployees->first()?->group?->name ?? 'Belum Ditugaskan');
                                    $displayDivision = $employee->division ?: $employee->section;
                                @endphp
                                <tr class="transition"
                                    :class="(selectedIds.includes({{ $employee->id }}) || selectedIds.includes('{{ $employee->id }}')) ? 'bg-blue-50/80 dark:bg-blue-950/50' : 'hover:bg-blue-50/40 dark:hover:bg-slate-900/40'">

                                    @if(Auth::user()->hasRole(['superadmin', 'admin']))
                                        <!-- Checkbox Pilihan -->
                                        <td class="py-3 px-3 text-center">
                                            <input type="checkbox" value="{{ $employee->id }}" x-model="selectedIds" @change="updateSelectAllState()"
                                                   class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600 cursor-pointer">
                                        </td>
                                    @endif

                                    <!-- AREA WLA -->
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800">
                                            {{ $displayArea }}
                                        </span>
                                    </td>

                                    <!-- EMPLOYEENO -->
                                    <td class="py-3 px-4 font-mono font-bold text-blue-600 dark:text-blue-400">
                                        {{ $employee->nrp }}
                                    </td>

                                    <!-- FULLNAME -->
                                    <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                        {{ $employee->fullname }}
                                    </td>

                                    <!-- DIVISION -->
                                    <td class="py-3 px-4 font-medium text-slate-700 dark:text-slate-300">
                                        {{ $displayDivision }}
                                    </td>

                                    <!-- DEPT -->
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                        {{ $employee->dept }}
                                    </td>

                                    <!-- PLANT -->
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                            {{ str_contains(strtoupper($employee->plant), 'CIKARANG') || str_contains($employee->plant, '1') ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300' }}">
                                            {{ $employee->plant }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <!-- Edit -->
                                            <a href="{{ route('wola.employees.edit', $employee) }}"
                                               style="background-color: #2563eb !important; color: #ffffff !important; border: 1px solid #1d4ed8 !important;"
                                               class="p-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition flex items-center justify-center cursor-pointer"
                                               title="Edit Karyawan">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </a>

                                            <!-- Hapus -->
                                            <form method="POST" action="{{ route('wola.employees.destroy', $employee) }}"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data karyawan {{ $employee->fullname }} (NRP {{ $employee->nrp }})?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                                                        class="p-2 rounded-xl bg-red-600 hover:bg-red-700 text-white shadow-sm transition flex items-center justify-center cursor-pointer"
                                                        title="Hapus Karyawan">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole(['superadmin', 'admin']) ? 8 : 7 }}" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                        <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p class="font-semibold text-slate-700 dark:text-slate-300">Tidak ada data karyawan ditemukan.</p>
                                        <p class="text-xs mt-1">Coba sesuaikan kata kunci pencarian atau bersihkan filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($employees->hasPages())
                    <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal Import Excel/CSV dengan Preview -->
    <x-modal name="import-employee-modal" focusable>
        <div class="p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700 mb-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Import Data Karyawan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Upload file Excel (.xlsx, .xls) atau CSV (.csv) untuk di-preview sebelum disimpan.</p>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'import-employee-modal')" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <!-- Petunjuk Format & Download Template -->
            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 mb-5">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-blue-900 dark:text-blue-200 uppercase tracking-wider">Format Kolom Spreadsheet:</h4>
                        <p class="text-xs text-blue-800 dark:text-blue-300 font-mono font-bold mt-1">AREA WLA, EMPLOYEENO, FULLNAME, DIVISION, DEPT, PLANT</p>
                        <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-0.5">Kompatibel penuh dengan format berkas Data Sales & Marketing (2W & 4W).xlsx</p>
                    </div>
                    <a href="{{ route('wola.employees.template') }}"
                       class="inline-flex items-center text-xs font-bold text-blue-700 dark:text-blue-300 hover:underline shrink-0">
                        <svg class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Template
                    </a>
                </div>
            </div>

            <!-- Form Upload File ke Preview -->
            <form method="POST" action="{{ route('wola.employees.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-5">
                    <label class="block text-xs font-semibold uppercase text-slate-700 dark:text-slate-300 mb-2">Pilih File Spreadsheet</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer border border-slate-300 dark:border-slate-700 rounded-xl p-2 bg-slate-50 dark:bg-slate-900">
                    <p class="text-[11px] text-slate-500 mt-1.5">Maksimal ukuran file: 5 MB.</p>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="$dispatch('close-modal', 'import-employee-modal')"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-md transition flex items-center space-x-1.5">
                        <span>Lanjut ke Preview Data</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal Konfirmasi Hapus Semua Karyawan -->
    <x-modal name="delete-all-employees-modal" focusable>
        <div class="p-6">
            <div class="flex items-center space-x-3 text-rose-600 dark:text-rose-400 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hapus Seluruh Data Karyawan?</h3>
                    <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">PERINGATAN: Tindakan ini permanen dan tidak dapat dibatalkan!</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-300 space-y-2 mb-6">
                <p class="font-semibold">Menghapus seluruh karyawan ({{ number_format($totalEmployees) }} orang) akan:</p>
                <ul class="list-disc list-inside space-y-1 ps-1 text-[11px] text-rose-700 dark:text-rose-400">
                    <li>Menghapus semua data master karyawan di sistem.</li>
                    <li>Melepaskan seluruh data penugasan karyawan pada kelompok kerja.</li>
                    <li>Menghapus rekaman matriks penilaian WOLA yang bertaut pada karyawan.</li>
                    <li>Menghapus foto profil karyawan yang tersimpan di storage.</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('wola.employees.destroy.all') }}">
                @csrf
                @method('DELETE')

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="$dispatch('close-modal', 'delete-all-employees-modal')"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                            style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                            class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Ya, Hapus Semua Karyawan</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal Konfirmasi Hapus Karyawan Terpilih (Bulk Delete) -->
    <x-modal name="delete-bulk-modal" focusable>
        <div class="p-6">
            <div class="flex items-center space-x-3 text-rose-600 dark:text-rose-400 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hapus Karyawan Terpilih?</h3>
                    <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">Konfirmasi penghapusan data karyawan yang Anda centang</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-300 space-y-2 mb-6">
                <p class="font-semibold">
                    Anda akan menghapus <span class="font-black text-rose-950 dark:text-rose-100 text-sm" x-text="selectedIds.length + ' orang karyawan'"></span> terpilih.
                </p>
                <ul class="list-disc list-inside space-y-1 ps-1 text-[11px] text-rose-700 dark:text-rose-400">
                    <li>Data profil dan identitas master karyawan terpilih akan dihapus permanen.</li>
                    <li>Penugasan pada kelompok kerja dan matriks riwayat penilaian WOLA karyawan terkait akan dibersihkan.</li>
                    <li>Foto profil karyawan terkait di storage publik akan dihapus.</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('wola.employees.destroy.bulk') }}">
                @csrf
                @method('DELETE')

                <!-- Hidden inputs for each selected employee ID -->
                <input type="hidden" name="selected_ids_str" :value="selectedIds.join(',')">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="selected_ids[]" :value="id">
                </template>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="$dispatch('close-modal', 'delete-bulk-modal')"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                            style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                            class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Ya, Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal Konfirmasi Hapus Langsung Filter Aktif (1-Klik Tanpa Memilih Dropdown) -->
    @if(Auth::user()->hasRole(['superadmin', 'admin']) && ($areaWla || $dept || $division))
        <x-modal name="delete-active-filter-modal" focusable>
            <div class="p-6">
                <div class="flex items-center space-x-3 text-rose-600 dark:text-rose-400 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            Hapus Seluruh Karyawan {{ $activeFilterLabel }}?
                        </h3>
                        <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">
                            Konfirmasi penghapusan {{ $activeFilterLabel }}: "{{ $activeFilterValue }}"
                        </p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-300 space-y-2 mb-6">
                    <p class="font-semibold">
                        Anda akan menghapus seluruh data <strong class="text-rose-950 dark:text-rose-100 text-sm font-extrabold">{{ number_format($activeFilterCount) }} orang karyawan</strong> yang berada dalam {{ $activeFilterLabel }}:
                        <strong class="underline decoration-rose-500 font-bold">"{{ $activeFilterValue }}"</strong>.
                    </p>
                    <ul class="list-disc list-inside space-y-1 ps-1 text-[11px] text-rose-700 dark:text-rose-400">
                        <li>Semua data master karyawan terkait akan dihapus permanen dari sistem.</li>
                        <li>Seluruh penugasan pada kelompok kerja dan rekaman riwayat penilaian WOLA yang bertaut akan dibersihkan.</li>
                        <li>Foto profil yang tersimpan di storage publik akan dihapus.</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('wola.employees.destroy.category') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="category_type" value="{{ $activeFilterType }}">
                    <input type="hidden" name="category_value" value="{{ $activeFilterValue }}">

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" @click="$dispatch('close-modal', 'delete-active-filter-modal')"
                                class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                                style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                                class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Ya, Hapus Semua ({{ number_format($activeFilterCount) }} Karyawan)</span>
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    @endif

    <!-- Modal Konfirmasi Hapus Berdasarkan Kategori (Area / Divisi / Dept) -->
    <x-modal name="delete-category-modal" focusable>
        <div class="p-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700 mb-5">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Hapus Karyawan per Kategori</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pilih kategori Area WLA, Divisi, atau Dept untuk menghapus seluruh anggotanya sekaligus.</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close-modal', 'delete-category-modal')" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
            </div>

            <form method="POST" action="{{ route('wola.employees.destroy.category') }}" class="space-y-5">
                @csrf
                @method('DELETE')

                <!-- 1. Pilihan Tipe Kategori -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        1. Pilih Tipe Kategori
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button"
                                @click="categoryType = 'area_wla'"
                                :class="categoryType === 'area_wla' ? 'bg-blue-600 text-white font-bold border-blue-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                                class="p-2.5 rounded-xl border text-xs font-semibold text-center transition flex flex-col items-center justify-center">
                            <span>Area WLA</span>
                            <span class="text-[10px] opacity-80">(Kelompok)</span>
                        </button>

                        <button type="button"
                                @click="categoryType = 'division'"
                                :class="categoryType === 'division' ? 'bg-blue-600 text-white font-bold border-blue-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                                class="p-2.5 rounded-xl border text-xs font-semibold text-center transition flex flex-col items-center justify-center">
                            <span>Divisi</span>
                            <span class="text-[10px] opacity-80">(Division)</span>
                        </button>

                        <button type="button"
                                @click="categoryType = 'dept'"
                                :class="categoryType === 'dept' ? 'bg-blue-600 text-white font-bold border-blue-600 shadow-sm' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100'"
                                class="p-2.5 rounded-xl border text-xs font-semibold text-center transition flex flex-col items-center justify-center">
                            <span>Departemen</span>
                            <span class="text-[10px] opacity-80">(DEPT)</span>
                        </button>
                    </div>
                    <input type="hidden" name="category_type" :value="categoryType">
                    <input type="hidden" name="category_value" :value="selectedCategoryValue">
                </div>

                <!-- 2. Pilihan Nilai Kategori yang Ingin Dihapus -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        2. Pilih <span x-text="categoryType === 'area_wla' ? 'Area WLA' : (categoryType === 'division' ? 'Divisi' : 'Departemen')"></span> yang Ingin Dihapus
                    </label>

                    <!-- Pilihan jika Area WLA -->
                    <div x-show="categoryType === 'area_wla'">
                        <select x-model="selectedArea"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                            <option value="">-- Pilih Area WLA yang Ingin Dihapus --</option>
                            @foreach($areas as $a)
                                @php $matchedGroup = isset($groups) ? $groups->firstWhere('name', $a) : null; @endphp
                                <option value="{{ $a }}">
                                    {{ $matchedGroup ? 'Kelompok ' . $matchedGroup->number . ': ' . $a : $a }} ({{ $areaCounts[$a] ?? 0 }} karyawan)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilihan jika Divisi -->
                    <div x-show="categoryType === 'division'">
                        <select x-model="selectedDivision"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                            <option value="">-- Pilih Divisi yang Ingin Dihapus --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div }}">
                                    {{ $div }} ({{ $divisionCounts[$div] ?? 0 }} karyawan)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pilihan jika Departemen -->
                    <div x-show="categoryType === 'dept'">
                        <select x-model="selectedDept"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                            <option value="">-- Pilih Departemen yang Ingin Dihapus --</option>
                            @foreach($depts as $d)
                                <option value="{{ $d }}">
                                    {{ $d }} ({{ $deptCounts[$d] ?? 0 }} karyawan)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 3. Kotak Konfirmasi & Peringatan Risiko -->
                <div x-show="selectedCategoryValue" class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-300 space-y-2">
                    <p class="font-bold flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Konfirmasi Penghapusan Kategori:</span>
                    </p>
                    <p>
                        Anda akan menghapus seluruh data karyawan (<strong class="text-rose-950 dark:text-rose-100 text-sm font-extrabold" x-text="getCategoryCount() + ' orang'"></strong>) yang berada dalam
                        <span class="font-bold" x-text="categoryType === 'area_wla' ? 'Area WLA' : (categoryType === 'division' ? 'Divisi' : 'Departemen')"></span>:
                        "<strong class="font-bold" x-text="selectedCategoryValue"></strong>".
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-700 dark:text-rose-400 ps-1">
                        <li>Data master seluruh karyawan tersebut akan dihapus permanen.</li>
                        <li>Seluruh penugasan pada kelompok kerja dan rekaman riwayat penilaian WOLA yang bertaut akan dibersihkan.</li>
                        <li>Foto profil yang tersimpan di storage akan dihapus.</li>
                    </ul>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="$dispatch('close-modal', 'delete-category-modal')"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                            :disabled="!selectedCategoryValue || getCategoryCount() === 0"
                            style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                            class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Ya, Hapus (<span x-text="getCategoryCount()"></span> Karyawan)</span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>
