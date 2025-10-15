@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Búsqueda de Paciente</h2>
    <form class="row g-3 mb-4" method="GET" action="{{ route('turnos.index') }}">
        <div class="col-md-4">
            <input type="text" class="form-control" name="documento" placeholder="N° de documento" value="{{ old('documento', $documento) }}" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
        <div class="col-md-6 text-end">
            <form method="POST" action="{{ route('turnos.sync') }}">
                @csrf
                <button class="btn btn-secondary">Sincronizar Turnos</button>
            </form>
        </div>
    </form>

    @if($paciente)
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Datos del Paciente</h5>
                <p><strong>Nombre:</strong> {{ $paciente['attributes']['nombres'] ?? '' }} {{ $paciente['attributes']['apellidos'] ?? '' }}</p>
                <p><strong>Documento:</strong> {{ $paciente['attributes']['documento'] ?? '' }}</p>
                <p><strong>Fecha de Nacimiento:</strong> {{ $paciente['attributes']['fechaNacimiento'] ?? '' }}</p>
                <p><strong>Nro HC:</strong> {{ $paciente['attributes']['nroHc'] ?? '' }}</p>
            </div>
        </div>
    @elseif($documento)
        <div class="alert alert-warning">No se encontró paciente con ese documento.</div>
    @endif

    <h3>Turnos del Paciente</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Especialidad</th>
                <th>Estado</th>
                <th>Valoración</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($turnos['data'] ?? []) as $turno)
                <tr>
                    <td>{{ $turno['attributes']['fecha'] ?? '' }}</td>
                    <td>{{ $turno['attributes']['especialidad'] ?? '' }}</td>
                    <td>{{ $turno['attributes']['estado'] ?? '' }}</td>
                    <td><!-- Aquí irá la valoración si existe --></td>
                    <td>
                        <a href="{{ route('turnos.show', $turno['id']) }}" class="btn btn-sm btn-info">Ver Detalle</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No hay turnos para mostrar.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
