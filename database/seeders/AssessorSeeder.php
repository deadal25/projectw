<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\Employee;
use App\Models\GroupAssessor;
use App\Models\User;

class AssessorSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($assessorsByGroup as $groupNum => $assessors) {
            $group = Group::where('number', $groupNum)->first();
            if (!$group) continue;

            foreach ($assessors as $item) {
                $emp = Employee::where('nrp', $item['nrp'])->first();
                if (!$emp) {
                    $emp = Employee::where('fullname', $item['fullname'])->first();
                }

                if (!$emp) {
                    $emp = Employee::create([
                        'area_wla' => $group->name,
                        'nrp' => $item['nrp'],
                        'fullname' => $item['fullname'],
                        'division' => $item['dept'],
                        'dept' => $item['dept'],
                        'section' => $item['section'] ?? '-',
                        'plant' => $item['plant'],
                    ]);
                }

                GroupAssessor::firstOrCreate(
                    [
                        'group_id' => $group->id,
                        'employee_id' => $emp->id,
                    ],
                    [
                        'is_leader' => $item['is_leader'],
                    ]
                );

                if ($item['is_leader']) {
                    $leaderUser = User::role('ketua')->where('group_id', $group->id)->first();
                    if ($leaderUser && empty($leaderUser->employee_id)) {
                        $leaderUser->update(['employee_id' => $emp->id]);
                    }
                }
            }
        }
    }
}
