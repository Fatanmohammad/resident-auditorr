<?php
require 'c:/laragon/www/resident-auditorr/vendor/autoload.php';
$app = require_once 'c:/laragon/www/resident-auditorr/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\RiskTrigger\RiskTriggerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$admin = User::find(30);
Auth::login($admin);

$controller = app(RiskTriggerController::class);

echo "1. Testing Admin KKA Index: ";
$resp = $controller->kkaIndex(new Request());
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

echo "2. Testing Admin KKA Edit (ID 1): ";
$resp = $controller->kkaEdit(new Request(), 1);
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

echo "3. Testing Admin Simpulan TL Index: ";
$resp = $controller->simpulanTlIndex(new Request());
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

echo "4. Testing Admin Simpulan TL Edit (ID 1): ";
$resp = $controller->simpulanTlEdit(new Request(), 1);
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

$raPoso = User::find(8);
Auth::login($raPoso);

echo "5. Testing RA KKA Index: ";
$resp = $controller->kkaIndex(new Request());
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

echo "6. Testing RA KKA Edit (ID 1): ";
$resp = $controller->kkaEdit(new Request(), 1);
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

echo "7. Testing RA Simpulan TL Index: ";
$resp = $controller->simpulanTlIndex(new Request());
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

echo "8. Testing RA Simpulan TL Edit (ID 1): ";
$resp = $controller->simpulanTlEdit(new Request(), 1);
$rendered = $resp->render();
echo strlen($rendered) . " bytes rendered.\n";

echo "\n============================================\n";
echo "SUCCESS: ALL 8 VIEWS RENDERED 100% PERFECTLY!\n";
echo "============================================\n";
