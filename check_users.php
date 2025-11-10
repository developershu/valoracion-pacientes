<?php
require_once 'vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== CONSULTANDO TABLA USUARIO EN SALUTTE2 ===\n\n";
    
    // Verificar conexión
    echo "1. Verificando conexión a salutte2...\n";
    $connection = DB::connection('mysql_real');
    $connection->getPdo();
    echo "✅ Conexión exitosa\n\n";
    
    // Consultar estructura de la tabla usuario
    echo "2. Estructura de la tabla 'usuario':\n";
    $columns = $connection->select("DESCRIBE usuario");
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type}) - {$column->Null} - {$column->Key}\n";
    }
    echo "\n";
    
    // Consultar algunos registros de ejemplo
    echo "3. Primeros 5 usuarios (solo campos relevantes):\n";
    $users = $connection->select("
        SELECT id, username, personal_id, blocked_account, borrado_logico, deleted_at 
        FROM usuario 
        WHERE deleted_at IS NULL 
        LIMIT 5
    ");
    
    foreach ($users as $user) {
        echo "ID: {$user->id} | Username: {$user->username} | Personal_ID: {$user->personal_id} | Blocked: {$user->blocked_account} | Borrado: {$user->borrado_logico}\n";
    }
    echo "\n";
    
    // Contar total de usuarios activos
    echo "4. Total de usuarios activos:\n";
    $activeCount = $connection->selectOne("
        SELECT COUNT(*) as total 
        FROM usuario 
        WHERE deleted_at IS NULL 
        AND blocked_account = 0 
        AND borrado_logico = 0
    ");
    echo "Total usuarios activos: {$activeCount->total}\n\n";
    
    // Buscar patrones de email
    echo "5. Patrones de username:\n";
    $patterns = $connection->select("
        SELECT 
            CASE 
                WHEN username LIKE '%@hospital.uncu.edu.ar' THEN '@hospital.uncu.edu.ar'
                WHEN username LIKE '%@%' THEN 'Otro dominio'
                ELSE 'Sin @'
            END as patron,
            COUNT(*) as cantidad
        FROM usuario 
        WHERE deleted_at IS NULL
        GROUP BY patron
    ");
    
    foreach ($patterns as $pattern) {
        echo "- {$pattern->patron}: {$pattern->cantidad} usuarios\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
