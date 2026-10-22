<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\RiskTrigger\SimpulanTl;

echo "=== TEST HTTP ENDPOINTS FASE 4 ===\n\n";

$admin = User::where('role', 'admin')->first() ?? User::first();
$ra = User::where('role', 'ra')->first();

$simTl = SimpulanTl::first();

// 1. GET /risk-trigger/simpulan-tl as Admin
echo "1. Request GET /risk-trigger/simpulan-tl (Admin)\n";
\Illuminate\Support\Facades\Auth::login($admin);
$req1 = Illuminate\Http\Request::create('/risk-trigger/simpulan-tl', 'GET');
$req1->setUserResolver(fn() => $admin);
$res1 = $kernel->handle($req1);
echo "Status: " . $res1->getStatusCode() . "\n";
assert($res1->getStatusCode() === 200, "Admin harus 200 OK");

// 2. GET /risk-trigger/simpulan-tl/{id}/edit as Admin
echo "\n2. Request GET /risk-trigger/simpulan-tl/{$simTl->id}/edit (Admin)\n";
$req2 = Illuminate\Http\Request::create("/risk-trigger/simpulan-tl/{$simTl->id}/edit", 'GET');
$req2->setUserResolver(fn() => $admin);
$res2 = $kernel->handle($req2);
echo "Status: " . $res2->getStatusCode() . "\n";
assert($res2->getStatusCode() === 200, "Admin edit harus 200 OK");

// 3. GET /risk-trigger/simpulan-tl as RA
if ($ra) {
    echo "\n3. Request GET /risk-trigger/simpulan-tl (RA: {$ra->name})\n";
    \Illuminate\Support\Facades\Auth::login($ra);
    $req3 = Illuminate\Http\Request::create('/risk-trigger/simpulan-tl', 'GET');
    $req3->setUserResolver(fn() => $ra);
    $res3 = $kernel->handle($req3);
    echo "Status: " . $res3->getStatusCode() . "\n";
    assert($res3->getStatusCode() === 200, "RA index harus 200 OK");
}

echo "\n=== SEMUA HTTP TEST FASE 4 BERHASIL 200 OK ===\n";
