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
                    {{ __('Preview Import Data Karyawan') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Periksa validitas data spreadsheet sebelum disimpan ke sistem database.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6">

            <!-- Ringkasan Hasil Validasi File -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Baris</p>
                    <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ count($previewData) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Data terbaca dari file</p>
                </div>

                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Siap Simpan (Baru)</p>
                    <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ $validCount }}</p>
                    <p class="text-xs text-emerald-600/80 mt-1">NRP belum terdaftar</p>
                </div>

                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">Duplikat NRP</p>
                    <p class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">{{ $duplicateCount }}</p>
                    <p class="text-xs text-amber-600/80 mt-1">NRP sudah ada di master</p>
                </div>

                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">Tidak Valid / Kosong</p>
                    <p class="text-3xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">{{ $invalidCount }}</p>
                    <p class="text-xs text-rose-600/80 mt-1">Akan otomatis dilewati</p>
                </div>
            </div>

            <!-- Form Konfirmasi Simpan -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <form method="POST" action="{{ route('wola.employees.import.confirm') }}">
                    @csrf

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-slate-700">
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white text-base">Opsi Penanganan Data Duplikat:</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tentukan perlakuan terhadap {{ $duplicateCount }} data yang NRP-nya sudah ada.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="action_type" value="insert_only" checked
                                       class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 focus:ring-blue-500">
                                <span class="ms-2 text-xs font-semibold text-slate-700 dark:text-slate-300">Hanya Simpan Data Baru (NRP sama tidak dimasukkan ganda)</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="action_type" value="upsert"
                                       class="w-4 h-4 text-blue-600 bg-slate-100 border-slate-300 focus:ring-blue-500">
                                <span class="ms-2 text-xs font-semibold text-slate-700 dark:text-slate-300">Update Data yang Duplikat</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tabel Pratinjau Baris -->
                    <div class="overflow-x-auto my-6 rounded-xl border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 uppercase tracking-wider font-bold">
                                    <th class="py-3 px-3 w-14 text-center">Baris</th>
                                    <th class="py-3 px-3">Status</th>
                                    <th class="py-3 px-3">AREA WLA</th>
                                    <th class="py-3 px-3 font-mono">EMPLOYEENO</th>
                                    <th class="py-3 px-3">FULLNAME</th>
                                    <th class="py-3 px-3">DIVISION</th>
                                    <th class="py-3 px-3">DEPT</th>
                                    <th class="py-3 px-3">PLANT</th>
                                    <th class="py-3 px-3">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                                @foreach($previewData as $item)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40">
                                        <td class="py-2.5 px-3 text-center text-slate-400 font-mono">{{ $item['row_number'] }}</td>
                                        <td class="py-2.5 px-3">
                                            @if($item['status'] === 'VALID')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                                    ✓ Siap Simpan
                                                </span>
                                            @elseif($item['status'] === 'DUPLICATE')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                    ⚠ Duplikat
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300">
                                                    ✕ Error
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80">
                                                {{ $item['area_wla'] ?: 'Otomatis' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-blue-600 dark:text-blue-400">{{ $item['nrp'] ?: '-' }}</td>
                                        <td class="py-2.5 px-3 font-semibold text-slate-900 dark:text-white">{{ $item['fullname'] ?: '-' }}</td>
                                        <td class="py-2.5 px-3 font-medium text-slate-700 dark:text-slate-300">{{ $item['division'] ?: '-' }}</td>
                                        <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">{{ $item['dept'] ?: '-' }}</td>
                                        <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">{{ $item['plant'] ?: '-' }}</td>
                                        <td class="py-2.5 px-3 text-slate-400 italic">
                                            @if($item['errors'])
                                                <span class="text-rose-500 font-semibold not-italic">{{ $item['errors'] }}</span>
                                            @elseif($item['status'] === 'DUPLICATE')
                                                EMPLOYEENO sudah ada di master
                                            @else
                                                Data valid
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Tombol Konfirmasi -->
                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('wola.employees.index') }}"
                           class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                            Batal & Kembali
                        </a>

                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Konfirmasi & Simpan ke Database</span>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
