<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Valoracion extends Model
{
    use HasFactory;

    protected $table = 'valoraciones';

    protected $fillable = [
        'turno_id',
        'paciente_documento',
        'paciente_nombre',
        'estrellas',
        'notas'
    ];

    // Relación con turno
    public function turno()
    {
        return $this->belongsTo(Turno::class, 'turno_id', 'id');
    }

    // Método para obtener la valoración promedio de un paciente
    public static function promedioValoracionPorPaciente($documento)
    {
        return self::where('paciente_documento', $documento)
                   ->avg('estrellas');
    }

    // Método para verificar si un turno ya fue valorado
    public static function turnoYaValorado($turnoId)
    {
        return self::where('turno_id', $turnoId)->exists();
    }
}
