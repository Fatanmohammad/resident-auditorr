<?php
require 'c:/laragon/www/resident-auditorr/vendor/autoload.php';
$app = require_once 'c:/laragon/www/resident-auditorr/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\RiskTrigger\RiskTriggerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$controller = app(RiskTriggerController::class);
$admin = User::find(30);
$raPoso = User::find(8);

view()->share('errors', new \Illuminate\Support\ViewErrorBag());

echo "========================================================\n";
echo "TEST 6: Blade Views Rendering Tests (Admin & RA)\n";
echo "========================================================\n";

// 1. Admin views
Auth::login($admin);

echo "1. Testing Admin Assignment Index View...\n";
$resp = $controller->assignment(new Request());
$rendered = $resp->render();
assert(str_contains($rendered, 'Assignment & Scope Visit'), "View must contain title");
assert(str_contains($rendered, '+ Buat Assignment Visit'), "Admin must see + Buat Assignment Visit button");
echo ">>> PASSED: Admin Assignment Index rendered successfully!\n";

echo "2. Testing Admin Assignment Create View...\n";
$resp = $controller->createAssignment(new Request());
$rendered = $resp->render();
assert(str_contains($rendered, 'Penerbitan Penugasan Risk Trigger Visit'), "View must contain title");
echo ">>> PASSED: Admin Assignment Create rendered successfully!\n";

echo "3. Testing Admin Program Kerja Index View...\n";
$resp = $controller->programKerja(new Request());
$rendered = $resp->render();
assert(str_contains($rendered, 'Program Kerja Terarah & Daftar Data'), "View must contain title");
echo ">>> PASSED: Admin Program Kerja Index rendered successfully!\n";

echo "4. Testing Admin Ringkasan Control View with Tabs...\n";
$resp = $controller->control(new Request());
$rendered = $resp->render();
assert(str_contains($rendered, 'Assignment & Scope (22 Kolom)'), "View must contain tab 3");
assert(str_contains($rendered, 'Program Kerja & Data (18 Kolom)'), "View must contain tab 4");
echo ">>> PASSED: Admin Ringkasan Control rendered successfully!\n";

// 2. RA views
Auth::login($raPoso);

echo "5. Testing RA Assignment Index View...\n";
$resp = $controller->assignment(new Request());
$rendered = $resp->render();
assert(str_contains($rendered, 'Cabang: Cabang Poso'), "RA must see their assigned branch");
assert(!str_contains($rendered, '+ Buat Assignment Visit'), "RA must NOT see + Buat Assignment Visit button");
echo ">>> PASSED: RA Assignment Index rendered successfully!\n";

echo "6. Testing RA Program Kerja Index View...\n";
$resp = $controller->programKerja(new Request());
$rendered = $resp->render();
assert(str_contains($rendered, 'Cabang: Cabang Poso'), "RA must see their assigned branch");
echo ">>> PASSED: RA Program Kerja Index rendered successfully!\n";

echo "========================================================\n";
echo "ALL BLADE VIEWS RENDERED 100% PERFECTLY WITHOUT ERRORS!\n";
echo "========================================================\n";
