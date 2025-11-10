@extends('layouts.dashboard')
@section('title', 'Gestión de Turnos')

<style>
.custom-header th {
    background-color: #003764 !important;
    color: white !important;
    border-color: #003764 !important;
}
.custom-header {
    background-color: #003764 !important;
}

.custom-text {
    color: #003764 !important;
}

.nav-tabs .nav-link {
    border: 1px solid #003764;
    color: #003764;
    background-color: white;
}

.nav-tabs .nav-link.active {
    background-color: #003764 !important;
    color: white !important;
    border-color: #003764 !important;
}

.nav-tabs .nav-link:hover {
    background-color: #f8f9fa;
    border-color: #003764;
}

.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}

.badge {
    font-size: 0.75em;
}
</style>

@section('content')
<!-- Encabezado de la página -->
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold" style="color: #003764;">
            <i class="fas fa-search me-2"></i>Búsqueda de Pacientes
        </h3>
    </div>
</div>

<!-- Pestañas de búsqueda -->
<div class="row mb-4">
    <div class="col">
        <ul class="nav nav-tabs" id="searchTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ (!isset($tipoeBusqueda) || $tipoeBusqueda === 'documento') ? 'active' : '' }}" 
                        id="documento-tab" data-bs-toggle="tab" data-bs-target="#documento-pane" 
                        type="button" role="tab" style="color: #003764;">
                    <i class="fas fa-user me-2"></i>Por Documento
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ (isset($tipoeBusqueda) && $tipoeBusqueda === 'estado') ? 'active' : '' }}" 
                        id="estado-tab" data-bs-toggle="tab" data-bs-target="#estado-pane" 
                        type="button" role="tab" style="color: #003764;">
                    <i class="fas fa-filter me-2"></i>Por Estado
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Contenido de las pestañas -->
<div class="tab-content" id="searchTabsContent">
    <!-- Pestaña: Búsqueda por Documento -->
    <div class="tab-pane fade {{ (!isset($tipoeBusqueda) || $tipoeBusqueda === 'documento') ? 'show active' : '' }}" 
         id="documento-pane" role="tabpanel">
        <div class="row mb-4">
            <!-- Columna del formulario de búsqueda por documento -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('turnos.index') }}">
                            <input type="hidden" name="tipo_busqueda" value="documento">
                            <div class="mb-3">
                                <label for="documento" class="form-label"></label>
                                <input type="text" class="form-control" id="documento" name="documento" 
                                       placeholder="Número de Documento" value="{{ old('documento', $documento) }}" required>
                            </div>
                            <button class="btn w-100" type="submit" style="background-color: #003764; border-color: #003764; color: white;">
                                <i class="fas fa-search me-2"></i>Buscar Paciente
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Columna de la información del paciente -->
            <div class="col-md-8">
                @if($resumenPaciente)
                    <div class="card border-success">
                        <div class="card-header bg-success text-white py-2">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-user-check me-2"></i>Paciente Encontrado
                            </h6>
                        </div>
                        <div class="card-body py-2">
                            @if(isset($resumenPaciente['mensaje']))
                                <div class="alert alert-info py-1 mb-2">
                                    <small><i class="fas fa-info-circle me-1"></i>{{ $resumenPaciente['mensaje'] }}</small>
                                </div>
                            @endif
                            
                            <!-- Información básica del paciente -->
                            <div class="row mb-3">
                                <div class="col-8">
                                    <small class="text-muted">Nombre:</small><br>
                                    <strong class="text-truncate d-block">{{ $resumenPaciente['paciente'] }}</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Documento:</small><br>
                                    <span class="badge bg-secondary">{{ $resumenPaciente['documento'] }}</span>
                                </div>
                            </div>

                            <!-- Gráfico de torta para estados de turnos -->
                            @if($resumenPaciente['score'] !== null)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-center" style="height: 200px; position: relative;">
                                            <canvas id="chartTurnos{{ $resumenPaciente['documento'] }}" width="200" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Valoración con estrellas -->
                                <div class="row mb-3">
                                    <div class="col-12 text-center">
                                        <small class="text-muted">Valoración del Paciente:</small><br>
                                        @if(isset($resumenPaciente['likert']) && $resumenPaciente['likert'] !== null && $resumenPaciente['likert'] > 0)
                                            <!-- Caso 1: Tiene valoración Likert específica -->
                                            <div class="text-warning fs-5 d-flex justify-content-center align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $resumenPaciente['likert'])
                                                        <i class="fas fa-star me-1"></i>
                                                    @else
                                                        <i class="far fa-star me-1"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        @elseif(isset($resumenPaciente['score']) && $resumenPaciente['score'] !== null && $resumenPaciente['score'] > 0)
                                            <!-- Caso 2: Calculado desde score de comportamiento -->
                                            @php
                                                $estrellas = 0;
                                                if ($resumenPaciente['score'] >= 90) $estrellas = 5;
                                                elseif ($resumenPaciente['score'] >= 75) $estrellas = 4;
                                                elseif ($resumenPaciente['score'] >= 60) $estrellas = 3;
                                                elseif ($resumenPaciente['score'] >= 45) $estrellas = 2;
                                                else $estrellas = 1;
                                            @endphp
                                            <div class="text-warning fs-5 d-flex justify-content-center align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $estrellas)
                                                        <i class="fas fa-star me-1"></i>
                                                    @else
                                                        <i class="far fa-star me-1"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <small class="text-muted d-block">Basado en comportamiento</small>
                                        @else
                                            <!-- Caso 3: Sin suficientes datos -->
                                            <div class="text-muted fs-5 d-flex justify-content-center align-items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="far fa-star me-1"></i>
                                                @endfor
                                            </div>
                                            <small class="text-muted d-block">Sin valoración disponible</small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($documento)
                    <div class="card border-warning">
                        <div class="card-body py-2 text-center">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            <small class="text-muted">No se encontró paciente con documento: <strong>{{ $documento }}</strong></small>
                        </div>
                    </div>
                @else
                    <div class="card border-light bg-light">
                        <div class="card-body py-2 text-center">
                            <i class="fas fa-search text-muted me-2"></i>
                            <small class="text-muted">Ingrese un documento para buscar</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pestaña: Búsqueda por Estado -->
    <div class="tab-pane fade {{ (isset($tipoeBusqueda) && $tipoeBusqueda === 'estado') ? 'show active' : '' }}" 
         id="estado-pane" role="tabpanel">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <p class="mb-0">Se muestran automáticamente los pacientes con la valoración más baja. No requiere filtros ni búsqueda manual.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados: ahora muestran pacientes con la valoración más baja -->
        @if(isset($resultadosEstado) && $resultadosEstado)
            <div class="card">
                <div class="card-header" style="background-color: #003764; color: white;">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Pacientes con valoración más baja
                    </h5>
                    <small>
                        Período: {{ \Carbon\Carbon::parse($resultadosEstado['fecha_desde'])->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($resultadosEstado['fecha_hasta'])->format('d/m/Y') }} | 
                        Total encontrados: {{ $resultadosEstado['total_encontrados'] }}
                    </small>
                </div>
                <div class="card-body p-0">
                    @if(count($resultadosEstado['pacientes']) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="custom-header">
                                    <tr>
                                        <th>Paciente</th>
                                        <th>Score</th>
                                        <th>Likert</th>
                                        <th>Turnos</th>
                                        <th>Último Turno</th>
                                        <th>Especialidades</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resultadosEstado['pacientes'] as $paciente)
                                        <tr>
                                            <td>
                                                <a href="{{ route('turnos.show', ['id' => $paciente->documento]) }}">
                                                    <strong>{{ $paciente->paciente }}</strong>
                                                </a><br>
                                                <small class="text-muted">DNI: {{ $paciente->documento }}</small><br>
                                                <small class="text-muted">Tel: {{ $paciente->telefono ?? 'N/A' }} | Mail: {{ $paciente->mail ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ isset($paciente->score) ? $paciente->score : 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $likert = isset($paciente->likert) ? intval($paciente->likert) : 0;
                                                @endphp
                                                <div class="text-warning">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $likert)
                                                            <i class="fas fa-star"></i>
                                                        @else
                                                            <i class="far fa-star"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $paciente->total_turnos }}</small>
                                            </td>
                                            <td>
                                                <span class="fw-medium">
                                                    {{ \Carbon\Carbon::parse($paciente->ultimo_turno)->format('d/m/Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($paciente->especialidades_fechas, 100) }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-search fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron pacientes con valoración baja en el período especificado.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>



<!-- Lista de turnos -->
@if($resumenPaciente && isset($resumenPaciente['score']) && $resumenPaciente['score'] !== null)
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0 custom-text">
                <i class="fas fa-calendar-check me-2 custom-text"></i>Turnos del Paciente
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="custom-header" style="background-color: #003764 !important; color: white !important;">
                        <tr>
                            <th style="background-color: #003764 !important; color: white !important; border-color: #003764 !important;"><i class="fas fa-calendar me-1"></i>Fecha</th>
                            <th style="background-color: #003764 !important; color: white !important; border-color: #003764 !important;"><i class="fas fa-stethoscope me-1"></i>Especialidad</th>
                            <th style="background-color: #003764 !important; color: white !important; border-color: #003764 !important;"><i class="fas fa-info-circle me-1"></i>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($resumenPaciente['turnos']))
                            @forelse($resumenPaciente['turnos'] as $turno)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ \Carbon\Carbon::parse($turno->FECHA_TURNO)->format('d/m/Y') }}</span>
                                        @if($turno->HORA_TURNO)
                                            <br><small class="text-muted">{{ \Carbon\Carbon::parse($turno->HORA_TURNO)->format('H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            {{ $turno->ESPECIALIDAD }}
                                        </span>
                                        @if($turno->PROFESIONAL)
                                            <br><small class="text-muted">{{ $turno->PROFESIONAL }}</small>
                                        @endif
                                        @if($turno->DEPARTAMENTO)
                                            <br><small class="text-info">{{ $turno->DEPARTAMENTO }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = \App\Models\Paciente::getBadgeClassPorEstado($turno->ESTADO_TURNO_ID);
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $turno->ESTADO_TURNO }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            No hay turnos registrados para este paciente en los últimos 180 días.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-2x mb-2"></i><br>
                                        No hay turnos disponibles para mostrar.
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@elseif($resumenPaciente)
    <!-- Mensaje cuando el paciente existe pero no tiene métricas suficientes -->
    <div class="card border-info">
        <div class="card-body text-center py-4">
            <i class="fas fa-info-circle text-info fa-2x mb-3"></i>
            <h5 class="text-info">Datos Insuficientes para Evaluación</h5>
            <p class="text-muted mb-0">
                Este paciente no tiene suficientes turnos de especialidades válidas para generar métricas de comportamiento.
                <br><small>Se requieren al menos 3 turnos en especialidades evaluables.</small>
            </p>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($resumenPaciente && $resumenPaciente['score'] !== null)
        // Datos para el gráfico de torta
        const ctx = document.getElementById('chartTurnos{{ $resumenPaciente['documento'] }}');
        if (ctx) {
            const realizados = {{ $resumenPaciente['realizados'] }};
            const cancelados = {{ $resumenPaciente['cancelados'] }};
            const noAsistio = {{ $resumenPaciente['no_asistio'] }};
            
            // Solo mostrar el gráfico si hay datos
            if (realizados > 0 || cancelados > 0 || noAsistio > 0) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Realizados', 'Cancelados', 'No Asistió'],
                        datasets: [{
                            data: [realizados, cancelados, noAsistio],
                            backgroundColor: [
                                '#198754', // Verde (bg-success)
                                '#ffc107', // Amarillo (bg-warning)
                                '#dc3545'  // Rojo (bg-danger)
                            ],
                            borderColor: [
                                '#146c43',
                                '#ff8c0a',
                                '#b02a37'
                            ],
                            borderWidth: 2,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    },
                                    padding: 8
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        },
                        cutout: '50%', // Para hacer un donut chart
                        animation: {
                            animateRotate: true,
                            duration: 1000
                        }
                    }
                });
            }
        }
    @endif
});
</script>
@endsection
