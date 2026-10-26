<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('wola.groups.index') }}"
               class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Tambah Kelompok Proyek Baru') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Buat area penilaian beban kerja (WOLA) baru di WOLA MUSASHI.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
                
                <form method="POST" action="{{ route('wola.groups.store') }}" class="space-y-6">
                    @csrf

                    <!-- Nomor Kelompok -->
                    <div>
                        <label for="number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nomor Urut Kelompok <span class="text-rose-500">*</span>
                        </label>
                        <input id="number" type="number" name="number" value="{{ old('number', $nextNumber) }}" required min="1"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <x-input-error :messages="$errors->get('number')" class="mt-1.5" />
                    </div>

                    <!-- Nama Area Kelompok -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Area / Proyek Kelompok <span class="text-rose-500">*</span>
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                               placeholder="Contoh: Logistik & Material Control"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    <!-- Akun Login Ketua yang Ditugaskan -->
                    <div>
                        <label for="leader_user_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Akun Ketua Kelompok yang Ditugaskan <span class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <select id="leader_user_id" name="leader_user_id"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">-- Belum Ditentukan / Pilih Nanti --</option>
                            @foreach($potentialLeaders as $userKetua)
                                <option value="{{ $userKetua->id }}" {{ old('leader_user_id') == $userKetua->id ? 'selected' : '' }}>
                                    {{ $userKetua->name }} ({{ $userKetua->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('leader_user_id')" class="mt-1.5" />
                    </div>

                    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-xs text-blue-800 dark:text-blue-300 flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong>Catatan:</strong> Kelompok baru akan otomatis dilengkapi dengan 10 daftar jabatan standar awal. Anda dapat menambah, mengedit, atau menghapus jabatan, penilai, dan karyawan sasaran di halaman detail setelah kelompok dibuat.
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <a href="{{ route('wola.groups.index') }}"
                           class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Buat Kelompok</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
