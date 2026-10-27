<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Fallback direct static asset handlers
Route::get('/logo.png', function () {
    $path = public_path('logo.png');
    return file_exists($path) ? response()->file($path, ['Content-Type' => 'image/png']) : abort(404);
});
Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');
    return file_exists($path) ? response()->file($path, ['Content-Type' => 'image/x-icon']) : abort(404);
});
Route::get('/{filename}.png', function ($filename) {
    if (in_array($filename, ['favicon-16x16', 'favicon-32x32', 'favicon-48x48', 'apple-touch-icon', 'android-chrome-192x192', 'gambarLogin'])) {
        $path = public_path($filename . '.png');
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'image/png']);
        }
    }
    abort(404);
})->where('filename', '^[a-zA-Z0-9_\-]+$');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole(['superadmin', 'admin'])) {
        return redirect()->route('wola.admin.dashboard');
    } elseif ($user->hasRole('ketua')) {
        return redirect()->route('wola.ketua.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/wola.php';
