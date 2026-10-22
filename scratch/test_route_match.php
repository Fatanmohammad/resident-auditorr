<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

$req1 = Request::create('/risk-trigger/assignment', 'GET');
$route1 = Route::getRoutes()->match($req1);
echo "Path: /risk-trigger/assignment -> Matches Route: " . $route1->getName() . " (" . $route1->uri() . ")\n";

$req2 = Request::create('/risk-trigger/program-kerja', 'GET');
$route2 = Route::getRoutes()->match($req2);
echo "Path: /risk-trigger/program-kerja -> Matches Route: " . $route2->getName() . " (" . $route2->uri() . ")\n";

$req3 = Request::create('/risk-trigger/123', 'GET');
$route3 = Route::getRoutes()->match($req3);
echo "Path: /risk-trigger/123 -> Matches Route: " . $route3->getName() . " (" . $route3->uri() . ")\n";
