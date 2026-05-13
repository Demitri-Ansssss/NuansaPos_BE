<?php

// 1. Tampilkan semua error
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp');

    // TEST 1: Cek apakah APP_KEY terbaca
    if (empty(env('APP_KEY'))) {
        throw new Exception("ERROR: APP_KEY tidak ditemukan di Environment Variables Vercel!");
    }

    // TEST 2: Cek apakah ekstensi PHP untuk database ada
    if (!extension_loaded('pdo_pgsql')) {
        throw new Exception("ERROR: Ekstensi PHP 'pdo_pgsql' tidak aktif di Vercel. Laravel tidak bisa konek ke Supabase.");
    }

    // Jalankan normal jika tes di atas lewat
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    echo "<h1>Investigasi Error:</h1>";
    echo "<p style='color:red; font-size:20px;'><strong>" . $e->getMessage() . "</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}