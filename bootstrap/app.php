<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $storagePath = '/tmp/storage';
    foreach ([$storagePath, "$storagePath/framework", "$storagePath/framework/views", "$storagePath/framework/cache", "$storagePath/framework/sessions", "$storagePath/logs"] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    $app->useStoragePath($storagePath);

    $tmpDb = '/tmp/database.sqlite';
    $bundledDb = dirname(__DIR__) . '/database/database.sqlite';
    if (!file_exists($tmpDb) || filesize($tmpDb) === 0) {
        if (file_exists($bundledDb)) {
            @copy($bundledDb, $tmpDb);
        } else {
            @touch($tmpDb);
        }
    }
    config(['database.connections.sqlite.database' => $tmpDb]);
}

return $app;

