<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_WOLA_PT_Musashi_{{ $group ? 'G'.$group->number : 'Semua_Kelompok' }}_{{ date('Ymd') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            @page {
                size: A4 landscape;
                margin: 12mm 15mm 12mm 15mm;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 sm:p-8">

    <!-- Tombol Aksi Layar (Hidden Saat Cetak) -->
    <div class="max-w-6xl mx-auto mb-6 flex justify-between items-center no-print bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <div class="flex items-center space-x-2">
            <a href="{{ route('wola.reports.index') }}"
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">
                ← Kembali ke Portal
            </a>
            <span class="text-xs text-slate-500">Pratinjau Cetak / Ekspor PDF</span>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-xs rounded-lg shadow-md transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Sekarang / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Lembar Kertas Resmi Laporan -->
    <div class="max-w-6xl mx-auto bg-white p-8 sm:p-12 shadow-md print:shadow-none print:p-0 rounded-2xl print:rounded-none border border-slate-200 print:border-none">
        
        <!-- KOP SURAT RESMI WOLA MUSASHI -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex justify-between items-start">
            <div>
                <h1 class="text-xl font-black tracking-tight text-slate-900">
                    WOLA MUSASHI INDONESIA
                </h1>
                <p class="text-xs text-slate-600 font-semibold mt-0.5">
                    EJIP Industrial Park, Plot 3J-2, Cikarang Selatan, Bekasi 17550 • Jawa Barat, Indonesia
                </p>
                <p class="text-[11px] text-slate-500">
                    Human Resources & General Affairs Division • Industrial Engineering Committee
                </p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-blue-900 text-white font-mono font-bold text-xs rounded uppercase tracking-wider">
                    INTERNAL REPORT
                </span>
                <p class="text-[10px] text-slate-400 mt-1">Dicetak: {{ now()->format('d F Y, H:i') }} WIB</p>
            </div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="text-center my-6">
            <h2 class="text-lg font-black uppercase tracking-wide text-slate-900">
                LAPORAN REKAPITULASI EVALUASI BEBAN KERJA (WOLA)
            </h2>
            <p class="text-xs font-semibold text-slate-600 mt-1">
                PERIODE PENILAIAN: SEPTEMBER – DESEMBER 2026
            </p>
            @if($group)
                <p class="text-sm font-bold text-blue-900 mt-1">
                    AREA KELOMPOK PROYEK {{ $group->number }}: {{ $group->name }}
                </p>
            @else
                <p class="text-sm font-bold text-blue-900 mt-1">
                    RINGKASAN SELURUH 12 KELOMPOK KERJA WOLA MUSASHI
                </p>
            @endif
        </div>

        <!-- INFORMASI / METADATA AREA -->
        @if($group)
            <div class="grid grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Ketua Kelompok</span>
                    <strong class="text-slate-900">{{ $group->leader ? $group->leader->name : 'Belum ditetapkan' }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Target Checklist</span>
                    <strong class="text-slate-900">{{ $summary['checked'] }} / {{ $summary['target'] }} unit ({{ $summary['percentage'] }}%)</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Status Capaian</span>
                    <strong class="text-slate-900">{{ $summary['status'] }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 block text-[10px] uppercase font-bold">Komposisi Tim</span>
                    <strong class="text-slate-900">{{ $summary['assessor_count'] }} Penilai • {{ $summary['job_count'] }} Jabatan • {{ $summary['employee_count'] }} Target</strong>
                </div>
            </div>
        @endif

        <!-- TABEL REKAPITULASI -->
        <div class="overflow-x-auto my-6">
            <table class="min-w-full border-collapse border border-slate-300 text-xs text-left">
                <thead class="bg-slate-100 font-bold text-slate-800">
                    <tr>
                        <th class="border border-slate-300 py-2.5 px-3 w-12 text-center">No</th>
                        <th class="border border-slate-300 py-2.5 px-3">Area Kelompok</th>
                        <th class="border border-slate-300 py-2.5 px-3">Ketua Kelompok</th>
                        <th class="border border-slate-300 py-2.5 px-2 text-center">Penilai</th>
                        <th class="border border-slate-300 py-2.5 px-2 text-center">Jabatan</th>
                        <th class="border border-slate-300 py-2.5 px-2 text-center">Target Karyawan</th>
                        <th class="border border-slate-300 py-2.5 px-3 text-center">Target Unit</th>
                        <th class="border border-slate-300 py-2.5 px-3 text-center">Ceklis Masuk</th>
                        <th class="border border-slate-300 py-2.5 px-3 text-center">Progres (%)</th>
                        <th class="border border-slate-300 py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-300">
                    @php
                        $list = $group ? collect([$group]) : $allGroups;
                        $grandTarget = 0;
                        $grandChecked = 0;
                    @endphp
                    @foreach($list as $idx => $g)
                        @php
                            $prog = $g->getProgressData();
                            $grandTarget += $prog['target'];
                            $grandChecked += $prog['checked'];
                        @endphp
                        <tr>
                            <td class="border border-slate-300 py-2 px-3 text-center font-mono font-bold">{{ $idx + 1 }}</td>
                            <td class="border border-slate-300 py-2 px-3 font-semibold">{{ $g->name }}</td>
                            <td class="border border-slate-300 py-2 px-3">{{ $g->leader ? $g->leader->name : '-' }}</td>
                            <td class="border border-slate-300 py-2 px-2 text-center">{{ $prog['assessor_count'] }}</td>
                            <td class="border border-slate-300 py-2 px-2 text-center">{{ $prog['job_count'] }}</td>
                            <td class="border border-slate-300 py-2 px-2 text-center">{{ $prog['employee_count'] }}</td>
                            <td class="border border-slate-300 py-2 px-3 text-center font-mono">{{ number_format($prog['target']) }}</td>
                            <td class="border border-slate-300 py-2 px-3 text-center font-mono font-bold">{{ number_format($prog['checked']) }}</td>
                            <td class="border border-slate-300 py-2 px-3 text-center font-bold">{{ $prog['percentage'] }}%</td>
                            <td class="border border-slate-300 py-2 px-3 text-center font-semibold">{{ $prog['status'] }}</td>
                        </tr>
                    @endforeach
                    @if(!$group)
                        <tr class="bg-slate-100 font-bold">
                            <td colspan="6" class="border border-slate-300 py-2 px-3 text-right uppercase">Total Akumulasi Global:</td>
                            <td class="border border-slate-300 py-2 px-3 text-center font-mono">{{ number_format($grandTarget) }}</td>
                            <td class="border border-slate-300 py-2 px-3 text-center font-mono">{{ number_format($grandChecked) }}</td>
                            <td class="border border-slate-300 py-2 px-3 text-center font-mono">
                                {{ $grandTarget > 0 ? round(($grandChecked / $grandTarget) * 100, 1) : 0 }}%
                            </td>
                            <td class="border border-slate-300 py-2 px-3 text-center">-</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- CATATAN & KETERANGAN DOKUMEN -->
        <div class="mt-6 text-[10px] text-slate-500 space-y-1">
            <p><strong>Catatan Audit:</strong> Laporan ini dihasilkan secara resmi melalui Sistem PORTAL WOLA MUSASHI.</p>
        </div>

        <!-- KOLOM TANDA TANGAN PENGESAHAN DOKUMEN -->
        <div class="grid grid-cols-3 gap-6 mt-12 pt-6 text-center text-xs">
            <div>
                <p class="text-slate-600 font-semibold mb-16">Dibuat Oleh,<br><span class="text-[10px] text-slate-400">Ketua Kelompok Area Proyek</span></p>
                <div class="border-t border-slate-400 pt-1 w-48 mx-auto font-bold text-slate-900">
                    ({{ $group && $group->leader ? $group->leader->name : '....................................' }})
                </div>
            </div>

            <div>
                <p class="text-slate-600 font-semibold mb-16">Diverifikasi Oleh,<br><span class="text-[10px] text-slate-400">Section Head HRD / WOLA Admin</span></p>
                <div class="border-t border-slate-400 pt-1 w-48 mx-auto font-bold text-slate-900">
                    (....................................)
                </div>
            </div>

            <div>
                <p class="text-slate-600 font-semibold mb-16">Disetujui Oleh,<br><span class="text-[10px] text-slate-400">Division Head / Plant Manager</span></p>
                <div class="border-t border-slate-400 pt-1 w-48 mx-auto font-bold text-slate-900">
                    (....................................)
                </div>
            </div>
        </div>

    </div>

</body>
</html>
