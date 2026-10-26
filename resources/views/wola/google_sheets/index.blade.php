<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 shadow-xs">
                    <!-- Google Sheets icon -->
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Integrasi Google Spreadsheet
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Kelola data WOLA (Karyawan, Kelompok & Posisi Job) langsung melalui Google Sheets sebagai database.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ $settings['url'] }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span>Buka Google Sheets</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl 2xl:max-w-[1720px] 3xl:max-w-[1920px] w-full mx-auto px-3 sm:px-6 lg:px-8 2xl:px-10 space-y-6">

            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">✕</button>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 dark:text-rose-400">✕</button>
                </div>
            @endif

            <!-- ============================================================= -->
            <!-- 0. KARTU UTAMA: PINDAHKAN SELURUH DATABASE KE GOOGLE SPREADSHEET -->
            <!-- ============================================================= -->
            <div class="rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden"
                 style="background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #047857 100%) !important; color: #ffffff !important;">
                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="space-y-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase"
                                  style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;">
                                🚀 Solusi Pindah Database ke Spreadsheet
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-black tracking-tight" style="color: #ffffff !important;">
                                Pindahkan Seluruh Database ke Google Spreadsheet
                            </h3>
                            <p class="text-xs sm:text-sm max-w-2xl leading-relaxed" style="color: #ecfdf5 !important;">
                                Seluruh data aplikasi saat ini (381 Karyawan, 12 Kelompok, 144 Posisi Jabatan, Akun Pengguna, dan Riwayat Penilaian) sudah siap diekspor dalam 1 file master Excel multi-tab untuk dijadikan database utama di Google Spreadsheet Anda.
                            </p>
                        </div>

                        <div class="shrink-0 flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('wola.google-sheets.download-full-database') }}"
                               style="background-color: #ffffff !important; color: #065f46 !important;"
                               class="inline-flex items-center justify-center px-6 py-3.5 hover:bg-emerald-50 font-black text-xs sm:text-sm rounded-2xl shadow-lg transition transform hover:-translate-y-0.5 cursor-pointer">
                                <svg class="w-5 h-5 me-2" style="color: #059669 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Unduh Master Database (.xlsx)</span>
                            </a>
                            <a href="{{ $settings['url'] }}" target="_blank" rel="noopener noreferrer"
                               style="background-color: rgba(6, 78, 59, 0.6) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.35) !important;"
                               class="inline-flex items-center justify-center px-5 py-3.5 hover:bg-emerald-900 font-bold text-xs sm:text-sm rounded-2xl transition cursor-pointer">
                                <span>Buka Google Sheet Anda &rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3 Langkah Mudah -->
                    <div class="mt-6 pt-6 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs" style="border-top: 1px solid rgba(255, 255, 255, 0.25) !important;">
                        <div class="p-4 rounded-xl" style="background-color: rgba(0, 0, 0, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.2) !important;">
                            <span class="font-black block text-sm mb-1" style="color: #a7f3d0 !important;">Langkah 1: Unduh</span>
                            <p style="color: #ffffff !important; line-height: 1.4;">Klik tombol <strong>"Unduh Master Database (.xlsx)"</strong> di atas untuk mendapatkan berkas seluruh tabel.</p>
                        </div>
                        <div class="p-4 rounded-xl" style="background-color: rgba(0, 0, 0, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.2) !important;">
                            <span class="font-black block text-sm mb-1" style="color: #a7f3d0 !important;">Langkah 2: Buka & Impor</span>
                            <p style="color: #ffffff !important; line-height: 1.4;">Buka Google Spreadsheet Anda &rarr; Klik menu <strong>File &rarr; Impor (Import) &rarr; Upload</strong> &rarr; Masukkan file tadi.</p>
                        </div>
                        <div class="p-4 rounded-xl" style="background-color: rgba(0, 0, 0, 0.15) !important; border: 1px solid rgba(255, 255, 255, 0.2) !important;">
                            <span class="font-black block text-sm mb-1" style="color: #a7f3d0 !important;">Langkah 3: Ganti Spreadsheet</span>
                            <p style="color: #ffffff !important; line-height: 1.4;">Pilih opsi <strong>"Ganti spreadsheet (Replace)"</strong> &rarr; Klik Impor. Seluruh tabel langsung jadi dan terisi otomatis!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1. KARTU STATUS KONEKSI & INFORMASI -->
            <div class="bg-white dark:bg-slate-800 p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="w-3 h-3 rounded-full {{ $connection['ok'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                                Status Koneksi Spreadsheet: 
                                <span class="{{ $connection['ok'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                                    {{ $connection['ok'] ? 'Terhubung & Aktif' : 'Terputus / Akses Ditolak' }}
                                </span>
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ $connection['message'] }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="text-xs px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300">
                            Terakhir Sinkron: 
                            <strong class="text-slate-900 dark:text-white">
                                {{ $settings['last_synced_at'] ? \Carbon\Carbon::parse($settings['last_synced_at'])->diffForHumans() : 'Belum pernah' }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Statistik Data Saat Ini -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-5">
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Karyawan</span>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total_employees']) }}</h4>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelompok Kerja</span>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total_groups']) }}</h4>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Posisi Jabatan</span>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total_jobs']) }}</h4>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Penilaian</span>
                        <h4 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">{{ number_format($stats['total_assessments']) }}</h4>
                    </div>
                </div>
            </div>

            <!-- 2. KARTU AKSI SINKRONISASI CEPAT (4 PILIHAN SYNC & PUSH) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                
                <!-- Aksi 1: Sinkronkan Semua -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between hover:border-emerald-500 transition">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold mb-3 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Sinkronkan Semua Data
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            Menarik data dari seluruh tab Google Sheet: Karyawan, Kelompok, dan Posisi Job sekaligus.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('wola.google-sheets.sync') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="scope" value="all">
                        <button type="submit" 
                                onclick="return confirm('Apakah Anda yakin ingin menarik dan menyinkronkan seluruh data dari Google Spreadsheet?');"
                                class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/30 transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Tarik Semua Sekarang</span>
                        </button>
                    </form>
                </div>

                <!-- Aksi 2: Sinkronkan Karyawan Saja -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between hover:border-blue-500 transition">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 flex items-center justify-center font-bold mb-3 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Sinkronkan Karyawan
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            Hanya menarik dan memperbarui daftar karyawan dari tab <strong>Karyawan</strong> atau <strong>Database</strong>.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('wola.google-sheets.sync') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="scope" value="employees">
                        <button type="submit" 
                                onclick="return confirm('Tarik daftar karyawan dari Google Spreadsheet?');"
                                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/30 transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Tarik Karyawan</span>
                        </button>
                    </form>
                </div>

                <!-- Aksi 3: Sinkronkan Kelompok & Posisi Job -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between hover:border-purple-500 transition">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-400 flex items-center justify-center font-bold mb-3 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Sinkronkan Kelompok & Job
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            Memperbarui struktur 12 kelompok kerja dan daftar posisi job dinilai dari tab <strong>Kelompok</strong> & <strong>Posisi_Job</strong>.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('wola.google-sheets.sync') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="scope" value="jobs">
                        <button type="submit" 
                                onclick="return confirm('Tarik daftar kelompok dan posisi job dari Google Spreadsheet?');"
                                class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-purple-600/30 transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span>Tarik Kelompok & Job</span>
                        </button>
                    </form>
                </div>

                <!-- Aksi 4: Kirim Seluruh Hasil Penilaian (9 Kolom) -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between hover:border-amber-500 transition">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold mb-3 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                            Kirim Hasil Penilaian
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            Mengirim & menimpa data ke tab <strong>Hasil_Penilaian</strong> dengan 9 kolom lengkap (<strong>NRP Penilai</strong> & <strong>NRP Dinilai</strong>).
                        </p>
                    </div>

                    <form method="POST" action="{{ route('wola.google-sheets.push-hasil-penilaian') }}" class="mt-5">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Apakah Anda yakin ingin mengirim dan memperbarui seluruh sheet Hasil_Penilaian dengan format 9 kolom (NRP)?');"
                                class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-amber-600/30 transition flex items-center justify-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <span>Kirim ke Spreadsheet</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- 3. PENGATURAN TAUTAN SPREADSHEET & WEBHOOK DUA ARAH -->
            <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 space-y-6"
                 x-data="{
                     appsScriptUrl: '{{ old('apps_script_url', $settings['apps_script_url'] ?? '') }}',
                     testLoading: false,
                     testResult: null,
                     copied: false,
                     async testWebhook() {
                         if (!this.appsScriptUrl) {
                             alert('Silakan isi kolom URL Webhook Google Apps Script terlebih dahulu.');
                             return;
                         }
                         this.testLoading = true;
                         this.testResult = null;
                         try {
                             const res = await fetch('{{ route('wola.google-sheets.test-webhook') }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                 },
                                 body: JSON.stringify({ apps_script_url: this.appsScriptUrl })
                             });
                             const data = await res.json();
                             this.testResult = data;
                         } catch (err) {
                             this.testResult = { ok: false, message: 'Koneksi gagal: ' + err.message };
                         } finally {
                             this.testLoading = false;
                         }
                     },
                     copyScript() {
                         const code = document.getElementById('apps-script-code').innerText;
                         navigator.clipboard.writeText(code).then(() => {
                             this.copied = true;
                             setTimeout(() => this.copied = false, 3000);
                         });
                     }
                 }">
                <div>
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center space-x-2">
                            <span>⚙️ Konfigurasi Sinkronisasi Dua Arah (Baca & Tulis)</span>
                        </h3>
                        @if(!empty($settings['apps_script_url']))
                            @if(isset($webhookStatus) && $webhookStatus['ok'])
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 me-1.5 animate-pulse"></span>
                                    Webhook Otomatis Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                    ⚠️ Perlu Pengecekan Webhook
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                Webhook Belum Diatur (Hanya 1 Arah)
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Agar saat Anda <strong>menambah data karyawan di portal</strong> langsung bertambah otomatis ke <strong>Google Spreadsheet</strong>, hubungkan Webhook Apps Script di bawah.
                    </p>
                </div>

                <form method="POST" action="{{ route('wola.google-sheets.settings.update') }}" class="space-y-5">
                    @csrf
                    
                    <!-- 1. URL SPREADSHEET (BACA) -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                1. URL Berkas Google Spreadsheet (Tarik Data ke Web)
                            </label>
                            <span class="text-[11px] text-slate-400 font-mono">
                                ID: <strong class="text-slate-600 dark:text-slate-300">{{ $settings['spreadsheet_id'] }}</strong>
                            </span>
                        </div>
                        <input type="url" name="url" value="{{ old('url', $settings['url']) }}" required
                               placeholder="https://docs.google.com/spreadsheets/d/.../edit?usp=sharing"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[11px] text-slate-400 mt-1">
                            Digunakan untuk membaca data dari tab <code>Karyawan</code>, <code>Kelompok</code>, dan <code>Posisi_Job</code>.
                        </p>
                    </div>

                    <!-- 2. URL WEBHOOK APPS SCRIPT (TULIS) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            2. URL Webhook Google Apps Script (Tulis / Tambah Karyawan Otomatis)
                        </label>
                        <div class="flex gap-2">
                            <input type="url" name="apps_script_url" x-model="appsScriptUrl"
                                   placeholder="https://script.google.com/macros/s/AKfycb.../exec"
                                   class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            
                            <button type="button" @click="testWebhook()" :disabled="testLoading"
                                    class="px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 font-bold text-xs rounded-xl transition flex items-center space-x-1.5 cursor-pointer disabled:opacity-50">
                                <span x-show="!testLoading">⚡ Uji Webhook</span>
                                <span x-show="testLoading" class="animate-spin">🔄</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Dapatkan URL ini dari menu <strong>Ekstensi &rarr; Apps Script &rarr; Terapkan (Deploy)</strong> pada Google Spreadsheet Anda.
                        </p>

                        <!-- Hasil Uji Webhook Live -->
                        <div x-show="testResult" x-cloak class="mt-2.5">
                            <template x-if="testResult && testResult.ok">
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 flex items-center space-x-2">
                                    <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="testResult.message"></span>
                                </div>
                            </template>
                            <template x-if="testResult && !testResult.ok">
                                <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-xs font-bold text-rose-700 dark:text-rose-300 flex items-center space-x-2">
                                    <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span x-text="testResult.message"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <button type="submit"
                                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition cursor-pointer">
                            Simpan Pengaturan Sinkronisasi
                        </button>
                    </div>
                </form>

                <!-- PANDUAN LANGKAH MEMASANG WEBHOOK (1 MENIT) -->
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center space-x-1.5">
                            <span>📋 Panduan 1 Menit: Mengaktifkan Penulisan Otomatis ke Spreadsheet</span>
                        </h4>
                        <a href="{{ $settings['url'] }}" target="_blank"
                           class="text-xs font-bold text-blue-600 hover:text-blue-500 dark:text-blue-400 hover:underline flex items-center space-x-1">
                            <span>Buka Spreadsheet Anda &rarr;</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs mb-4">
                        <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <span class="font-black text-emerald-600 block mb-1">Langkah 1</span>
                            Buka spreadsheet &rarr; Klik menu <strong>Ekstensi (Extensions)</strong> &rarr; <strong>Apps Script</strong>.
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <span class="font-black text-emerald-600 block mb-1">Langkah 2</span>
                            Hapus kode bawaan di editor, klik tombol <strong>Salin Script</strong> di bawah, lalu paste & simpan (ikon 💾).
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <span class="font-black text-emerald-600 block mb-1">Langkah 3</span>
                            Klik <strong>Terapkan (Deploy)</strong> &rarr; <strong>Kelola Penerapan</strong> (atau <strong>Penerapan baru</strong>) &rarr; Edit & pilih <strong>Versi Baru</strong>.
                        </div>
                        <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700/60">
                            <span class="font-black text-emerald-600 block mb-1">Langkah 4</span>
                            Pastikan Akses: <strong>"Siapa saja (Anyone)"</strong> &rarr; Klik <strong>Terapkan (Deploy)</strong> &rarr; Selesai!
                        </div>
                    </div>

                    <!-- KOTAK KODE APPS SCRIPT -->
                    <div class="relative bg-slate-950 text-slate-100 rounded-xl p-4 font-mono text-[11px] leading-relaxed border border-slate-800">
                        <div class="flex items-center justify-between mb-2 pb-2 border-b border-slate-800">
                            <span class="text-slate-400 font-sans text-xs font-bold">Kode Google Apps Script (Kode.gs)</span>
                            <button type="button" @click="copyScript()"
                                    class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-sans font-bold transition flex items-center space-x-1 cursor-pointer">
                                <span x-show="!copied">📋 Salin Script</span>
                                <span x-show="copied" class="text-emerald-200">✅ Tersalin!</span>
                            </button>
                        </div>
                        <pre id="apps-script-code" class="overflow-x-auto max-h-56 scrollbar-thin">{{ $appsScriptCode }}</pre>
                    </div>
                </div>
            </div>

            <!-- 4. PANDUAN STRUKTUR TAB & UNDUH TEMPLATE SIAP PAKAI -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 space-y-5">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                        Panduan Struktur Tab & Unduh Template Data
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Anda dapat mengunduh berkas template CSV di bawah yang sudah terisi data proyek saat ini, lalu tinggal salin (*copy-paste*) ke dalam tab Google Spreadsheet Anda:
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    
                    <!-- Tab 1: Karyawan -->
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex flex-col justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                Tab 1: Karyawan
                            </span>
                            <h4 class="font-bold text-xs text-slate-800 dark:text-white mt-2">Kolom Wajib:</h4>
                            <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-1">
                                NRP, Nama_Lengkap, Departemen, Divisi, Plant, Area_WLA
                            </p>
                        </div>
                        <a href="{{ route('wola.google-sheets.template', ['type' => 'karyawan']) }}"
                           class="mt-4 w-full py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-blue-600 dark:text-blue-400 text-center transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh CSV Karyawan</span>
                        </a>
                    </div>

                    <!-- Tab 2: Kelompok -->
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex flex-col justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                Tab 2: Kelompok
                            </span>
                            <h4 class="font-bold text-xs text-slate-800 dark:text-white mt-2">Kolom Wajib:</h4>
                            <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-1">
                                Nomor_Kelompok, Nama_Kelompok, Plant
                            </p>
                        </div>
                        <a href="{{ route('wola.google-sheets.template', ['type' => 'kelompok']) }}"
                           class="mt-4 w-full py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-purple-600 dark:text-purple-400 text-center transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh CSV Kelompok</span>
                        </a>
                    </div>

                    <!-- Tab 3: Tim Penilai -->
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex flex-col justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-300">
                                Tab 3: tim_penilai
                            </span>
                            <h4 class="font-bold text-xs text-slate-800 dark:text-white mt-2">Kolom Wajib:</h4>
                            <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-1">
                                Kelompok, Nama_Kelompok, NRP_Penilai, Nama_Penilai, Peran, Dept, Plant
                            </p>
                        </div>
                        <a href="{{ route('wola.google-sheets.template', ['type' => 'tim_penilai']) }}"
                           class="mt-4 w-full py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-teal-600 dark:text-teal-400 text-center transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh CSV Tim Penilai</span>
                        </a>
                    </div>

                    <!-- Tab 4: Posisi Job -->
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex flex-col justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                Tab 4: Posisi_Job
                            </span>
                            <h4 class="font-bold text-xs text-slate-800 dark:text-white mt-2">Kolom Wajib:</h4>
                            <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-1">
                                Nomor_Kelompok, Kode_Job, Nama_Job, Target_Kuota
                            </p>
                        </div>
                        <a href="{{ route('wola.google-sheets.template', ['type' => 'posisi_job']) }}"
                           class="mt-4 w-full py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-amber-600 dark:text-amber-400 text-center transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh CSV Posisi Job</span>
                        </a>
                    </div>

                    <!-- Tab 5: Hasil Penilaian -->
                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex flex-col justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                                Tab 5: Hasil_Penilaian
                            </span>
                            <h4 class="font-bold text-xs text-slate-800 dark:text-white mt-2">Kolom Hasil (9 Kolom):</h4>
                            <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-1">
                                Tanggal, Kelompok, <strong>NRP_Penilai</strong>, Penilai, <strong>NRP_Dinilai</strong>, Karyawan, Job, Status, Catatan
                            </p>
                        </div>
                        <a href="{{ route('wola.google-sheets.template', ['type' => 'hasil_penilaian']) }}"
                           class="mt-4 w-full py-2 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-bold text-emerald-600 dark:text-emerald-400 text-center transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh CSV Hasil Evaluasi</span>
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>
