<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('especialidad', 100);
            $table->string('medico', 100)->nullable();
            $table->enum('estado', ['Pendiente', 'En curso', 'Cancelado', 'No asistió', 'Realizado', 'Arribo', 'Bloqueado', 'No atendido', 'Visto', 'A reprogramar'])->default('Pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->index(['paciente_id', 'fecha']);
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
