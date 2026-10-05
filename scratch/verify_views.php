<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$admin = User::where('role', 'superadmin')->first();
Auth::login($admin);

$views = [
    'auth.login' => [],
    'auth.two-factor' => ['user' => $admin],
    'auth.force-reset' => ['user' => $admin],
    'admin.audit-logs' => [
        'logs' => \App\Models\AuditLog::paginate(10),
        'totalLogsToday' => 0,
        'totalFailedAttempts' => 0,
        'totalLockouts' => 0,
        'totalBackups' => 0,
        'actionTypes' => []
    ],
    'admin.backup' => ['backups' => []],
    'admin.users' => ['users' => [$admin]],
    'admin.two-factor-setup' => [
        'user' => $admin,
        'secret' => 'TESTSECRET123456',
        'recoveryKey' => 'PADU-TEST-1234-5678-9999',
        'otpUri' => 'otpauth://totp/PADU:test?secret=TEST'
    ]
];

foreach ($views as $viewName => $viewData) {
    try {
        view($viewName, $viewData)->render();
        echo 'View ' . $viewName . ': OK' . PHP_EOL;
    } catch (\Throwable $e) {
        echo 'View ' . $viewName . ': ERROR -> ' . $e->getMessage() . PHP_EOL;
    }
}
