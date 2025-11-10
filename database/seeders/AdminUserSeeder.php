<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'guillermo.bermejo'],
            [
                'username' => 'guillermo.bermejo',
                'password' => Hash::make('admin123'),
                'personal_id' => 1532, // ID disponible en tabla personal
                'blocked_account' => 0,
                'borrado_logico' => 0,
                'cambiar_password' => 0,
                'log_attempt' => 0,
                'created_by' => 1, // ID del usuario que crea (sistema)
                'modified_by' => 1, // ID del usuario que modifica (sistema)
            ]
        );
        
        $this->command->info('✅ Usuario admin creado: guillermo.bermejo');
        $this->command->info('🔑 Contraseña: admin123');
        $this->command->info('📧 Email equivalente: guillermo.bermejo@hospital.uncu.edu.ar');
    }
}
