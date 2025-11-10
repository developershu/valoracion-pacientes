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
        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();
            $table->string('turno_id'); // ID del turno desde la API externa
            $table->string('paciente_documento'); // Documento del paciente
            $table->string('paciente_nombre')->nullable(); // Nombre del paciente
            $table->integer('estrellas'); // Valoración de 1 a 5 estrellas
            $table->text('notas')->nullable(); // Comentarios adicionales
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('paciente_documento');
            $table->index('turno_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valoraciones');
    }
};
