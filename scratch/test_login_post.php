<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Models\User;

$controller = new AuthController();

// TEST 1: POST dengan email dan password salah
echo "=== TEST 1: Login dengan Password Salah ===\n";
$req1 = Request::create('/login', 'POST', [
    'email' => 'operator@padu.local',
    'password' => 'WrongPassword123!',
]);
// Set session
$session = app('session')->driver();
$req1->setLaravelSession($session);

try {
    $res1 = $controller->processLogin($req1);
    echo "Status Code: " . $res1->getStatusCode() . "\n";
    echo "Target URL: " . $res1->getTargetUrl() . "\n";
    $sessionErrors = $session->get('errors');
    if ($sessionErrors) {
        echo "Error message: " . $sessionErrors->first('password') . "\n";
        echo "Email error (should be empty): " . ($sessionErrors->first('email') ?? 'NONE') . "\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// TEST 2: POST dengan email dan password benar (Operator)
echo "\n=== TEST 2: Login Operator Berhasil ===\n";
$req2 = Request::create('/login', 'POST', [
    'email' => 'operator@padu.local',
    'password' => 'PasswordOperator2026!',
]);
$req2->setLaravelSession($session);
try {
    $res2 = $controller->processLogin($req2);
    echo "Status Code: " . $res2->getStatusCode() . "\n";
    echo "Target URL: " . $res2->getTargetUrl() . "\n";
    echo "Authenticated User: " . (auth()->user() ? auth()->user()->name : 'NONE') . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
auth()->logout();

// TEST 3: POST dengan email dan password benar (Super Admin - first login)
echo "\n=== TEST 3: Login Super Admin (First Login) ===\n";
$req3 = Request::create('/login', 'POST', [
    'email' => 'superadmin@padu.local',
    'password' => 'PasswordSuperAdmin2026!',
]);
$req3->setLaravelSession($session);
try {
    $res3 = $controller->processLogin($req3);
    echo "Status Code: " . $res3->getStatusCode() . "\n";
    echo "Target URL: " . $res3->getTargetUrl() . "\n";
    echo "Authenticated User: " . (auth()->user() ? auth()->user()->name : 'NONE') . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
auth()->logout();
