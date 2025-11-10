<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Valoracion;
use Illuminate\Support\Facades\Http;

class ValoracionController extends Controller
{
    public function store(Request $request, $turnoId)
    {
        // Validar los datos de entrada
        $request->validate([
            'estrellas' => 'required|integer|between:1,5',
            'notas' => 'nullable|string|max:1000'
        ]);

        // Verificar si el turno ya fue valorado
        if (Valoracion::turnoYaValorado($turnoId)) {
            return redirect()->route('turnos.show', $turnoId)
                ->with('error', 'Este turno ya ha sido valorado anteriormente.');
        }

        // Obtener información del turno desde la API
        $response = Http::withBasicAuth('apiuser', 'Apiuser2025')
            ->get('https://universitario.alephoo.com/api/v3/admision/turnos/' . $turnoId);
        
        if (!$response->ok()) {
            return redirect()->route('turnos.show', $turnoId)
                ->with('error', 'No se pudo obtener la información del turno.');
        }

        $turno = $response->json();
        $turnoData = $turno['data'] ?? null;

        if (!$turnoData) {
            return redirect()->route('turnos.show', $turnoId)
                ->with('error', 'Información del turno no disponible.');
        }

        // Buscar información del paciente
        $pacienteId = $turnoData['attributes']['persona_id'] ?? null;
        $pacienteNombre = $turnoData['attributes']['paciente'] ?? 'No especificado';
        $pacienteDocumento = null;

        if ($pacienteId) {
            $respPaciente = Http::withBasicAuth('apiuser', 'Apiuser2025')
                ->get('https://universitario.alephoo.com/api/v3/admin/personas/' . $pacienteId);
            
            if ($respPaciente->ok()) {
                $pacienteData = $respPaciente->json();
                $pacienteDocumento = $pacienteData['data']['attributes']['documento'] ?? null;
                $pacienteNombre = ($pacienteData['data']['attributes']['nombres'] ?? '') . ' ' . 
                                ($pacienteData['data']['attributes']['apellidos'] ?? '');
            }
        }

        // Crear la valoración
        Valoracion::create([
            'turno_id' => $turnoId,
            'paciente_documento' => $pacienteDocumento,
            'paciente_nombre' => trim($pacienteNombre),
            'estrellas' => $request->estrellas,
            'notas' => $request->notas
        ]);

        return redirect()->route('turnos.index')
            ->with('success', 'Valoración guardada exitosamente. ¡Gracias por su opinión!');
    }
}
