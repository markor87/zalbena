<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = DB::select('SHOW TABLES');

echo "Tables in database 'zalbena':\n\n";
foreach ($tables as $table) {
    echo "- " . $table->Tables_in_zalbena . "\n";
}
