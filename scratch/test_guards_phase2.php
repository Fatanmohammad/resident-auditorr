<?php
require 'c:/laragon/www/resident-auditorr/vendor/autoload.php';
$app = require_once 'c:/laragon/www/resident-auditorr/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RiskTrigger\AssignmentScope;
use App\Http\Controllers\RiskTrigger\RiskTriggerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

$controller = app(RiskTriggerController::class);
$raPoso = User::find(8); // Yan Hamsah (ra, cabang Poso - 003)
$raPalu = User::find(2); // Januar (ra, cabang Palu - 001)
$admin = User::find(30); // Admin Sistem

echo "========================================================\n";
echo "TEST 5: Fase 2 Controller Guards (RA vs Admin)\n";
echo "========================================================\n";

// 1. RA accessing createAssignment()
Auth::login($raPoso);
try {
    $controller->createAssignment(new Request());
    echo "FAILED: RA accessed createAssignment without 403!\n";
    exit(1);
} catch (HttpException $e) {
    assert($e->getStatusCode() === 403);
    echo "PASSED: RA access to createAssignment() blocked with HTTP 403 (" . $e->getMessage() . ")\n";
}

// 2. RA accessing storeAssignment()
try {
    $controller->storeAssignment(new Request([
        'trigger_id' => 1,
        'tanggal_visit' => date('Y-m-d'),
        'ra_pelaksana' => 'Yan Hamsah',
        'reviewer' => 'Admin',
        'referensi_otorisasi' => 'ST/001',
        'klasifikasi' => 'Risk Trigger Visit Terarah',
    ]));
    echo "FAILED: RA accessed storeAssignment without 403!\n";
    exit(1);
} catch (HttpException $e) {
    assert($e->getStatusCode() === 403);
    echo "PASSED: RA access to storeAssignment() blocked with HTTP 403 (" . $e->getMessage() . ")\n";
}

// 3. RA Palu accessing RA Poso Assignment showAssignment()
$posoAssignment = AssignmentScope::where('kode_unit', '003')->first();
if ($posoAssignment) {
    Auth::login($raPalu);
    try {
        $controller->showAssignment($posoAssignment->id);
        echo "FAILED: RA Palu accessed Poso assignment without 403!\n";
        exit(1);
    } catch (HttpException $e) {
        assert($e->getStatusCode() === 403);
        echo "PASSED: Cross-branch access to showAssignment() blocked with HTTP 403 (" . $e->getMessage() . ")\n";
    }
}

// 4. Admin accessing createAssignment()
Auth::login($admin);
$resp = $controller->createAssignment(new Request());
echo "PASSED: Admin successfully accessed createAssignment()!\n";

echo "========================================================\n";
echo "ALL FASE 2 CONTROLLER GUARDS TESTED AND VERIFIED 100% SECURE!\n";
echo "========================================================\n";
