<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- FIXING DESIL DATA IN KELUARGAS TABLE ---\n";

DB::statement("UPDATE keluargas SET desil_nasional = (ABS(RANDOM()) % 10 + 1) WHERE desil_nasional IS NULL OR typeof(desil_nasional) = 'blob' OR CAST(desil_nasional AS INTEGER) = 0;");

$stats = DB::table('keluargas')->select('desil_nasional', DB::raw('count(*) as cnt'))->groupBy('desil_nasional')->get();
echo "Updated desil_nasional distribution in keluargas:\n";
foreach ($stats as $s) {
    echo "Desil " . $s->desil_nasional . ": " . number_format($s->cnt) . " families\n";
}
