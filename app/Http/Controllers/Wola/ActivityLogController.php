<?php

namespace App\Http\Controllers\Wola;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan riwayat audit trail / log aktivitas sistem
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'group'])->orderByDesc('id');

        // Filter Aksi
        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        // Filter Pengguna
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter Kelompok
        if ($groupId = $request->input('group_id')) {
            $query->where('group_id', $groupId);
        }

        // Filter Rentang Tanggal
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Pencarian Teks
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Data pendukung untuk dropdown filter
        $users = User::orderBy('name')->get();
        $groups = Group::orderBy('number')->get();
        $distinctActions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('wola.activity_logs.index', compact(
            'logs',
            'users',
            'groups',
            'distinctActions'
        ));
    }

    /**
     * Hapus satu data catatan log aktivitas sistem (Khusus Superadmin)
     */
    public function destroy(ActivityLog $activityLog)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Hanya Superadmin yang memiliki hak akses untuk menghapus log aktivitas sistem.');
        }

        $id = $activityLog->id;
        $action = $activityLog->action;

        $activityLog->delete();

        return redirect()->back()->with('success', "Log aktivitas berhasil dihapus.");
    }

    /**
     * Hapus beberapa log aktivitas terpilih sekaligus (Khusus Superadmin - Checkbox Bulk Delete)
     */
    public function destroyBulk(Request $request)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Hanya Superadmin yang memiliki hak akses untuk menghapus log aktivitas sistem.');
        }

        $raw = $request->input('selected_ids');
        if (empty($raw) && $request->filled('selected_ids_str')) {
            $raw = explode(',', $request->input('selected_ids_str'));
        } elseif (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        $ids = array_values(array_filter(array_map('intval', (array) $raw)));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu catatan aktivitas yang ingin dihapus.');
        }

        $count = ActivityLog::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', "Sebanyak {$count} catatan aktivitas berhasil dihapus.");
    }

    /**
     * Hapus semua log aktivitas sistem (Khusus Superadmin - Purge All Logs)
     */
    public function destroyAll(Request $request)
    {
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Hanya Superadmin yang memiliki hak akses untuk menghapus log aktivitas sistem.');
        }

        $total = ActivityLog::count();
        if ($total === 0) {
            return redirect()->back()->with('error', 'Tidak ada data log aktivitas untuk dibersihkan.');
        }

        ActivityLog::query()->delete();

        return redirect()->back()->with('success', "Seluruh catatan log aktivitas berhasil dibersihkan.");
    }
}
