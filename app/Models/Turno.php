<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'fecha',
        'hora',
        'especialidad',
        'medico',
        'estado',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i'
    ];

    // Relación con paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Relación con valoraciones
    public function valoracion()
    {
        return $this->hasOne(Valoracion::class, 'turno_id', 'id');
    }

    // Método para verificar si está valorado
    public function estaValorado()
    {
        return $this->valoracion()->exists();
    }

    // Scope para turnos por paciente
    public function scopePorPaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    // Scope para turnos por fecha
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }
}
