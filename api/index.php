
// Jembatan untuk menjalankan Laravel di Vercel Serverless Functions
// require __DIR__ . '/../public/index.php';

<?php

// Jalankan autoload
require __DIR__ . '/../vendor/autoload.php';

// Inisialisasi Aplikasi Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Set folder storage ke /tmp agar tidak Error 500 di Vercel
$app->useStoragePath('/tmp');

// Jalankan Request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
