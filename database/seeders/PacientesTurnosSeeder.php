<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\Turno;
use App\Models\Valoracion;

class PacientesTurnosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear pacientes de ejemplo
        $paciente1 = Paciente::create([
            'documento' => '12345678',
            'nombres' => 'Juan Carlos',
            'apellidos' => 'Pérez García',
            'fecha_nacimiento' => '1985-03-15',
            'nro_hc' => 'HC001'
        ]);

        $paciente2 = Paciente::create([
            'documento' => '87654321',
            'nombres' => 'María Elena',
            'apellidos' => 'González López',
            'fecha_nacimiento' => '1990-07-22',
            'nro_hc' => 'HC002'
        ]);

        $paciente3 = Paciente::create([
            'documento' => '11223344',
            'nombres' => 'Carlos Alberto',
            'apellidos' => 'Rodríguez Silva',
            'fecha_nacimiento' => '1982-11-08',
            'nro_hc' => 'HC003'
        ]);

        // Crear turnos para Juan Carlos (12345678)
        $turno1 = Turno::create([
            'paciente_id' => $paciente1->id,
            'fecha' => '2025-10-20',
            'hora' => '09:30',
            'especialidad' => 'Cardiología',
            'medico' => 'Dr. Martínez',
            'estado' => 'Realizado'
        ]);

        $turno2 = Turno::create([
            'paciente_id' => $paciente1->id,
            'fecha' => '2025-10-18',
            'hora' => '14:00',
            'especialidad' => 'Medicina General',
            'medico' => 'Dra. López',
            'estado' => 'Realizado'
        ]);

        $turno3 = Turno::create([
            'paciente_id' => $paciente1->id,
            'fecha' => '2025-10-25',
            'hora' => '10:15',
            'especialidad' => 'Traumatología',
            'medico' => 'Dr. García',
            'estado' => 'Pendiente'
        ]);

        // Crear turnos para María Elena (87654321)
        $turno4 = Turno::create([
            'paciente_id' => $paciente2->id,
            'fecha' => '2025-10-21',
            'hora' => '11:00',
            'especialidad' => 'Ginecología',
            'medico' => 'Dra. Fernández',
            'estado' => 'Realizado'
        ]);

        $turno5 = Turno::create([
            'paciente_id' => $paciente2->id,
            'fecha' => '2025-10-23',
            'hora' => '16:30',
            'especialidad' => 'Dermatología',
            'medico' => 'Dr. Ruiz',
            'estado' => 'Pendiente'
        ]);

        // Crear turnos para Carlos Alberto (11223344)
        $turno6 = Turno::create([
            'paciente_id' => $paciente3->id,
            'fecha' => '2025-10-19',
            'hora' => '08:45',
            'especialidad' => 'Oftalmología',
            'medico' => 'Dr. Vargas',
            'estado' => 'Realizado'
        ]);

        // Crear valoraciones de ejemplo para Juan Carlos
        Valoracion::create([
            'turno_id' => $turno1->id,
            'paciente_documento' => '12345678',
            'paciente_nombre' => 'Juan Carlos Pérez García',
            'estrellas' => 4,
            'notas' => 'Muy buena atención del cardiólogo'
        ]);

        Valoracion::create([
            'turno_id' => $turno2->id,
            'paciente_documento' => '12345678',
            'paciente_nombre' => 'Juan Carlos Pérez García',
            'estrellas' => 5,
            'notas' => 'Excelente servicio en medicina general'
        ]);

        // Crear valoración para María Elena
        Valoracion::create([
            'turno_id' => $turno4->id,
            'paciente_documento' => '87654321',
            'paciente_nombre' => 'María Elena González López',
            'estrellas' => 3,
            'notas' => 'Regular, puede mejorar la puntualidad'
        ]);
    }
}
