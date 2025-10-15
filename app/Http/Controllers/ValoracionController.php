<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ValoracionController extends Controller
{
    public function store(Request $request, $turnoId)
    {
        // Guardar valoración (estrellas y notas) en Firestore o base local
        return redirect()->route('turnos.show', $turnoId)->with('status', 'Valoración guardada');
    }
}
