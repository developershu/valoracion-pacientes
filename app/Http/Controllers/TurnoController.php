<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TurnoController extends Controller
{
    public function index(Request $request)
    {
        // Obtener filtro por documento
        $documento = $request->input('documento');
        $turnos = [];
        $paciente = null;

        if ($documento) {
            // Buscar paciente por documento en la API externa usando autenticación básica
            $respPaciente = Http::withBasicAuth('apiuser', 'Apiuser2025')
                ->get('https://universitario.alephoo.com/api/v3/admin/personas', [
                    'filter[documento]' => $documento
                ]);
            $json = $respPaciente->json();
            $paciente = null;
            if (
                $respPaciente->ok() &&
                isset($json['data']) &&
                is_array($json['data']) &&
                !empty($json['data']) &&
                isset($json['data'][0]) &&
                is_array($json['data'][0]) &&
                array_key_exists('id', $json['data'][0])
            ) {
                $paciente = $json['data'][0];
                // Buscar turnos del paciente (limitando la cantidad)
                $respTurnos = Http::withBasicAuth('apiuser', 'Apiuser2025')
                    ->get('https://universitario.alephoo.com/api/v3/admision/turnos', [
                        'persona_id' => $paciente['id'],
                        'limit' => 10
                    ]);
                $turnos = $respTurnos->ok() ? $respTurnos->json() : [];
            }
            // Depuración: mostrar respuesta cruda
            // dd($json, $paciente ?? null, $turnos);
        }

        return view('turnos', compact('turnos', 'paciente', 'documento'));
    }

    public function show($id)
    {
        // Obtener detalle de turno desde la API externa
        $response = Http::get('https://universitario.alephoo.com/api/v3/admision/turnos/' . $id);
        $turno = $response->ok() ? $response->json() : null;
        return view('detalle_turno', compact('turno'));
    }

    public function sync()
    {
        // Aquí podrías guardar los turnos en la base local si lo deseas
        $response = Http::get('https://universitario.alephoo.com/api/v3/admision/turnos');
        // Lógica para guardar en base local...
        return back()->with('status', 'Turnos sincronizados');
    }
}
