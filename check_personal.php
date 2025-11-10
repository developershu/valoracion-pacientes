<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Buscar IDs de personal que NO estén siendo usados por usuarios
    $result = DB::connection('mysql_real')->select("
        SELECT p.id 
        FROM personal p 
        LEFT JOIN usuario u ON p.id = u.personal_id 
        WHERE u.personal_id IS NULL 
        ORDER BY p.id DESC 
        LIMIT 5
    ");
    
    echo "IDs de personal disponibles (no usados por usuarios):\n";
    foreach ($result as $p) {
        echo "- ID: {$p->id}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
