<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\RiskTrigger\SimpulanTl;

echo "=== TEST ISOLASI CABANG RA FASE 4 ===\n\n";

$ra = User::where('role', 'ra')->first();
echo "Testing RA: {$ra->name} (Cabang ID: {$ra->cabang_id})\n";

// Cari SimpulanTl yang ada
$simTl = SimpulanTl::first();
echo "Simpulan TL Unit: {$simTl->kode_unit} ({$simTl->visit_id})\n";

// Buat akun RA dummy cabang lain
$otherRa = new User();
$otherRa->name = 'RA Cabang Lain';
$otherRa->email = 'ra_lain@banksulteng.co.id';
$otherRa->role = 'ra';
$otherRa->cabang_id = 999;

echo "Testing akses RA Cabang Lain ke Simpulan TL Cabang {$simTl->kode_unit}\n";

\Illuminate\Support\Facades\Auth::login($otherRa);
$req = Illuminate\Http\Request::create("/risk-trigger/simpulan-tl/{$simTl->id}/edit", 'GET');
$req->setUserResolver(fn() => $otherRa);

try {
    $res = $kernel->handle($req);
    echo "Status Code: " . $res->getStatusCode() . "\n";
    assert($res->getStatusCode() === 403, "Akses cabang lain oleh RA HARUS 403 Forbidden");
    echo "BERHASIL: Akses ditolak dengan kode 403 Forbidden!\n";
} catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
    echo "Caught expected HTTP Exception: " . $e->getStatusCode() . " - " . $e->getMessage() . "\n";
    assert($e->getStatusCode() === 403, "Status harus 403");
    echo "BERHASIL: Akses ditolak dengan HTTP 403 Forbidden!\n";
}
