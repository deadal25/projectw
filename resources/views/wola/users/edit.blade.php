<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('wola.users.index') }}"
               class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Edit Akun Pengguna') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Memperbarui data akun {{ $user->name }} ({{ $user->email }})
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-8">
                
                @php
                    $currentRole = $user->getRoleNames()->first() ?? 'ketua';
                @endphp

                <form method="POST" action="{{ route('wola.users.update', $user) }}" class="space-y-6"
                      x-data="{ selectedRole: '{{ old('role', $currentRole) }}' }">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Role Akses -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Tingkat Role Akses <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach($roles as $role)
                                    <label class="relative flex items-center p-3 rounded-xl border cursor-pointer transition"
                                           :class="selectedRole === '{{ $role->name }}' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700'">
                                        <input type="radio" name="role" value="{{ $role->name }}" x-model="selectedRole" class="text-blue-600 focus:ring-blue-500">
                                        <div class="ms-3">
                                            <p class="text-xs font-bold uppercase text-slate-900 dark:text-white">{{ $role->name }}</p>
                                            <p class="text-[11px] text-slate-500">
                                                @if($role->name === 'superadmin') Kontrol penuh
                                                @elseif($role->name === 'admin') Kelola data & ketua
                                                @else Input kelompok sendiri @endif
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Nama Lengkap Akun <span class="text-rose-500">*</span>
                            </label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>

                        <!-- Password (Opsional) -->
                        <div>
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Ubah Kata Sandi (Opsional)
                            </label>
                            <input id="password" type="password" name="password"
                                   placeholder="Biarkan kosong jika tidak diubah"
                                   class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika kata sandi tidak ingin diubah.</p>
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        <!-- NRP / Username (Hanya Angka) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    NRP / Username <span class="text-xs font-normal text-slate-400 dark:text-slate-500">(Wajib Angka)</span>
                                </label>
                                <span class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/50 px-2 py-0.5 rounded-full border border-blue-200 dark:border-blue-900">
                                    Format Angka
                                </span>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                </div>
                                <input id="username" type="text" name="username" 
                                       value="{{ old('username', preg_replace('/[^0-9]/', '', $user->username ?? '')) }}"
                                       inputmode="numeric"
                                       pattern="[0-9]*"
                                       maxlength="20"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       placeholder="Contoh: 1183 (Hanya angka)"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <span>NRP wajib berupa angka murni (contoh: 1183). Karakter huruf & simbol otomatis dicegah.</span>
                            </p>
                            <x-input-error :messages="$errors->get('username')" class="mt-1.5" />
                        </div>

                        <!-- Pilihan Kelompok (Jika Role = Ketua) -->
                        <div class="md:col-span-2" x-show="selectedRole === 'ketua'">
                            <label for="group_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                Kelompok yang Dipimpin (Khusus Ketua)
                            </label>
                            <select id="group_id" name="group_id"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="">-- Pilih Kelompok Kerja --</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id', $user->group_id) == $group->id ? 'selected' : '' }}>
                                        Kelompok {{ $group->number }}: {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('group_id')" class="mt-1.5" />
                        </div>

                        <!-- Status Aktif -->
                        <div class="md:col-span-2 flex items-center pt-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 rounded focus:ring-blue-500">
                                <span class="ms-2 text-xs font-bold text-slate-700 dark:text-slate-300">Akun Aktif (Dapat Login ke Sistem)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <a href="{{ route('wola.users.index') }}"
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
