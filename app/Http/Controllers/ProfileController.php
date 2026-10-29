<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Group;
use App\Services\GoogleSheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load(['group', 'employee']);

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information and sync with Google Spreadsheet.
     */
    public function update(ProfileUpdateRequest $request, GoogleSheetService $googleSheetService): RedirectResponse
    {
        $user = $request->user();

        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldUsername = $user->username;
        $oldNrp = $user->username ?: ($user->employee?->nrp ?? '');

        $validated = $request->validated();
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // 1. Hubungkan & perbarui master data karyawan (Employee) jika terkait
        if ($user->employee_id) {
            $emp = Employee::find($user->employee_id);
            if ($emp) {
                $empData = ['fullname' => $user->name];
                if (!empty($user->username) && ctype_digit($user->username)) {
                    $conflict = Employee::where('nrp', $user->username)->where('id', '!=', $emp->id)->exists();
                    if (!$conflict) {
                        $empData['nrp'] = $user->username;
                    }
                }
                if ($user->group) {
                    $empData['area_wla'] = $user->group->name;
                }
                $emp->update($empData);
            }
        }

        // 2. Jika user adalah Ketua, sinkronkan profil penilai ketua di tabel lokal
        if ($user->hasRole('ketua') && $user->group_id) {
            $group = Group::find($user->group_id);
            $group?->syncLeaderAssessor();
        }

        // 3. Catat di Log Aktivitas
        ActivityLog::log(
            'UPDATE_PROFILE',
            "Pengguna memperbarui profil: {$user->name} ({$user->email})",
            ['user_id' => $user->id, 'role' => $user->roles->first()?->name ?? 'user']
        );

        // 4. Sinkronisasi otomatis ke Google Spreadsheet (Sheet Pengguna, tim_penilai, dll)
        $user->load(['group', 'employee']);
        $newNrp = $user->username ?: ($user->employee?->nrp ?? '');
        $roleName = $user->roles->first()?->name ?? 'ketua';
        $groupNumber = $user->group ? $user->group->number : null;
        $groupName = $user->group ? "Kelompok {$user->group->number} ({$user->group->name})" : 'Semua Area';

        $googleSheetService->cascadeUpdatePerson([
            'nrp' => $newNrp,
            'old_nrp' => $oldNrp ?: $newNrp,
            'name' => $user->name,
            'old_name' => $oldName,
            'email' => $user->email,
            'old_email' => $oldEmail,
            'role' => $roleName,
            'group_number' => $groupNumber,
            'group_name' => $groupName,
        ]);

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('sync_success', 'Profil berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
