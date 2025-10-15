@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Detalle del Turno</h2>
    @if($turno && isset($turno['data']))
        @php dd($turno['data']['attributes']); @endphp
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>Paciente:</strong> {{ $turno['data']['attributes']['paciente'] ?? '' }}</p>
                <p><strong>Fecha:</strong> {{ $turno['data']['attributes']['fecha'] ?? '' }}</p>
                <p><strong>Especialidad:</strong> {{ $turno['data']['attributes']['especialidad'] ?? '' }}</p>
                <p><strong>Estado:</strong> 
                    @php
                        $estados = [
                            1 => 'Pendiente',
                            2 => 'En curso',
                            3 => 'Cancelado',
                            4 => 'No asistió',
                            5 => 'Realizado',
                            6 => 'Arribo',
                            7 => 'Bloqueado',
                            8 => 'No atendido',
                            9 => 'Visto',
                            10 => 'A reprogramar',
                        ];
                        $estadoId = $turno['data']['attributes']['estado'] ?? null;
                    @endphp
                    {{ $estados[$estadoId] ?? $estadoId }}
                </p>
            </div>
        </div>
        <h4>Valoración</h4>
        <form method="POST" action="{{ route('turnos.valorar', $turno['data']['id']) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Estrellas</label>
                <select class="form-select" name="estrellas">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Notas</label>
                <textarea class="form-control" name="notas"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Guardar Valoración</button>
        </form>
    @else
        <div class="alert alert-warning">No se encontró información del turno.</div>
    @endif
</div>
@endsection
