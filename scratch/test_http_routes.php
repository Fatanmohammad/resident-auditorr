<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(30);
\Illuminate\Support\Facades\Auth::login($user);

// Request 1: /risk-trigger/assignment
$request1 = \Illuminate\Http\Request::create('/risk-trigger/assignment', 'GET');
$request1->setUserResolver(fn() => $user);
$response1 = $kernel->handle($request1);
echo "GET /risk-trigger/assignment Status: " . $response1->getStatusCode() . "\n";

// Request 2: /risk-trigger/program-kerja
$request2 = \Illuminate\Http\Request::create('/risk-trigger/program-kerja', 'GET');
$request2->setUserResolver(fn() => $user);
$response2 = $kernel->handle($request2);
echo "GET /risk-trigger/program-kerja Status: " . $response2->getStatusCode() . "\n";

// Request 3: /risk-trigger/assignment/create
$request3 = \Illuminate\Http\Request::create('/risk-trigger/assignment/create', 'GET');
$request3->setUserResolver(fn() => $user);
$response3 = $kernel->handle($request3);
echo "GET /risk-trigger/assignment/create Status: " . $response3->getStatusCode() . "\n";
