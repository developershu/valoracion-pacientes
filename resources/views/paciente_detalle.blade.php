@extends('layouts.dashboard')
@section('title', 'Detalle Paciente')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold" style="color: #003764;"><i class="fas fa-user"></i> Detalle del Paciente</h3>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $resumenPaciente['paciente'] }}</h5>
                <p class="mb-1"><strong>DNI:</strong> {{ $resumenPaciente['documento'] }}</p>
                <p class="mb-1"><strong>Tel:</strong> {{ $resumenPaciente['telefono'] ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Mail:</strong> {{ $resumenPaciente['mail'] ?? 'N/A' }}</p>

                {{-- Score y Likert removidos por solicitud del usuario --}}

            </div>
        </div>

        <!-- Bloque checklist solicitado -->
        <div class="card mt-3">
            <div class="card-header">Motivos (checklist)</div>
            <div class="card-body">
                <form id="problemasForm">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="problemas_personales" id="chk_problemas" name="problemas[]">
                        <label class="form-check-label" for="chk_problemas">Problemas Personales</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="mala_atencion" id="chk_mala" name="problemas[]">
                        <label class="form-check-label" for="chk_mala">Mala Atención</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="no_recordo" id="chk_no_recordo" name="problemas[]">
                        <label class="form-check-label" for="chk_no_recordo">No recordó el turno</label>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-primary" id="guardarProblemas">Guardar</button>
                        <small class="text-muted ms-2">(Por ahora no se persiste en servidor)</small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @if(isset($resumenPaciente['turnos']) && count($resumenPaciente['turnos']) > 0)
            <div class="card mb-3">
                <div class="card-body" style="height: 260px;">
                    <canvas id="chartTurnosDetalle" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="custom-header">
                            <tr>
                                <th>Fecha</th>
                                <th>Especialidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($resumenPaciente['turnos']) && count($resumenPaciente['turnos']) > 0)
                                @foreach($resumenPaciente['turnos'] as $turno)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($turno->FECHA_TURNO)->format('d/m/Y') }} @if($turno->HORA_TURNO) <br><small class="text-muted">{{ \Carbon\Carbon::parse($turno->HORA_TURNO)->format('H:i') }}</small> @endif</td>
                                        <td>{{ $turno->ESPECIALIDAD }} @if($turno->PROFESIONAL) <br><small class="text-muted">{{ $turno->PROFESIONAL }}</small> @endif</td>
                                        <td><span class="badge {{ \App\Models\Paciente::getBadgeClassPorEstado($turno->ESTADO_TURNO_ID) }}">{{ $turno->ESTADO_TURNO }}</span></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center py-4">No hay turnos para mostrar.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartTurnosDetalle');
    if (ctx) {
        const realizados = {{ $resumenPaciente['realizados'] ?? 0 }};
        const cancelados = {{ $resumenPaciente['cancelados'] ?? 0 }};
        const noAsistio = {{ $resumenPaciente['no_asistio'] ?? 0 }};

        if (realizados > 0 || cancelados > 0 || noAsistio > 0) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Realizados', 'Cancelados', 'No Asistió'],
                    datasets: [{
                        data: [realizados, cancelados, noAsistio],
                        backgroundColor: ['#198754','#ffc107','#dc3545'],
                        borderWidth: 2
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '50%' }
            });
        }
    }

    // Manejo simple del checklist (solo UI)
    const btn = document.getElementById('guardarProblemas');
    if (btn) {
        btn.addEventListener('click', function() {
            const checked = Array.from(document.querySelectorAll('#problemasForm input[type=checkbox]:checked')).map(i => i.value);
            if (checked.length === 0) {
                alert('Seleccioná al menos una opción o cancela.');
                return;
            }
            // Por ahora solo mostramos en consola y un breve aviso
            console.log('Motivos seleccionados:', checked);
            btn.innerText = 'Guardado';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            setTimeout(() => { btn.innerText = 'Guardar'; btn.classList.remove('btn-success'); btn.classList.add('btn-primary'); }, 1500);
        });
    }
});
</script>
@endsection
