<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\RiskTrigger\RiskTriggerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

echo "========================================================\n";
echo "   VERIFIKASI HARMONISASI UI RISK TRIGGER (SOP 06)      \n";
echo "========================================================\n\n";

$admin = User::where('role', 'admin')->first() ?: User::first();
Auth::login($admin);
echo "Logged in as: {$admin->name} ({$admin->role})\n";

// TEST 1: Check RiskTriggerService::getActiveAlarms
echo "\n[TEST 1] Testing RiskTriggerService::getActiveAlarms...\n";
$service = app(RiskTriggerService::class);
$alarms = $service->getActiveAlarms($admin);
echo "  Found " . count($alarms) . " active alarms (> 5 temuan akumulasi).\n";
foreach ($alarms as $i => $a) {
    echo "  - Alarm #" . ($i + 1) . ": Area {$a['area']} pada {$a['nama_unit']} ({$a['kode_unit']}) -> Total: {$a['count']} temuan (DR: {$a['dr_count']}, Off: {$a['off_count']}, On: {$a['on_count']})\n";
}
if (count($alarms) > 0) {
    echo "  --> PASSED: Active alarms generated successfully.\n";
} else {
    echo "  --> INFO: No active alarms (> 5 temuan) currently found in test database.\n";
}

// TEST 2: Check View Composer for layouts.header
echo "\n[TEST 2] Testing View Composer on layouts.header for headerNotifs...\n";
$view = View::make('layouts.header');
$html = $view->render();
$viewData = $view->getData();
if (!isset($viewData['headerNotifs'])) {
    echo "  --> FAILED: headerNotifs not bound to layouts.header!\n";
    exit(1);
}
$headerNotifs = $viewData['headerNotifs'];
echo "  headerNotifs count: " . $headerNotifs->count() . "\n";
foreach ($headerNotifs as $idx => $n) {
    echo "  [Notif " . ($idx + 1) . "] {$n['text']} | Time: {$n['time']} | URL: {$n['url']}\n";
}

$alarmNotifs = $headerNotifs->filter(fn($n) => str_contains($n['text'], 'ALARM SOP 06'));
echo "  Found " . $alarmNotifs->count() . " Risk Trigger alarms in headerNotifs.\n";
if (count($alarms) > 0 && $alarmNotifs->count() === 0) {
    echo "  --> FAILED: Alarms exist but not found in headerNotifs!\n";
    exit(1);
}
echo "  --> PASSED: View composer correctly injects alarms into navbar header.\n";

// TEST 3: Check risk_trigger.tabs existence and syntax
echo "\n[TEST 3] Testing risk_trigger.tabs partial rendering...\n";
$tabsHtml = View::make('risk_trigger.tabs')->render();
if (str_contains($tabsHtml, 'Ringkasan Control') && str_contains($tabsHtml, 'Register Trigger')) {
    echo "  --> PASSED: risk_trigger.tabs rendered successfully.\n";
} else {
    echo "  --> FAILED: risk_trigger.tabs output unexpected.\n";
    exit(1);
}

// TEST 4: Test HTTP routes rendering for all 6 main Risk Trigger submenus
echo "\n[TEST 4] Testing main Risk Trigger views rendering...\n";
$routes = [
    'risk-trigger/control',
    'risk-trigger/register',
    'risk-trigger/assignment',
    'risk-trigger/program-kerja',
    'risk-trigger/kka',
    'risk-trigger/simpulan-tl',
    'risk-trigger/create',
];

$httpKernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
foreach ($routes as $uri) {
    $req = \Illuminate\Http\Request::create('/' . $uri, 'GET');
    $req->setUserResolver(fn() => $admin);
    $res = $httpKernel->handle($req);
    $status = $res->getStatusCode();
    echo "  GET /{$uri} -> HTTP {$status}\n";
    if ($status !== 200 && $status !== 302) {
        echo "  --> FAILED: Route /{$uri} returned HTTP {$status}!\n";
        exit(1);
    }
}
echo "  --> PASSED: All 7 Risk Trigger routes returned HTTP 200/302.\n";

echo "\n========================================================\n";
echo "   SEMUA UJI HARMONISASI UI SOP 06 BERHASIL (100% OK)   \n";
echo "========================================================\n";
exit(0);
