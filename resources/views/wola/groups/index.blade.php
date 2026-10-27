<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Manajemen Kelompok Kerja') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola area kelompok, tim penilai, daftar jabatan, serta sasaran karyawan dinilai WOLA.
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-700">
                    Total: <strong class="text-blue-600 dark:text-blue-400">{{ $groups->count() }}</strong> Area
                </span>
                <button type="button" 
                        @click="$dispatch('open-create-group-modal')"
                        class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-extrabold rounded-xl shadow-md shadow-blue-600/30 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Kelompok Baru</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        deleteConfirmationText: '',
        editGroup: { id: '', number: '', name: '', leader_id: '' },
        deleteGroup: { id: '', number: '', name: '', route: '' },
        openEdit(id, number, name, leaderId) {
            this.editGroup = {
                id: id,
                number: number,
                name: name,
                leader_id: leaderId !== null ? leaderId : ''
            };
            this.editModalOpen = true;
        },
        confirmDelete(id, number, name, routeUrl) {
            this.deleteGroup = {
                id: id,
                number: number,
                name: name,
                route: routeUrl
            };
            this.deleteConfirmationText = '';
            this.deleteModalOpen = true;
        }
    }"
    @open-create-group-modal.window="createModalOpen = true">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert Success -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-3">
                        <span class="p-1.5 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span class="text-sm font-bold">{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-sm font-bold">✕</button>
                </div>
            @endif

            <!-- Flash Alert Error -->
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-3">
                        <span class="p-1.5 rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="text-sm font-bold">{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 text-sm font-bold">✕</button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs space-y-1 shadow-xs">
                    <strong class="font-bold block text-sm mb-1">Gagal Menyimpan Data Kelompok:</strong>
                    @foreach($errors->all() as $err)
                        <div class="flex items-center space-x-1.5">
                            <span>•</span>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Grid Seluruh Kelompok -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($groups as $group)
                    @php
                        $progress = $group->getProgressData();
                        $leaderName = $group->leader ? $group->leader->name : 'Belum ditetapkan';
                        $leaderId = $group->leader ? $group->leader->id : null;
                    @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/90 dark:border-slate-700/80 shadow-sm hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition-all flex flex-col justify-between p-6">
                        <div>
                            <!-- Header Kartu: Badge Nomor & Status -->
                            <div class="flex items-center justify-between mb-3.5">
                                <span class="px-3 py-1 rounded-xl text-xs font-mono font-black bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800">
                                    Kelompok {{ $group->number }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase
                                    @if($progress['status_color'] === 'green') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300
                                    @elseif($progress['status_color'] === 'blue') bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300
                                    @elseif($progress['status_color'] === 'yellow') bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300
                                    @elseif($progress['status_color'] === 'red') bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300
                                    @else bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 @endif">
                                    ● {{ $progress['status'] }}
                                </span>
                            </div>

                            <!-- Nama Kelompok -->
                            <h3 class="font-black text-slate-900 dark:text-white text-lg tracking-tight mb-1 line-clamp-1" title="{{ $group->name }}">
                                {{ $group->name }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Ketua:</span>
                                <strong class="text-slate-800 dark:text-slate-200 font-extrabold truncate">{{ $leaderName }}</strong>
                            </p>

                            <!-- Breakdown 3 Elemen Target -->
                            <div class="grid grid-cols-3 gap-2 py-3 border-y border-slate-100 dark:border-slate-700/60 text-center mb-4">
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Penilai</span>
                                    <span class="text-sm font-black text-slate-800 dark:text-white mt-0.5 block">{{ $progress['assessor_count'] }}</span>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Jabatan</span>
                                    <span class="text-sm font-black text-slate-800 dark:text-white mt-0.5 block">{{ $progress['job_count'] }}</span>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Karyawan</span>
                                    <span class="text-sm font-black text-slate-800 dark:text-white mt-0.5 block">{{ $progress['employee_count'] }}</span>
                                </div>
                            </div>

                            <!-- Progres Bar -->
                            <div>
                                <div class="flex justify-between text-xs mb-1.5">
                                    <span class="text-slate-500 font-medium">Capaian Progres</span>
                                    <span class="font-black text-blue-600 dark:text-blue-400">{{ $progress['percentage'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $progress['percentage'] }}%"></div>
                                </div>
                                <div class="flex justify-between text-[11px] text-slate-400 mt-1.5">
                                    <span>Target: {{ $progress['employee_count'] }} Karyawan</span>
                                    <span>{{ $progress['assessed_employees_count'] }}/{{ $progress['employee_count'] }} selesai</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi Bawah: Kelola Komponen, Edit, Hapus -->
                        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between gap-2">
                            <a href="{{ route('wola.groups.show', $group) }}"
                               class="inline-flex items-center text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 transition">
                                <span>Kelola Komponen</span>
                                <svg class="w-4 h-4 ms-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>

                            <div class="flex items-center space-x-1.5 shrink-0">
                                <!-- Tombol Edit Kelompok (Buka Modal Edit Cepat) -->
                                <button type="button"
                                        @click="openEdit({{ $group->id }}, {{ $group->number }}, '{{ addslashes($group->name) }}', {{ $leaderId !== null ? $leaderId : 'null' }})"
                                        class="p-2 rounded-xl text-slate-600 hover:text-blue-600 bg-slate-50 hover:bg-blue-50 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-600 transition cursor-pointer"
                                        title="Edit Kelompok {{ $group->number }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>

                                <!-- Tombol Hapus Kelompok (Buka Modal Konfirmasi Hapus) -->
                                @if(auth()->user()->hasRole(['superadmin', 'admin']))
                                    <button type="button"
                                            @click="confirmDelete({{ $group->id }}, {{ $group->number }}, '{{ addslashes($group->name) }}', '{{ route('wola.groups.destroy', $group) }}')"
                                            class="p-2 rounded-xl text-rose-500 hover:text-rose-700 bg-rose-50/60 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-900/60 transition cursor-pointer"
                                            title="Hapus Kelompok {{ $group->number }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-3 p-12 text-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <h4 class="font-extrabold text-slate-800 dark:text-white">Belum Ada Kelompok Kerja</h4>
                        <p class="text-xs text-slate-400 mt-1 mb-4">Klik tombol di bawah untuk menambah kelompok kerja WOLA baru.</p>
                        <button type="button" @click="createModalOpen = true"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md hover:bg-blue-500 transition">
                            Tambah Kelompok Sekarang
                        </button>
                    </div>
                @endforelse
            </div>

        </div>

        <!-- ============================================================= -->
        <!-- 1. MODAL TAMBAH KELOMPOK BARU -->
        <!-- ============================================================= -->
        <div x-show="createModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="createModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Dialog Box -->
                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700 p-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                            <div>
                                <h4 class="font-extrabold text-base text-slate-900 dark:text-white">Tambah Kelompok Kerja Baru</h4>
                                <p class="text-xs text-slate-400">Buat area kelompok kerja WOLA baru</p>
                            </div>
                        </div>
                        <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg font-bold">
                            ✕
                        </button>
                    </div>

                    <form method="POST" action="{{ route('wola.groups.store') }}" class="space-y-4 my-4">
                        @csrf
                        <div>
                            <label for="create_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Nomor Urut Kelompok <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="create_number" name="number" value="{{ $nextNumber }}" required min="1"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="create_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Nama Area / Proyek Kelompok <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="create_name" name="name" required placeholder="Contoh: Logistik & Material Control"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="create_leader" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Akun Ketua Kelompok yang Ditugaskan <span class="text-slate-400 font-normal">(Opsional)</span>
                            </label>
                            <select id="create_leader" name="leader_user_id"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Belum Ditentukan / Pilih Nanti --</option>
                                @foreach($potentialLeaders as $userKetua)
                                    <option value="{{ $userKetua->id }}">
                                        {{ $userKetua->name }} ({{ $userKetua->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-[11px] text-blue-800 dark:text-blue-300 flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Kelompok baru otomatis dilengkapi 10 daftar jabatan standar awal. Anda dapat menyesuaikannya di halaman detail komponen setelah kelompok dibuat.</span>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                            <button type="button" @click="createModalOpen = false"
                                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Simpan & Buat Kelompok</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- 2. MODAL EDIT KELOMPOK KERJA -->
        <!-- ============================================================= -->
        <div x-show="editModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-edit-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="editModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Dialog Box -->
                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700 p-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </span>
                            <div>
                                <h4 class="font-extrabold text-base text-slate-900 dark:text-white">
                                    Edit Kelompok <span x-text="editGroup.number"></span>
                                </h4>
                                <p class="text-xs text-slate-400">Perbarui informasi nomor, nama area, dan ketua</p>
                            </div>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg font-bold">
                            ✕
                        </button>
                    </div>

                    <form method="POST" :action="'{{ url('/wola/groups') }}/' + editGroup.id" class="space-y-4 my-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="edit_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Nomor Urut Kelompok <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" id="edit_number" name="number" x-model="editGroup.number" required min="1"
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="edit_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Nama Area / Proyek Kelompok <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="edit_name" name="name" x-model="editGroup.name" required
                                   class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="edit_leader" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Akun Login Ketua yang Ditugaskan
                            </label>
                            <select id="edit_leader" name="leader_user_id" x-model="editGroup.leader_id"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Belum Ditentukan / Kosongkan --</option>
                                @foreach($potentialLeaders as $userKetua)
                                    <option value="{{ $userKetua->id }}">
                                        {{ $userKetua->name }} ({{ $userKetua->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-700">
                            <button type="button" @click="editModalOpen = false"
                                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-extrabold rounded-xl shadow-md transition flex items-center space-x-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- 3. MODAL KONFIRMASI HAPUS KELOMPOK KERJA -->
        <!-- ============================================================= -->
        <div x-show="deleteModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-delete-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="deleteModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Dialog Box -->
                <div x-show="deleteModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-700 p-6">
                    
                    <div class="flex items-start space-x-3 mb-4">
                        <span class="p-2.5 rounded-2xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <div>
                            <h4 class="font-black text-base text-slate-900 dark:text-white">Hapus Kelompok Kerja?</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Apakah benar Anda ingin menghapus permanen <strong class="text-slate-800 dark:text-slate-200">Kelompok <span x-text="deleteGroup.number"></span> (<span x-text="deleteGroup.name"></span>)</strong>?
                            </p>
                        </div>
                    </div>

                    <!-- Peringatan Rinci Penghapusan Relasi Database & Spreadsheet -->
                    <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/80 text-xs text-rose-900 dark:text-rose-200 mb-4 space-y-2">
                        <p class="font-bold flex items-center space-x-1.5 text-rose-700 dark:text-rose-300">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Seluruh data terkait akan ikut terhapus di database & spreadsheet:</span>
                        </p>
                        <ul class="text-[11px] text-rose-800 dark:text-rose-300 space-y-1 ps-4 list-disc">
                            <li><strong>Tab Kelompok</strong>: Baris data kelompok ini.</li>
                            <li><strong>Tab tim_penilai</strong>: Nama Ketua dan seluruh anggota penilai kelompok.</li>
                            <li><strong>Tab Pengguna</strong>: Akun login Ketua kelompok.</li>
                            <li><strong>Tab Posisi_Job</strong>: Seluruh daftar jabatan / job di kelompok ini.</li>
                            <li><strong>Tab Hasil_Penilaian</strong>: Seluruh checklist dan riwayat penilaian karyawan terkait.</li>
                        </ul>
                    </div>

                    <!-- Input Konfirmasi Ketik 'HAPUS' -->
                    <div class="mb-5">
                        <label for="confirm_delete_input" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Untuk melanjutkan, ketik kata <span class="px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/70 text-rose-700 dark:text-rose-300 font-mono font-black text-xs border border-rose-200 dark:border-rose-800">HAPUS</span> di bawah ini:
                        </label>
                        <input type="text"
                               id="confirm_delete_input"
                               name="confirmation_text"
                               x-model="deleteConfirmationText"
                               placeholder="Ketik HAPUS di sini..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-black tracking-wider text-rose-600 dark:text-rose-400 placeholder:text-slate-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                               required
                               autocomplete="off">
                        <p class="text-[11px] text-slate-400 mt-1" x-show="deleteConfirmationText.trim().toUpperCase() !== 'HAPUS'">
                            Tombol konfirmasi akan aktif setelah Anda mengetik kata <strong>HAPUS</strong>.
                        </p>
                    </div>

                    <form method="POST" :action="deleteGroup.route" @submit="if(deleteConfirmationText.trim().toUpperCase() !== 'HAPUS') { $event.preventDefault(); return false; }" class="flex items-center justify-end space-x-2.5">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="confirmation_text" :value="deleteConfirmationText">
                        <button type="button" @click="deleteModalOpen = false"
                                class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition cursor-pointer hover:bg-slate-200 dark:hover:bg-slate-600">
                            Batal
                        </button>
                        <button type="submit"
                                :disabled="deleteConfirmationText.trim().toUpperCase() !== 'HAPUS'"
                                :class="deleteConfirmationText.trim().toUpperCase() === 'HAPUS' 
                                    ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/30 cursor-pointer opacity-100' 
                                    : 'bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 cursor-not-allowed opacity-50 shadow-none'"
                                class="px-5 py-2.5 text-xs font-extrabold rounded-xl transition flex items-center space-x-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Saya Yakin, Hapus Semua</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
