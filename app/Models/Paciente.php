<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'documento',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'nro_hc',
        'telefono',
        'email'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date'
    ];

    // Accessor para nombre completo
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    // Método para obtener la clase CSS del badge según el estado del turno
    public static function getBadgeClassPorEstado($estadoId)
    {
        return match($estadoId) {
            5 => 'bg-success', // Realizado - Verde
            3 => 'bg-warning text-dark', // Cancelado - Amarillo
            4 => 'bg-danger', // No asistió - Rojo
            1, 6, 9 => 'bg-info', // Pendiente, Arribo, Visto - Azul
            2 => 'bg-info', // En curso - Azul
            7, 8 => 'bg-danger', // Bloqueado, No atendido - Rojo
            10 => 'bg-secondary', // A reprogramar - Gris
            default => 'bg-dark'
        };
    }

    // Método para buscar por documento
    public static function buscarPorDocumento($documento)
    {
        return self::where('documento', $documento)->first();
    }
}
