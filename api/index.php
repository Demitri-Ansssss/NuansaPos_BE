<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// 1. Jalankan autoload
require __DIR__ . '/../vendor/autoload.php';

// 2. Inisialisasi Aplikasi Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Konfigurasi folder storage ke /tmp (Wajib untuk Vercel)
$app->useStoragePath('/tmp');

// 4. Jalankan Kernel
$kernel = $app->make(Kernel::class);

$response = $kernel->handle(    
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
