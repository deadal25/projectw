<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\GoogleSheetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password and sync with Google Spreadsheet.
     */
    public function update(Request $request, GoogleSheetService $googleSheetService): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Catat di Log Aktivitas
        ActivityLog::log(
            'UPDATE_PASSWORD',
            "Pengguna memperbarui kata sandi: {$user->name} ({$user->email})",
            ['user_id' => $user->id]
        );

        // Sinkronisasi kata sandi baru ke Google Spreadsheet sheet Pengguna
        $user->load(['group', 'employee']);
        $nrp = $user->username ?: ($user->employee?->nrp ?? '');
        $roleName = $user->roles->first()?->name ?? 'ketua';
        $groupNumber = $user->group ? $user->group->number : null;
        $groupName = $user->group ? "Kelompok {$user->group->number} ({$user->group->name})" : 'Semua Area';

        $sheetRes = $googleSheetService->cascadeUpdatePerson([
            'nrp' => $nrp,
            'old_nrp' => $nrp,
            'name' => $user->name,
            'old_name' => $user->name,
            'email' => $user->email,
            'old_email' => $user->email,
            'role' => $roleName,
            'group_number' => $groupNumber,
            'group_name' => $groupName,
            'password' => $validated['password'],
        ]);

        $syncMsg = 'Kata sandi berhasil diperbarui.';
        if ($sheetRes['success'] ?? false) {
            $syncMsg .= ' 🟢 Password baru otomatis tersinkronkan ke Google Spreadsheet (sheet Pengguna)!';
        }

        return back()->with('status', 'password-updated')->with('password_sync_success', $syncMsg);
    }
}
