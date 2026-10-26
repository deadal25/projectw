<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Group;
use App\Models\GroupAssessor;
use App\Models\GroupEmployee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Penilai per Kelompok sesuai spesifikasi (K = Ketua)
        $assessorsByGroup = [
            1 => [
                ['nrp' => '622', 'fullname' => 'Nanang Siswanto', 'dept' => 'Production Control', 'section' => 'Capacity Plan', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '974', 'fullname' => 'Dodi Solehudin', 'dept' => 'Production Control', 'section' => 'Capacity Plan', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
                ['nrp' => '521', 'fullname' => 'Kasimin', 'dept' => 'Production Control', 'section' => 'Capacity Plan', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            2 => [
                ['nrp' => '545', 'fullname' => 'Agung Wahyu Wibowo', 'dept' => 'Inventory Control', 'section' => 'New Model', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '628', 'fullname' => 'Andi Purwanto', 'dept' => 'Inventory Control', 'section' => 'New Model', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
                ['nrp' => '549', 'fullname' => 'Mulyadi', 'dept' => 'Inventory Control', 'section' => 'New Model', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            3 => [
                ['nrp' => '548', 'fullname' => 'Heri Eko Purnomo', 'dept' => 'Engineering', 'section' => 'Die Engineering', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '364', 'fullname' => 'Supiyadi', 'dept' => 'Engineering', 'section' => 'Die Engineering', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
                ['nrp' => '462', 'fullname' => 'Aan Hardiyanto', 'dept' => 'Engineering', 'section' => 'Die Engineering', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            4 => [
                ['nrp' => '740', 'fullname' => 'Joko Pramono', 'dept' => 'Machining', 'section' => 'M/ME 1', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '571', 'fullname' => 'Tugiman', 'dept' => 'Machining', 'section' => 'M/ME 1', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
                ['nrp' => '518', 'fullname' => 'Robin Dwi Wiyono', 'dept' => 'Machining', 'section' => 'M/ME 1', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            5 => [
                ['nrp' => '683', 'fullname' => 'Muhamad Setiyawan', 'dept' => 'Machining', 'section' => 'M/ME 2', 'plant' => 'Plant 2 - Karawang', 'is_leader' => true],
                ['nrp' => '838', 'fullname' => 'Agus Taufiq', 'dept' => 'Machining', 'section' => 'M/ME 2', 'plant' => 'Plant 2 - Karawang', 'is_leader' => false],
                ['nrp' => '744', 'fullname' => 'Sugiharto', 'dept' => 'Machining', 'section' => 'M/ME 2', 'plant' => 'Plant 2 - Karawang', 'is_leader' => false],
                ['nrp' => '856', 'fullname' => 'Dian Susalit', 'dept' => 'Machining', 'section' => 'M/ME 2', 'plant' => 'Plant 2 - Karawang', 'is_leader' => false],
            ],
            6 => [
                ['nrp' => '276', 'fullname' => 'Joko Hartono', 'dept' => 'Engineering', 'section' => 'Product Engineering', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '422', 'fullname' => 'Nur Rohman', 'dept' => 'Engineering', 'section' => 'Product Engineering', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
                ['nrp' => '1003', 'fullname' => 'Rahmat Saleh', 'dept' => 'Engineering', 'section' => 'Product Engineering', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            7 => [
                ['nrp' => '620', 'fullname' => 'Arif Dwi Saputro', 'dept' => 'Quality Assurance', 'section' => 'QA Ckr', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '521B', 'fullname' => 'Sulistyo (Verifikasi NRP)', 'dept' => 'Quality Assurance', 'section' => 'QA Ckr', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            8 => [
                ['nrp' => '318', 'fullname' => 'Zulianto', 'dept' => 'Quality Assurance', 'section' => 'QA Krw', 'plant' => 'Plant 2 - Karawang', 'is_leader' => true],
                ['nrp' => '589', 'fullname' => 'Heru Suryono', 'dept' => 'Quality Assurance', 'section' => 'QA Krw', 'plant' => 'Plant 2 - Karawang', 'is_leader' => false],
                ['nrp' => '583', 'fullname' => 'Budi Sulistyawan', 'dept' => 'Quality Assurance', 'section' => 'QA Krw', 'plant' => 'Plant 2 - Karawang', 'is_leader' => false],
            ],
            9 => [
                ['nrp' => '671', 'fullname' => 'Eko Prasetyo', 'dept' => 'Procurement', 'section' => 'New Business', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '427', 'fullname' => 'Triyatno', 'dept' => 'Procurement', 'section' => 'New Business', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            10 => [
                ['nrp' => '861', 'fullname' => 'Ricky Apriliyanto', 'dept' => 'Human Resources', 'section' => 'HR & GA', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '660', 'fullname' => 'Sugeng Hariyanto', 'dept' => 'General Affairs', 'section' => 'HR & GA', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            11 => [
                ['nrp' => '1390', 'fullname' => 'Yayat Sutrisno', 'dept' => 'Finance & Accounting', 'section' => 'System', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '666', 'fullname' => 'Arif Nugroho', 'dept' => 'Finance & Accounting', 'section' => 'System', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
            12 => [
                ['nrp' => '624', 'fullname' => 'Sariyanto', 'dept' => 'Sales & Marketing', 'section' => '2W & 4W', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => true],
                ['nrp' => '910', 'fullname' => 'Wahyu Febriyanto', 'dept' => 'Sales & Marketing', 'section' => '2W & 4W', 'plant' => 'Plant 1 - Cikarang', 'is_leader' => false],
            ],
        ];

        foreach ($assessorsByGroup as $groupNumber => $assessors) {
            $group = Group::where('number', $groupNumber)->first();
            if (!$group) continue;

            foreach ($assessors as $item) {
                $employee = Employee::firstOrCreate(
                    ['nrp' => $item['nrp']],
                    [
                        'fullname' => $item['fullname'],
                        'dept' => $item['dept'],
                        'section' => $item['section'],
                        'plant' => $item['plant'],
                    ]
                );

                GroupAssessor::firstOrCreate(
                    [
                        'group_id' => $group->id,
                        'employee_id' => $employee->id,
                    ],
                    [
                        'is_leader' => $item['is_leader'],
                    ]
                );
            }
        }

        // 2. 28 Karyawan Dummy untuk Kelompok 1 (NRP 1101 – 1128)
        $group1 = Group::where('number', 1)->first();
        $sampleNames = [
            'Ahmad Fauzi', 'Budi Santoso', 'Candra Wijaya', 'Dedi Kusnadi',
            'Eko Supriyadi', 'Fajar Ramadhan', 'Gunawan Prasetyo', 'Hendra Kurniawan',
            'Indra Permana', 'Joko Susilo', 'Kurniawan Dwi', 'Lukman Hakim',
            'Mohamad Rizki', 'Novan Pratama', 'Oki Setiawan', 'Panji Nugroho',
            'Qori Maulana', 'Rian Hidayat', 'Surya Saputra', 'Taufik Hidayat',
            'Untung Subagyo', 'Viko Hermansyah', 'Wahyu Tri', 'Yanuar Putra',
            'Zainal Arifin', 'Agung Nugroho', 'Bagus Triyanto', 'Dimas Pangestu'
        ];

        for ($i = 0; $i < 28; $i++) {
            $nrp = (string)(1101 + $i);
            $fullname = $sampleNames[$i] ?? "Karyawan $nrp";

            $employee = Employee::firstOrCreate(
                ['nrp' => $nrp],
                [
                    'fullname' => $fullname,
                    'dept' => 'Production Control',
                    'section' => 'Capacity Plan',
                    'plant' => 'Plant 1 - Cikarang',
                ]
            );

            if ($group1) {
                GroupEmployee::firstOrCreate([
                    'group_id' => $group1->id,
                    'employee_id' => $employee->id,
                ]);
            }
        }

        // 3. Tambahkan beberapa karyawan target untuk kelompok 2 - 12 (opsional, sebagai data demo dinamis)
        for ($g = 2; $g <= 12; $g++) {
            $group = Group::where('number', $g)->first();
            if (!$group) continue;

            for ($k = 1; $k <= 28; $k++) {
                $nrp = (string)($g * 1000 + $k);
                $employee = Employee::firstOrCreate(
                    ['nrp' => $nrp],
                    [
                        'fullname' => "Karyawan Sample Area {$group->name} #$k",
                        'dept' => 'Manufacturing',
                        'section' => $group->name,
                        'plant' => ($g % 2 === 0) ? 'Plant 2 - Karawang' : 'Plant 1 - Cikarang',
                    ]
                );

                GroupEmployee::firstOrCreate([
                    'group_id' => $group->id,
                    'employee_id' => $employee->id,
                ]);
            }
        }
    }
}
