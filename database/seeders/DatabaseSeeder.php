<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario administrador específico para Guillermo
        $this->call([
            AdminUserSeeder::class,
        ]);
        
        $this->command->info('✅ Sistema configurado para autenticación con salutte2');
        $this->command->info('ℹ️  Los usuarios deben autenticarse con su username del sistema hospitalario');
        $this->command->info('📝 Ejemplos: admin, carlos.estrada, cristian.reta, fabiana.colucci');
        $this->command->info('👤 Usuario admin creado: guillermo.bermejo');
    }
}
