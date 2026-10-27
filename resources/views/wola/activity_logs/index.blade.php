<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3 sm:gap-4">
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                        {{ __('Log Aktivitas Sistem (Audit Trail)') }}
                    </h2>
                    @if(Auth::user()->hasRole('superadmin'))
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300 border border-purple-200 dark:border-purple-700/60 shadow-xs">
                            <svg class="w-3.5 h-3.5 me-1 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Superadmin
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Rekam jejak seluruh operasi data WOLA MUSASHI untuk akuntabilitas & audit sistem
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Tombol Bersihkan Semua Log (Khusus Superadmin) -->
                @if(Auth::user()->hasRole('superadmin') && $logs->total() > 0)
                    <button type="button"
                            @click="$dispatch('open-modal', 'delete-all-logs-modal')"
                            style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                            class="inline-flex items-center px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-md transition transform hover:-translate-y-0.5 cursor-pointer"
                            title="Hapus seluruh catatan log aktivitas">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Bersihkan Semua Log</span>
                    </button>
                @endif

                <div class="text-xs text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
                    Total Tercatat: <strong class="text-slate-800 dark:text-slate-200">{{ number_format($logs->total()) }}</strong> aktivitas
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
             selectedIds: [],
             selectAll: false,
             currentPageIds: {{ json_encode($logs->pluck('id')->values()) }},
             singleItemToDelete: null,
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
             openDeleteSingle(id, action, description) {
                 this.singleItemToDelete = { id, action, description };
                 $dispatch('open-modal', 'delete-single-log-modal');
             }
         }">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-center space-x-3 shadow-xs">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 flex items-center space-x-3 shadow-xs">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Floating / Inline Action Bar saat Ada Log yang Dicentang (Khusus Superadmin) -->
            @if(Auth::user()->hasRole('superadmin'))
                <div x-show="selectedIds.length > 0" x-cloak x-transition
                     class="bg-slate-900 dark:bg-slate-950 text-white p-4 rounded-2xl shadow-xl border border-slate-700 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <span class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-sm text-white shrink-0" x-text="selectedIds.length"></span>
                        <div>
                            <p class="font-bold text-sm text-white"><span x-text="selectedIds.length"></span> Log Aktivitas Terpilih</p>
                            <p class="text-xs text-slate-400">Log aktivitas yang dicentang siap untuk dihapus sekaligus dari riwayat audit.</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button type="button" @click="clearSelection()"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition cursor-pointer">
                            Batal Pilih
                        </button>
                        <button type="button" @click="$dispatch('open-modal', 'delete-bulk-logs-modal')"
                                style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-red-600 hover:bg-red-700 shadow-md flex items-center space-x-1.5 transition cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Filter & Pencarian Audit Trail -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <form method="GET" action="{{ route('wola.activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    
                    <!-- Search Text -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Cari Kata Kunci</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari deskripsi / aksi..."
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Filter Pengguna -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Pengguna</label>
                        <select name="user_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Pengguna</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Kelompok -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kelompok</label>
                        <select name="group_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kelompok</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>
                                    Kelompok {{ $g->number }}: {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tipe Aksi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kategori Aksi</label>
                        <select name="action" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Aksi</option>
                            @foreach($distinctActions as $act)
                                <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                                    {{ $act }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tombol Submit & Reset -->
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer">
                            Terapkan
                        </button>
                        <a href="{{ route('wola.activity-logs.index') }}"
                           class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition text-center">
                            Reset
                        </a>
                    </div>

                </form>
            </div>

            <!-- Tabel Audit Trail -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 dark:text-slate-400 font-bold">
                            <tr>
                                @if(Auth::user()->hasRole('superadmin'))
                                    <th scope="col" class="py-3 px-4 w-10 text-center">
                                        <input type="checkbox"
                                               @change="toggleSelectAll()"
                                               :checked="selectAll"
                                               class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                               title="Pilih Semua di Halaman Ini">
                                    </th>
                                @endif
                                <th scope="col" class="py-3 px-4 w-40">Waktu (WIB)</th>
                                <th scope="col" class="py-3 px-4 w-48">Pengguna</th>
                                <th scope="col" class="py-3 px-3 w-40">Kategori Aksi</th>
                                <th scope="col" class="py-3 px-4">Deskripsi Aktivitas</th>
                                <th scope="col" class="py-3 px-3 w-36">Area Kelompok</th>
                                <th scope="col" class="py-3 px-3 w-32 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60 bg-white dark:bg-slate-800"
                               x-data="{ activeModal: null }">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition">
                                    
                                    <!-- Checkbox (Khusus Superadmin) -->
                                    @if(Auth::user()->hasRole('superadmin'))
                                        <td class="py-3 px-4 text-center">
                                            <input type="checkbox"
                                                   :value="{{ $log->id }}"
                                                   x-model="selectedIds"
                                                   @change="updateSelectAllState()"
                                                   class="rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        </td>
                                    @endif

                                    <!-- Waktu -->
                                    <td class="py-3 px-4 whitespace-nowrap text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                                        {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '-' }}
                                    </td>

                                    <!-- Pengguna -->
                                    <td class="py-3 px-4">
                                        @if($log->user)
                                            <div class="font-bold text-slate-900 dark:text-white line-clamp-1">
                                                {{ $log->user->name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $log->user->email }}
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Sistem Otomatis</span>
                                        @endif
                                    </td>

                                    <!-- Kategori Aksi -->
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        @php
                                            $actionColor = 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300';
                                            if (str_contains($log->action, 'employee')) {
                                                $actionColor = 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300';
                                            } elseif (str_contains($log->action, 'group')) {
                                                $actionColor = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-300';
                                            } elseif (str_contains($log->action, 'assessment')) {
                                                $actionColor = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300';
                                            } elseif (str_contains($log->action, 'user')) {
                                                $actionColor = 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300';
                                            } elseif (str_contains($log->action, 'auth')) {
                                                $actionColor = 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300';
                                            } elseif (str_contains($log->action, 'PURGE') || str_contains($log->action, 'DELETE')) {
                                                $actionColor = 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300';
                                            }
                                        @endphp
                                        <span class="px-2.5 py-1 text-[10px] font-mono font-bold rounded-lg uppercase tracking-wide {{ $actionColor }}">
                                            {{ $log->action }}
                                        </span>
                                    </td>

                                    <!-- Deskripsi Aktivitas -->
                                    <td class="py-3 px-4 text-slate-800 dark:text-slate-200">
                                        {{ $log->description }}
                                    </td>

                                    <!-- Kelompok -->
                                    <td class="py-3 px-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                        @if($log->group)
                                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ Str::limit($log->group->name, 22) }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Tombol Aksi (Detail Metadata + Hapus Satuan Khusus Superadmin) -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center justify-center space-x-1.5">
                                            @if($log->properties && count($log->properties) > 0)
                                                <button type="button" @click="activeModal = {{ $log->id }}"
                                                        class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 text-[10px] font-bold transition cursor-pointer"
                                                        title="Lihat Detail Metadata">
                                                    <span>Metadata</span>
                                                    <svg class="w-3 h-3 ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </button>

                                                <!-- Modal Metadata JSON -->
                                                <div x-show="activeModal === {{ $log->id }}"
                                                     x-cloak
                                                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                                                    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 text-left shadow-2xl border border-slate-200 dark:border-slate-700"
                                                         @click.away="activeModal = null">
                                                        <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-700">
                                                            <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                                                                Detail Metadata Log #{{ $log->id }}
                                                            </h4>
                                                            <button @click="activeModal = null" class="text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
                                                        </div>
                                                        <div class="mt-4">
                                                            <div class="text-xs text-slate-500 mb-2 font-mono">
                                                                Aksi: {{ $log->action }} • Waktu: {{ $log->created_at->format('Y-m-d H:i:s') }}
                                                            </div>
                                                            <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl text-xs font-mono overflow-x-auto max-h-72">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                        </div>
                                                        <div class="mt-5 text-right">
                                                            <button type="button" @click="activeModal = null"
                                                                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl cursor-pointer">
                                                                Tutup
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Tombol Hapus Satuan (Khusus Superadmin) -->
                                            @if(Auth::user()->hasRole('superadmin'))
                                                <button type="button"
                                                        @click="openDeleteSingle({{ $log->id }}, '{{ addslashes($log->action) }}', '{{ addslashes(Str::limit($log->description, 70)) }}')"
                                                        class="inline-flex items-center px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-600 dark:hover:text-white text-[10px] font-bold transition cursor-pointer"
                                                        title="Hapus log ini">
                                                    <svg class="w-3 h-3 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif

                                            @if(!Auth::user()->hasRole('superadmin') && (!$log->properties || count($log->properties) === 0))
                                                <span class="text-slate-300 dark:text-slate-600">-</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole('superadmin') ? '7' : '6' }}" class="py-12 text-center text-slate-400">
                                        Tidak ada catatan aktivitas sistem yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($logs->hasPages())
                    <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>

        @if(Auth::user()->hasRole('superadmin'))
            <!-- Modal Konfirmasi Hapus Satuan Log -->
            <x-modal name="delete-single-log-modal" focusable>
                <div class="p-6">
                    <div class="flex items-center space-x-3 text-rose-600 dark:text-rose-400 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hapus Log Aktivitas?</h3>
                            <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">Konfirmasi penghapusan catatan log aktivitas sistem</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700/60 text-xs space-y-2 mb-6">
                        <div>
                            <span class="text-slate-400 font-medium">ID Log:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="'#' + (singleItemToDelete?.id || '')"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium">Kategori Aksi:</span>
                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400" x-text="singleItemToDelete?.action || ''"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-medium">Deskripsi:</span>
                            <p class="text-slate-700 dark:text-slate-300 mt-0.5" x-text="singleItemToDelete?.description || ''"></p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-400 mb-6">
                        Apakah Anda yakin ingin menghapus catatan log ini? Tindakan ini bersifat permanen dan tidak dapat dibatalkan.
                    </p>

                    <form :action="'{{ url('/wola/activity-logs') }}/' + (singleItemToDelete?.id || '')" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" @click="$dispatch('close-modal', 'delete-single-log-modal')"
                                    class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                                    class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Ya, Hapus Log Ini</span>
                            </button>
                        </div>
                    </form>
                </div>
            </x-modal>

            <!-- Modal Konfirmasi Hapus Log Terpilih (Bulk Delete) -->
            <x-modal name="delete-bulk-logs-modal" focusable>
                <div class="p-6">
                    <div class="flex items-center space-x-3 text-rose-600 dark:text-rose-400 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Hapus Log Aktivitas Terpilih?</h3>
                            <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">Konfirmasi penghapusan data log yang Anda centang</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-300 space-y-2 mb-6">
                        <p class="font-semibold">
                            Anda akan menghapus <span class="font-black text-rose-950 dark:text-rose-100 text-sm" x-text="selectedIds.length + ' catatan aktivitas'"></span> terpilih.
                        </p>
                        <ul class="list-disc list-inside space-y-1 ps-1 text-[11px] text-rose-700 dark:text-rose-400">
                            <li>Data riwayat aktivitas yang dicentang akan dihapus permanen dari sistem.</li>
                            <li>Tindakan ini tidak dapat dibatalkan setelah dieksekusi.</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('wola.activity-logs.destroy.bulk') }}">
                        @csrf
                        @method('DELETE')

                        <!-- Hidden inputs for selected IDs -->
                        <input type="hidden" name="selected_ids_str" :value="selectedIds.join(',')">
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="selected_ids[]" :value="id">
                        </template>

                        <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" @click="$dispatch('close-modal', 'delete-bulk-logs-modal')"
                                    class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                                    class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Ya, Hapus (<span x-text="selectedIds.length"></span>) Log Terpilih</span>
                            </button>
                        </div>
                    </form>
                </div>
            </x-modal>

            <!-- Modal Konfirmasi Hapus Semua Log (Bersihkan Semua Log) -->
            <x-modal name="delete-all-logs-modal" focusable>
                <div class="p-6" x-data="{ confirmWord: '' }">
                    <div class="flex items-center space-x-3 text-rose-600 dark:text-rose-400 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Bersihkan Seluruh Log Aktivitas?</h3>
                            <p class="text-xs text-rose-600 dark:text-rose-400 font-semibold mt-0.5">PERINGATAN: Tindakan ini permanen dan menghapus seluruh riwayat!</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-300 space-y-2 mb-5">
                        <p class="font-semibold">Menghapus seluruh catatan ({{ number_format($logs->total()) }} log aktivitas) akan:</p>
                        <ul class="list-disc list-inside space-y-1 ps-1 text-[11px] text-rose-700 dark:text-rose-400">
                            <li>Mengosongkan seluruh tabel catatan audit trail sistem.</li>
                            <li>Menghapus semua jejak histori operasi data yang pernah dicatat sebelumnya.</li>
                            <li>Sebuah log audit baru khusus (PURGE_ACTIVITY_LOGS) akan dibuat untuk mencatat bahwa Anda telah membersihkan riwayat ini.</li>
                        </ul>
                    </div>

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Ketik kata <strong class="text-rose-600 dark:text-rose-400 font-mono">HAPUS</strong> di bawah ini untuk konfirmasi:
                        </label>
                        <input type="text" x-model="confirmWord" placeholder="Ketik HAPUS"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                    </div>

                    <form method="POST" action="{{ route('wola.activity-logs.destroy.all') }}">
                        @csrf
                        @method('DELETE')

                        <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                            <button type="button" @click="$dispatch('close-modal', 'delete-all-logs-modal'); confirmWord = ''"
                                    class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    :disabled="confirmWord !== 'HAPUS'"
                                    style="background-color: #dc2626 !important; color: #ffffff !important; border: 1px solid #b91c1c !important;"
                                    class="px-5 py-2.5 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #ffffff !important;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Ya, Bersihkan Semua Log</span>
                            </button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endif
    </div>
</x-app-layout>
