<?php

// Forward HTTPS headers if behind Vercel edge proxy
if ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
}

// Enable response compression to keep payload small for Vercel Serverless
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ini_set('zlib.output_compression', '1');
}

// Copy SQLite database to /tmp if needed on Vercel
$tmpDb = '/tmp/database.sqlite';
$bundledDb = __DIR__ . '/../database/database.sqlite';
if (!file_exists($tmpDb) || filesize($tmpDb) === 0) {
    if (file_exists($bundledDb)) {
        @copy($bundledDb, $tmpDb);
    } else {
        @touch($tmpDb);
    }
}
putenv("DB_DATABASE={$tmpDb}");
$_ENV['DB_DATABASE'] = $tmpDb;
$_SERVER['DB_DATABASE'] = $tmpDb;

// Forward Vercel serverless requests to public/index.php
require __DIR__ . '/../public/index.php';

