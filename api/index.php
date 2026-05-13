<?php

// 1. Nyalakan laporan error agar muncul di browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // 2. Jalankan autoload
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception('File vendor/autoload.php tidak ditemukan. Pastikan composer install berhasil.');
    }
    require __DIR__ . '/../vendor/autoload.php';

    // 3. Inisialisasi Aplikasi Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // 4. Konfigurasi folder storage ke /tmp
    $app->useStoragePath('/tmp');

    // 5. Jalankan Request
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    // Jika ada error, tampilkan di layar
    echo "<h1>Terjadi Error:</h1>";
    echo "<p><strong>Pesan:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . " pada baris " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
