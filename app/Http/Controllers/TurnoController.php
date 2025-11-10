<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Valoracion;
use App\Models\Paciente;
use App\Models\Turno;
use App\Models\ResumenPaciente;

class TurnoController extends Controller
{
    public function index(Request $request)
    {
        // Verificar si es búsqueda por documento o por estado
        $tipoeBusqueda = $request->input('tipo_busqueda', 'documento');
        $documento = $request->input('documento');
        $resumenPaciente = null;
        $resultadosEstado = null;

        if ($tipoeBusqueda === 'documento' && $documento) {
            // Búsqueda por documento específico
            $resumenPaciente = ResumenPaciente::obtenerResumenPorDocumento($documento);
        } elseif ($tipoeBusqueda === 'estado') {
            // Ahora la pestaña 'Por Estado' muestra pacientes con valoración más baja
            // Usamos parámetros si el usuario los pasó (aunque la vista los muestra deshabilitados)
            $fechaDesde = $request->input('fecha_desde');
            $fechaHasta = $request->input('fecha_hasta');
            $minTurnos = $request->input('min_turnos', 3);

            $resultadosEstado = ResumenPaciente::buscarPorValoracionBaja(
                $fechaDesde,
                $fechaHasta,
                $minTurnos,
                100 // límite
            );
        }

        // Si no se cargaron resultados por estado (p. ej. se visitó la página sin params),
        // cargamos la lista de valoración baja por defecto para que la pestaña la muestre.
        if (is_null($resultadosEstado)) {
            $resultadosEstado = ResumenPaciente::buscarPorValoracionBaja(null, null, 3, 100);
        }

        return view('turnos', compact('resumenPaciente', 'documento', 'resultadosEstado', 'tipoeBusqueda'));
    }

    public function show($id)
    {
        try {
            // Intentar encontrar un turno por ID
            $turno = Turno::with('paciente', 'valoracion')->find($id);
            if ($turno) {
                return view('detalle_turno', compact('turno'));
            }

            // Si no se encontró un turno, tratamos el parámetro como documento (DNI)
            $documento = $id;
            $resumenPaciente = ResumenPaciente::obtenerResumenPorDocumento($documento);

            if (!$resumenPaciente) {
                return redirect()->route('turnos.index')->with('error', 'Paciente no encontrado.');
            }

            // Mostrar vista específica de paciente
            return view('paciente_detalle', compact('resumenPaciente'));

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error en TurnoController@show: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->route('turnos.index')->with('error', 'Ocurrió un error al cargar el paciente. Revisa los logs.');
        }
    }
}
