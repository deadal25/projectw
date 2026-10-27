<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('wola.groups.index') }}"
               class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
               title="Kembali ke Daftar Kelompok">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Edit Kelompok ' . $group->number) }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Perbarui nama area, nomor urut, ambang batas lagging, atau akun Ketua.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
                
                <form method="POST" action="{{ route('wola.groups.update', $group) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Nomor Kelompok -->
                    <div>
                        <label for="number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nomor Urut Kelompok <span class="text-rose-500">*</span>
                        </label>
                        <input id="number" type="number" name="number" value="{{ old('number', $group->number) }}" required min="1"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <x-input-error :messages="$errors->get('number')" class="mt-1.5" />
                    </div>

                    <!-- Nama Area Kelompok -->
                    <div>
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Area / Proyek Kelompok <span class="text-rose-500">*</span>
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name', $group->name) }}" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    <!-- Akun Login Ketua yang Ditugaskan -->
                    <div>
                        <label for="leader_user_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Akun Login Ketua yang Ditugaskan
                        </label>
                        <select id="leader_user_id" name="leader_user_id"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">-- Belum Ditentukan / Kosongkan --</option>
                            @foreach($potentialLeaders as $userKetua)
                                <option value="{{ $userKetua->id }}" {{ ($group->leader && $group->leader->id == $userKetua->id) || ($userKetua->group_id == $group->id) ? 'selected' : '' }}>
                                    {{ $userKetua->name }} ({{ $userKetua->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('leader_user_id')" class="mt-1.5" />
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
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>

                </form>

                @if(auth()->user()->hasRole(['superadmin', 'admin']))
                    <div class="mt-8 pt-6 border-t border-rose-100 dark:border-rose-950/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h4 class="text-xs font-bold text-rose-600 dark:text-rose-400">Hapus Kelompok Ini</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Hapus permanen area kelompok ini beserta seluruh data jabatan, penilai, dan checklistnya.</p>
                        </div>
                        <form method="POST" action="{{ route('wola.groups.destroy', $group) }}"
                              onsubmit="let confirmWord = prompt('Apakah Anda yakin ingin menghapus Kelompok {{ $group->number }} ({{ addslashes($group->name) }})?\n\nKetik kata HAPUS untuk mengonfirmasi:'); if (!confirmWord || confirmWord.trim().toUpperCase() !== 'HAPUS') { alert('Penghapusan dibatalkan. Anda wajib mengetik kata HAPUS.'); return false; } document.getElementById('edit-delete-confirm-input').value = confirmWord.trim().toUpperCase();">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="confirmation_text" id="edit-delete-confirm-input" value="">
                            <button type="submit"
                                    class="px-4 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 font-bold text-xs rounded-xl border border-rose-200 dark:border-rose-800 transition flex items-center space-x-1.5 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus Kelompok</span>
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
