<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
{
    use HandlesAuthorization;

    /**
     * Superadmin & Admin can do anything
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['superadmin', 'admin'])) {
            return true;
        }

        return null;
    }

    /**
     * Siapa yang boleh melihat daftar seluruh kelompok
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin']);
    }

    /**
     * Hanya Ketua kelompok yang bersangkutan yang boleh melihat detail/menilai kelompoknya
     */
    public function view(User $user, Group $group): bool
    {
        return $user->hasRole('ketua') && (int)$user->group_id === (int)$group->id;
    }

    /**
     * Otorisasi pengisian penilaian
     */
    public function assess(User $user, Group $group): bool
    {
        return $user->hasRole('ketua') && (int)$user->group_id === (int)$group->id;
    }

    /**
     * Otorisasi mengelola jabatan di kelompok
     */
    public function manageJobs(User $user, Group $group): bool
    {
        return $user->hasRole('ketua') && (int)$user->group_id === (int)$group->id;
    }

    /**
     * Otorisasi mengelola karyawan di kelompok
     */
    public function manageEmployees(User $user, Group $group): bool
    {
        return $user->hasRole('ketua') && (int)$user->group_id === (int)$group->id;
    }

    /**
     * Hanya Superadmin & Admin yang bisa membuat kelompok baru
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin']);
    }

    /**
     * Hanya Superadmin & Admin yang bisa mengubah nama/nomor kelompok
     */
    public function update(User $user, Group $group): bool
    {
        return $user->hasRole(['superadmin', 'admin']);
    }

    /**
     * Hanya Superadmin yang bisa menghapus kelompok
     */
    public function delete(User $user, Group $group): bool
    {
        return $user->hasRole('superadmin');
    }
}
