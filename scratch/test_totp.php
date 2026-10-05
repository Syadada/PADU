<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$secret = \App\Services\TwoFactorService::generateSecret();
$timeSlice = (int)floor(time() / 30);
$code = \App\Services\TwoFactorService::calculateCode($secret, $timeSlice);
$valid = \App\Services\TwoFactorService::verifyCode($secret, $code);
$key = \App\Services\TwoFactorService::generateEmergencyRecoveryKey();

echo "Secret: {$secret}\n";
echo "Code: {$code}\n";
echo "Valid: " . ($valid ? "YES" : "NO") . "\n";
echo "Recovery Key: {$key}\n";
