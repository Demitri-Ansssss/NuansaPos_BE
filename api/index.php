<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// 1. Paksa tampilkan error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // 2. Jalankan autoload
    require __DIR__ . '/../vendor/autoload.php';

    // 3. Inisialisasi Aplikasi Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // 4. Konfigurasi folder storage ke /tmp
    $app->useStoragePath('/tmp');

    // 5. Jalankan Kernel
    $kernel = $app->make(Kernel::class);

    $response = $kernel->handle(
        $request = Request::capture()
    );

    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    // Tampilkan detail error jika terjadi kegagalan
    echo "<h2>Laravel Error:</h2>";
    echo "<p><strong>Pesan:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . " baris " . $e->getLine() . "</p>";
    echo "<hr><pre>" . $e->getTraceAsString() . "</pre>";
}