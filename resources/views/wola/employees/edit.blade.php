<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('wola.employees.index') }}"
               class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Edit Data Karyawan') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Memperbarui data karyawan NRP {{ $employee->nrp }} - {{ $employee->fullname }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
                
                <form method="POST" action="{{ route('wola.employees.update', $employee) }}" enctype="multipart/form-data" class="space-y-6"
                      x-data="{
                          avatarPreview: '{{ $employee->avatar && Storage::disk('public')->exists($employee->avatar) ? Storage::url($employee->avatar) : '' }}',
                          fileChosen(event) {
                              const file = event.target.files[0];
                              if (file) {
                                  const reader = new FileReader();
                                  reader.onload = (e) => { this.avatarPreview = e.target.result; };
                                  reader.readAsDataURL(file);
                              }
                          }
                      }">
                    @csrf
                    @method('PUT')

                    <!-- Foto Avatar / Profil -->
                    <div class="flex items-center space-x-6 pb-6 border-b border-slate-100 dark:border-slate-700">
                        <div class="shrink-0">
                            <template x-if="avatarPreview">
                                <img :src="avatarPreview" class="h-20 w-20 object-cover rounded-2xl border-2 border-blue-500 shadow-sm">
                            </template>
                            <template x-if="!avatarPreview">
                                <div class="h-20 w-20 rounded-2xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-xl border border-blue-200 dark:border-blue-800">
                                    {{ strtoupper(substr($employee->fullname, 0, 2)) }}
                                </div>
                            </template>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">Ganti Foto Karyawan</label>
                            <input type="file" name="avatar" accept="image/*" @change="fileChosen"
                                   class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-1">Biarkan kosong jika tidak ingin mengubah foto. Maks 2MB.</p>
                            <x-input-error :messages="$errors->get('avatar')" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- AREA WLA (Dropdown + Opsi Buat Kelompok Baru) -->
                        @php
                            $currentArea = old('area_wla', $employee->area_wla ?: ($employee->groupEmployees->first()?->group?->name ?? ''));
                            $isPredefined = $groups->contains('name', $currentArea);
                            $initialMode = old('area_wla_custom') ? 'new' : ($isPredefined || empty($currentArea) ? 'select' : 'new');
                        @endphp
                        <div class="md:col-span-2"
                             x-data="{
                                 mode: '{{ $initialMode }}',
                                 selectedArea: '{{ $isPredefined ? $currentArea : '__NEW__' }}',
                                 customArea: '{{ old('area_wla_custom', !$isPredefined ? $currentArea : '') }}',
                                 onSelectChange() {
                                     if (this.selectedArea === '__NEW__') {
                                         this.mode = 'new';
                                         this.$nextTick(() => { this.$refs.customInput.focus(); });
                                     }
                                 },
                                 switchToSelect() {
                                     this.mode = 'select';
                                     this.selectedArea = '{{ $isPredefined ? $currentArea : '' }}';
                                     this.customArea = '';
                                 }
                             }">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    AREA WLA (Area / Kelompok Kerja)
                                </label>
                                <button type="button" x-show="mode === 'select'" @click="mode = 'new'; selectedArea = '__NEW__'; $nextTick(() => $refs.customInput.focus());"
                                        class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:text-blue-500 hover:underline">
                                    + Tambah Kelompok Baru
                                </button>
                                <button type="button" x-show="mode === 'new'" @click="switchToSelect()"
                                        class="text-[11px] font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:underline">
                                    ← Pilih dari Kelompok yang Ada
                                </button>
                            </div>

                            <!-- Pilihan Dropdown Kelompok yang Sudah Ada -->
                            <div x-show="mode === 'select'">
                                <select name="area_wla" x-model="selectedArea" @change="onSelectChange()"
                                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                    <option value="">-- Pilih AREA WLA / Kelompok Kerja --</option>
                                    @foreach($groups as $grp)
                                        <option value="{{ $grp->name }}">{{ $grp->name }} (Kelompok {{ $grp->number }})</option>
                                    @endforeach
                                    <option value="__NEW__" class="font-bold text-blue-600 dark:text-blue-400">➕ Buat Kelompok / Area WLA Baru...</option>
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Karyawan akan otomatis dimasukkan sebagai sasaran penilaian pada kelompok yang dipilih.
                                </p>
                            </div>

                            <!-- Input Teks Bebas jika Menambah Kelompok Baru -->
                            <div x-show="mode === 'new'" style="display: none;" class="space-y-1.5">
                                <div class="relative">
                                    <input type="text" name="area_wla_custom" x-ref="customInput" x-model="customArea"
                                           placeholder="Ketik nama Kelompok / Area WLA baru"
                                           class="w-full pl-4 pr-10 py-2.5 bg-slate-50 dark:bg-slate-900 border-2 border-blue-500 rounded-xl text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition">
                                    <button type="button" @click="switchToSelect()" title="Batalkan & kembali ke pilihan dropdown"
                                            class="absolute right-2.5 top-2.5 p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
                                        ✕
                                    </button>
                                </div>
                                <p class="text-[11px] text-blue-600 dark:text-blue-400 font-medium">
                                    Kelompok baru ini akan otomatis dibuat di sistem WOLA beserta 10 jabatan standar WOLA.
                                </p>
                            </div>

                            <x-input-error :messages="$errors->get('area_wla')" class="mt-1.5" />
                            <x-input-error :messages="$errors->get('area_wla_custom')" class="mt-1.5" />
                        </div>

                        <!-- EMPLOYEENO (NRP) -->
                        <div>
                            <label for="nrp" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                EMPLOYEENO (NRP) <span class="text-rose-500">*</span>
                            </label>
                            <input id="nrp" type="text" name="nrp" value="{{ old('nrp', $employee->nrp) }}" required
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <x-input-error :messages="$errors->get('nrp')" class="mt-1.5" />
                        </div>

                        <!-- FULLNAME -->
                        <div>
                            <label for="fullname" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                FULLNAME (Nama Lengkap) <span class="text-rose-500">*</span>
                            </label>
                            <input id="fullname" type="text" name="fullname" value="{{ old('fullname', $employee->fullname) }}" required
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <x-input-error :messages="$errors->get('fullname')" class="mt-1.5" />
                        </div>

                        <!-- DIVISION -->
                        <div>
                            <label for="division" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                DIVISION (Divisi)
                            </label>
                            <input id="division" type="text" name="division" value="{{ old('division', $employee->division ?: $employee->section) }}" list="division-list"
                                   placeholder="Contoh: SALES & MARKETING DIV"
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <datalist id="division-list">
                                @foreach($divisions as $div)
                                    <option value="{{ $div }}"></option>
                                @endforeach
                            </datalist>
                            <x-input-error :messages="$errors->get('division')" class="mt-1.5" />
                        </div>

                        <!-- DEPT -->
                        <div>
                            <label for="dept" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                DEPT (Departemen) <span class="text-rose-500">*</span>
                            </label>
                            <input id="dept" type="text" name="dept" value="{{ old('dept', $employee->dept) }}" required list="dept-list"
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <datalist id="dept-list">
                                @foreach($depts as $d)
                                    <option value="{{ $d }}"></option>
                                @endforeach
                            </datalist>
                            <x-input-error :messages="$errors->get('dept')" class="mt-1.5" />
                        </div>

                        <!-- PLANT -->
                        <div class="md:col-span-2">
                            <label for="plant" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                PLANT (Lokasi) <span class="text-rose-500">*</span>
                            </label>
                            <input id="plant" type="text" name="plant" value="{{ old('plant', $employee->plant) }}" required list="plant-list"
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <datalist id="plant-list">
                                @foreach($plants as $p)
                                    <option value="{{ $p }}"></option>
                                @endforeach
                                <option value="CIKARANG"></option>
                                <option value="KARAWANG"></option>
                                <option value="Plant 1 - Cikarang"></option>
                                <option value="Plant 2 - Karawang"></option>
                            </datalist>
                            <x-input-error :messages="$errors->get('plant')" class="mt-1.5" />
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <a href="{{ route('wola.employees.index') }}"
                           class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition flex items-center space-x-2">
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
</x-app-layout>
