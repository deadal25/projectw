<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Group;
use App\Models\GroupAssessor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('Musashi@2026');

        // 1. Akun Superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@musashi.co.id'],
            [
                'name' => 'Super Administrator Musashi',
                'username' => 'superadmin',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $superadmin->assignRole('superadmin');

        // 2. Dua Akun Admin
        $admin1 = User::firstOrCreate(
            ['email' => 'admin1@musashi.co.id'],
            [
                'name' => 'Admin WOLA 1',
                'username' => 'admin1',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $admin1->assignRole('admin');

        $admin2 = User::firstOrCreate(
            ['email' => 'admin2@musashi.co.id'],
            [
                'name' => 'Admin WOLA 2',
                'username' => 'admin2',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $admin2->assignRole('admin');

        // 3. Dua Belas Akun Ketua (1 per Kelompok)
        for ($i = 1; $i <= 12; $i++) {
            $group = Group::where('number', $i)->first();
            if (!$group) continue;

            // Cari assessor yang is_leader = true
            $leaderAssessor = GroupAssessor::where('group_id', $group->id)
                ->where('is_leader', true)
                ->first();

            $employee = $leaderAssessor ? $leaderAssessor->employee : null;
            $name = $employee ? $employee->fullname : "Ketua Kelompok $i";
            $email = "ketua{$i}@musashi.co.id";
            $username = $employee ? "nrp_{$employee->nrp}" : "ketua{$i}";

            $ketuaUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => $defaultPassword,
                    'group_id' => $group->id,
                    'employee_id' => $employee ? $employee->id : null,
                    'is_active' => true,
                ]
            );

            $ketuaUser->assignRole('ketua');
            $group->syncLeaderAssessor();
        }
    }
}
