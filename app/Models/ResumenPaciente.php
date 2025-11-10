<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResumenPaciente extends Model
{
    // No usamos tabla específica ya que trabajamos con consultas directas
    public $timestamps = false;

    /**
     * Ejecutar la consulta completa de resumen de pacientes
     * 
     * LÓGICA DE EVALUACIÓN:
     * - Busca turnos de los últimos 6 meses (180 días)
     * - Aplica decaimiento exponencial con período de 3 meses (90 días)
     * - Los turnos más recientes tienen más peso en la evaluación
     * - Estados considerados: 3=Cancelado, 4=No Asistió, 5=Realizado
     * - Score: 100 - (0.5 × %Cancelados + 1.0 × %No_Asistió) × 100
     * - Likert: 5★≥90, 4★≥75, 3★≥60, 2★≥40, 1★<40
     * - RESTRICCIÓN: Solo pacientes con 3+ turnos en el período
     */
    public static function obtenerResumenCompleto($documento = null, $fechaDesde = null, $fechaHasta = null)
    {
        $consulta = "
        WITH resumen_pacientes AS (
          SELECT
            tp.persona_id,
            COUNT(*) AS total,
            SUM((tp.estado_turno_id = 5) * EXP(-LN(2) * DATEDIFF(CURDATE(), tp.fecha) / 90)) AS REALIZADO,
            SUM((tp.estado_turno_id = 3) * EXP(-LN(2) * DATEDIFF(CURDATE(), tp.fecha) / 90)) AS CANCELADO,
            SUM((tp.estado_turno_id = 4) * EXP(-LN(2) * DATEDIFF(CURDATE(), tp.fecha) / 90)) AS NO_ASISTIO,
            SUM(tp.estado_turno_id = 3) / COUNT(*) AS TASA_CANCELACION,
            SUM(tp.estado_turno_id = 4) / COUNT(*) AS TASA_NO_ASISTIO,
            100 - (0.5 * (SUM(tp.estado_turno_id = 3) / COUNT(*)) + 1.0 * (SUM(tp.estado_turno_id = 4) / COUNT(*))) * 100 AS SCORE
          FROM turno_programado AS tp
          INNER JOIN agenda AS ag ON ag.id = tp.agenda_id
          INNER JOIN asignacion AS asig ON asig.id = ag.asignacion_id
          INNER JOIN especialidad AS esp ON esp.id = asig.especialidad_id
          INNER JOIN personal AS per ON per.id = asig.personal_id
          INNER JOIN persona AS med ON med.id = per.persona_id
          INNER JOIN persona AS p ON tp.persona_id = p.id
          WHERE tp.fecha >= CURDATE() - INTERVAL 180 DAY
            AND esp.nombre NOT IN ('LABORATORIO', 'TRIAGE','NO USAR CLÍNICA MÉDICA','RADIOLOGÍA GENERAL','ODONTOLOGÍA DEMANDA ESPONTÁNEA','ANATOMÍA PATOLÓGICA','PEDIATRÍA TURNOS DEL DIA','PEDIAT.DEMANDA ESPONTÁNEA')
            AND esp.nombre NOT LIKE '%UDEA%'
            AND esp.nombre NOT LIKE '%prueba%'
            AND p.apellidos NOT LIKE '%prueba%'
            AND med.apellidos NOT LIKE '%prueba%'
            AND tp.estado_turno_id IN (3, 4, 5)
            AND asig.institucion_id = 1";
            
        // Agregar filtro por documento en el CTE si se proporciona
        if ($documento) {
            $consulta .= " AND p.documento = :documento_cte";
        }
            
       $consulta .= "
          GROUP BY tp.persona_id
          HAVING total >= 3
        )
        SELECT DISTINCT
          tp.id AS TURNO_ID,
          tp.fecha AS FECHA_TURNO,
          tp.hora AS HORA_TURNO,
          CONCAT(p.apellidos, ' ', p.nombres) AS PACIENTE,
          p.documento AS DOCUMENTO,
	  CONCAT(p.telefono_codigo,'',p.telefono_numero) AS TELÉFONO,
  	  p.contacto_email_direccion AS MAIL,
          IF(ISNULL(con.evento_id),CONCAT(med.apellidos, ' ', med.nombres),CONCAT(medc.apellidos, ' ', medc.nombres)) AS PROFESIONAL,
          esp.nombre AS ESPECIALIDAD,
          dep.nombre AS DEPARTAMENTO,
          et.nombre AS ESTADO_TURNO,
          et.id AS ESTADO_TURNO_ID,
          rp.total,
          rp.REALIZADO,
          rp.CANCELADO,
          rp.NO_ASISTIO,
          rp.TASA_CANCELACION,
          rp.TASA_NO_ASISTIO,
          ROUND(rp.SCORE, 1) AS SCORE,
          CASE
            WHEN rp.SCORE >= 90 THEN 5
            WHEN rp.SCORE >= 75 THEN 4
            WHEN rp.SCORE >= 60 THEN 3
            WHEN rp.SCORE >= 40 THEN 2
            WHEN rp.SCORE IS NULL THEN NULL
            ELSE 1
          END AS LIKERT
        FROM turno_programado AS tp
        INNER JOIN agenda AS ag ON ag.id = tp.agenda_id
        INNER JOIN asignacion AS asig ON asig.id = ag.asignacion_id
        INNER JOIN especialidad AS esp ON esp.id = asig.especialidad_id
        INNER JOIN lugar AS lg ON lg.id = ag.lugar_id
        INNER JOIN personal AS per ON per.id = asig.personal_id
        INNER JOIN persona AS med ON med.id = per.persona_id
        INNER JOIN departamento AS dep ON dep.id = esp.departamento_id
        LEFT JOIN bono AS bo ON bo.turnoprogramado_id = tp.id
        LEFT JOIN item_bono AS bi ON bi.bono_id = bo.id
        LEFT JOIN prestacion AS pre ON pre.id = bi.prestacion_id
        LEFT JOIN consulta AS con ON con.id = tp.consulta_id
        LEFT JOIN personal AS perc ON perc.id = con.personal_id
        LEFT JOIN persona AS medc ON medc.id = perc.persona_id
        LEFT JOIN persona AS p ON tp.persona_id = p.id
        LEFT JOIN estado_turno AS et ON tp.estado_turno_id = et.id
        LEFT JOIN resumen_pacientes AS rp ON rp.persona_id = p.id
        WHERE asig.institucion_id = 1
          AND tp.fecha >= CURDATE() - INTERVAL 180 DAY
          AND esp.nombre NOT IN ('LABORATORIO', 'TRIAGE','NO USAR CLÍNICA MÉDICA','RADIOLOGÍA GENERAL','ODONTOLOGÍA DEMANDA ESPONTÁNEA','ANATOMÍA PATOLÓGICA','PEDIATRÍA TURNOS DEL DIA','PEDIAT. DEMANDA ESPONTÁNEA')
          AND esp.nombre NOT LIKE '%UDEA%'
          AND p.apellidos NOT LIKE '%prueba%'
          AND esp.nombre NOT LIKE '%prueba%'
          AND med.apellidos NOT LIKE '%prueba%'
          AND tp.estado_turno_id IN (3, 4, 5)
          AND rp.total IS NOT NULL";

        // Agregar filtro por documento si se proporciona
        if ($documento) {
            $consulta .= " AND p.documento = :documento";
        }

        $consulta .= " ORDER BY tp.fecha DESC, tp.hora DESC";

        $bindings = [];
        if ($documento) {
            $bindings['documento'] = $documento;
            $bindings['documento_cte'] = $documento;
        }

        return DB::connection('mysql_real')->select($consulta, $bindings);
    }

    /**
     * Verificar si existe un paciente por documento (consulta simple)
     */
    public static function verificarPacienteExiste($documento)
    {
        $consulta = "SELECT id, documento, apellidos, nombres, fecha_nacimiento, telefono_codigo, telefono_numero, contacto_email_direccion 
                     FROM persona 
                     WHERE documento = :documento 
                       AND apellidos NOT LIKE '%prueba%'
                     LIMIT 1";
        
        $resultado = DB::connection('mysql_real')->select($consulta, ['documento' => $documento]);
        
        return !empty($resultado) ? $resultado[0] : null;
    }

    /**
     * Obtener turnos básicos de un paciente (sin filtros restrictivos)
     */
    public static function obtenerTurnosPaciente($documento)
    {
        $consulta = "SELECT 
                        tp.id AS TURNO_ID,
                        tp.fecha AS FECHA_TURNO,
                        tp.hora AS HORA_TURNO,
                        CONCAT(p.apellidos, ' ', p.nombres) AS PACIENTE,
                        p.documento AS DOCUMENTO,
                        IF(ISNULL(con.evento_id),CONCAT(med.apellidos, ' ', med.nombres),CONCAT(medc.apellidos, ' ', medc.nombres)) AS PROFESIONAL,
                        esp.nombre AS ESPECIALIDAD,
                        dep.nombre AS DEPARTAMENTO,
                        et.nombre AS ESTADO_TURNO,
                        et.id AS ESTADO_TURNO_ID,
                        0 as total,
                        0 as REALIZADO,
                        0 as CANCELADO,
                        0 as NO_ASISTIO,
                        0 as TASA_CANCELACION,
                        0 as TASA_NO_ASISTIO,
                        NULL as SCORE,
                        NULL as LIKERT
                     FROM turno_programado AS tp
                     INNER JOIN agenda AS ag ON ag.id = tp.agenda_id
                     INNER JOIN asignacion AS asig ON asig.id = ag.asignacion_id
                     INNER JOIN especialidad AS esp ON esp.id = asig.especialidad_id
                     INNER JOIN lugar AS lg ON lg.id = ag.lugar_id
                     INNER JOIN personal AS per ON per.id = asig.personal_id
                     INNER JOIN persona AS med ON med.id = per.persona_id
                     INNER JOIN departamento AS dep ON dep.id = esp.departamento_id
                     LEFT JOIN bono AS bo ON bo.turnoprogramado_id = tp.id
                     LEFT JOIN item_bono AS bi ON bi.bono_id = bo.id
                     LEFT JOIN prestacion AS pre ON pre.id = bi.prestacion_id
                     LEFT JOIN consulta AS con ON con.id = tp.consulta_id
                     LEFT JOIN personal AS perc ON perc.id = con.personal_id
                     LEFT JOIN persona AS medc ON medc.id = perc.persona_id
                     INNER JOIN persona AS p ON tp.persona_id = p.id
                     INNER JOIN estado_turno AS et ON tp.estado_turno_id = et.id
                     WHERE p.documento = :documento
                       AND asig.institucion_id = 1
                       AND tp.fecha >= CURDATE() - INTERVAL 180 DAY
                       AND esp.nombre NOT LIKE '%prueba%'
                       AND p.apellidos NOT LIKE '%prueba%'
                       AND med.apellidos NOT LIKE '%prueba%'
                     ORDER BY tp.fecha DESC, tp.hora DESC
                     LIMIT 20";
        
        return DB::connection('mysql_real')->select($consulta, ['documento' => $documento]);
    }

    /**
     * Obtener resumen específico de un paciente por documento
     * 
     * Este método maneja tres escenarios:
     * 1. Paciente no existe - retorna null
     * 2. Paciente existe pero sin métricas suficientes - retorna datos básicos sin score
     * 3. Paciente con métricas completas - retorna datos completos con valoración
     */
    public static function obtenerResumenPorDocumento($documento)
    {
        // Primero verificar si el paciente existe
        $pacienteExiste = self::verificarPacienteExiste($documento);
        
        if (!$pacienteExiste) {
            return null;
        }

        // Intentar obtener el resumen completo
        $resultados = self::obtenerResumenCompleto($documento);
        
        // Si no hay resultados con la consulta compleja, obtener turnos básicos
        if (empty($resultados)) {
            $turnosBasicos = self::obtenerTurnosPaciente($documento);
            
            if (!empty($turnosBasicos)) {
                return [
                    'paciente' => $pacienteExiste->apellidos . ' ' . $pacienteExiste->nombres,
                    'documento' => $pacienteExiste->documento,
+                    'telefono' => (isset($pacienteExiste->telefono_codigo) && isset($pacienteExiste->telefono_numero)) ? ($pacienteExiste->telefono_codigo . $pacienteExiste->telefono_numero) : null,
+                    'mail' => $pacienteExiste->contacto_email_direccion ?? null,
                    'total_turnos' => count($turnosBasicos),
                    'realizados' => 0,
                    'cancelados' => 0,
                    'no_asistio' => 0,
                    'tasa_cancelacion' => 0,
                    'tasa_no_asistio' => 0,
                    'score' => null,
                    'likert' => null,
                    'turnos' => $turnosBasicos,
                    'mensaje' => 'Paciente encontrado pero sin datos suficientes para métricas'
                ];
            }
            
            // Si no hay turnos, al menos mostrar que existe
            return [
                'paciente' => $pacienteExiste->apellidos . ' ' . $pacienteExiste->nombres,
                'documento' => $pacienteExiste->documento,
+                'telefono' => (isset($pacienteExiste->telefono_codigo) && isset($pacienteExiste->telefono_numero)) ? ($pacienteExiste->telefono_codigo . $pacienteExiste->telefono_numero) : null,
+                'mail' => $pacienteExiste->contacto_email_direccion ?? null,
                'total_turnos' => 0,
                'realizados' => 0,
                'cancelados' => 0,
                'no_asistio' => 0,
                'tasa_cancelacion' => 0,
                'tasa_no_asistio' => 0,
                'score' => null,
                'likert' => null,
                'turnos' => [],
                'mensaje' => 'Paciente encontrado pero sin turnos en los últimos 180 días'
            ];
        }

        // Si hay resultados completos, procesar normalmente
        $primerResultado = $resultados[0];
        
        return [
            'paciente' => $primerResultado->PACIENTE,
            'documento' => $primerResultado->DOCUMENTO,
            'telefono' => isset($primerResultado->TELÉFONO) ? $primerResultado->TELÉFONO : (isset($primerResultado->telefono) ? $primerResultado->telefono : null),
            'mail' => isset($primerResultado->MAIL) ? $primerResultado->MAIL : (isset($primerResultado->mail) ? $primerResultado->mail : null),
            'total_turnos' => (int) $primerResultado->total,
            'realizados' => is_null($primerResultado->REALIZADO) ? 0 : round((float) $primerResultado->REALIZADO, 1),
            'cancelados' => is_null($primerResultado->CANCELADO) ? 0 : round((float) $primerResultado->CANCELADO, 1),
            'no_asistio' => is_null($primerResultado->NO_ASISTIO) ? 0 : round((float) $primerResultado->NO_ASISTIO, 1),
            'tasa_cancelacion' => is_null($primerResultado->TASA_CANCELACION) ? 0 : round(((float) $primerResultado->TASA_CANCELACION) * 100, 1),
            'tasa_no_asistio' => is_null($primerResultado->TASA_NO_ASISTIO) ? 0 : round(((float) $primerResultado->TASA_NO_ASISTIO) * 100, 1),
            'score' => is_null($primerResultado->SCORE) ? null : round((float) $primerResultado->SCORE, 1),
            'likert' => is_null($primerResultado->LIKERT) ? null : (int) $primerResultado->LIKERT,
            'turnos' => $resultados
        ];
    }

    /**
     * Convertir valor Likert a estrellas
     */
    public static function likertAEstrellas($likert)
    {
        return $likert ?? 0;
    }

    /**
     * Obtener clase CSS para el score
     */
    public static function getClaseScore($score)
    {
        if ($score >= 90) return 'text-success';
        if ($score >= 75) return 'text-info';
        if ($score >= 60) return 'text-warning';
        if ($score >= 40) return 'text-orange';
        return 'text-danger';
    }

    /**
     * Obtener descripción del score
     */
    public static function getDescripcionScore($score)
    {
        if ($score >= 90) return 'Excelente';
        if ($score >= 75) return 'Muy Bueno';
        if ($score >= 60) return 'Bueno';
        if ($score >= 40) return 'Regular';
        return 'Necesita Mejorar';
    }

    /**
     * Buscar pacientes por estado específico en un período de tiempo
     * 
     * @param int $estadoId Estado a buscar (3=Cancelado, 4=No Asistió, 5=Realizado)
     * @param string $fechaDesde Fecha desde (formato Y-m-d)
     * @param string $fechaHasta Fecha hasta (formato Y-m-d)
     * @param int $minTurnos Mínimo de turnos para incluir al paciente
     * @return array Pacientes que cumplen los criterios
     */
    public static function buscarPorEstado($estadoId, $fechaDesde = null, $fechaHasta = null, $minTurnos = 1)
    {
        // Establecer fechas por defecto si no se proporcionan
        if (!$fechaDesde) {
            $fechaDesde = date('Y-m-d', strtotime('-90 days'));
        }
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }

        $estadoNombre = match($estadoId) {
            3 => 'Cancelado',
            4 => 'No Asistió',
            5 => 'Realizado',
            default => 'Desconocido'
        };

        $consulta = "
        SELECT 
            p.documento,
            CONCAT(p.nombres, ' ', p.apellidos) as paciente,
            COUNT(*) as total_turnos,
            SUM(CASE WHEN tp.estado_turno_id = ? THEN 1 ELSE 0 END) as turnos_estado,
            ROUND((SUM(CASE WHEN tp.estado_turno_id = ? THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as porcentaje_estado,
            COUNT(CASE WHEN tp.estado_turno_id = 5 THEN 1 END) as realizados,
            COUNT(CASE WHEN tp.estado_turno_id = 3 THEN 1 END) as cancelados,
            COUNT(CASE WHEN tp.estado_turno_id = 4 THEN 1 END) as no_asistio,
            MAX(tp.fecha) as ultimo_turno,
            GROUP_CONCAT(
                DISTINCT CONCAT(esp.nombre, ' (', DATE_FORMAT(tp.fecha, '%d/%m'), ')')
                ORDER BY tp.fecha DESC
                SEPARATOR ', '
            ) as especialidades_fechas
        FROM turno_programado tp
        INNER JOIN agenda ag ON ag.id = tp.agenda_id
        INNER JOIN asignacion asig ON asig.id = ag.asignacion_id
        INNER JOIN especialidad esp ON esp.id = asig.especialidad_id
        INNER JOIN personal per ON per.id = asig.personal_id
        INNER JOIN persona med ON med.id = per.persona_id
        INNER JOIN persona p ON tp.persona_id = p.id
        WHERE tp.fecha BETWEEN ? AND ?
            AND esp.nombre NOT IN ('LABORATORIO', 'TRIAGE','NO USAR CLÍNICA MÉDICA','RADIOLOGÍA GENERAL','ODONTOLOGÍA DEMANDA ESPONTÁNEA','ANATOMÍA PATOLÓGICA','PEDIATRÍA TURNOS DEL DIA','PEDIAT.DEMANDA ESPONTÁNEA')
            AND esp.nombre NOT LIKE '%UDEA%'
            AND esp.nombre NOT LIKE '%prueba%'
            AND p.apellidos NOT LIKE '%prueba%'
            AND med.apellidos NOT LIKE '%prueba%'
            AND tp.estado_turno_id IN (3, 4, 5)
        GROUP BY p.id, p.documento, p.nombres, p.apellidos
        HAVING COUNT(*) >= ?
            AND turnos_estado > 0
        ORDER BY porcentaje_estado DESC, turnos_estado DESC, ultimo_turno DESC
        LIMIT 100
        ";

        try {
            $resultados = DB::connection('mysql_real')->select($consulta, [
                $estadoId, $estadoId, $fechaDesde, $fechaHasta, $minTurnos
            ]);

            return [
                'pacientes' => $resultados,
                'estado_buscado' => $estadoNombre,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'total_encontrados' => count($resultados),
                'min_turnos' => $minTurnos
            ];

        } catch (\Exception $e) {
            Log::error('Error en búsqueda por estado: ' . $e->getMessage());
            return [
                'pacientes' => [],
                'estado_buscado' => $estadoNombre,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'total_encontrados' => 0,
                'error' => 'Error en la consulta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar pacientes con la valoración más baja según la fórmula (SCORE < 40 / LIKERT = 1)
     *
     * @param string|null $fechaDesde
     * @param string|null $fechaHasta
     * @param int $minTurnos
     * @param int $limit
     * @return array
     */
    public static function buscarPorValoracionBaja($fechaDesde = null, $fechaHasta = null, $minTurnos = 3, $limit = 100)
    {
        // Fechas por defecto (últimos 180 días ya que la valoración se calcula en ese período)
        if (!$fechaDesde) {
            $fechaDesde = date('Y-m-d', strtotime('-180 days'));
        }
        if (!$fechaHasta) {
            $fechaHasta = date('Y-m-d');
        }

        $consulta = "
        WITH resumen_pacientes AS (
          SELECT
            tp.persona_id,
            COUNT(*) AS total,
            SUM((tp.estado_turno_id = 5) * EXP(-LN(2) * DATEDIFF(CURDATE(), tp.fecha) / 90)) AS REALIZADO,
            SUM((tp.estado_turno_id = 3) * EXP(-LN(2) * DATEDIFF(CURDATE(), tp.fecha) / 90)) AS CANCELADO,
            SUM((tp.estado_turno_id = 4) * EXP(-LN(2) * DATEDIFF(CURDATE(), tp.fecha) / 90)) AS NO_ASISTIO,
            SUM(tp.estado_turno_id = 3) / COUNT(*) AS TASA_CANCELACION,
            SUM(tp.estado_turno_id = 4) / COUNT(*) AS TASA_NO_ASISTIO,
            100 - (0.5 * (SUM(tp.estado_turno_id = 3) / COUNT(*)) + 1.0 * (SUM(tp.estado_turno_id = 4) / COUNT(*))) * 100 AS SCORE
          FROM turno_programado AS tp
          INNER JOIN agenda AS ag ON ag.id = tp.agenda_id
          INNER JOIN asignacion AS asig ON asig.id = ag.asignacion_id
          INNER JOIN especialidad AS esp ON esp.id = asig.especialidad_id
          INNER JOIN personal AS per ON per.id = asig.personal_id
          INNER JOIN persona AS med ON med.id = per.persona_id
          INNER JOIN persona AS p ON tp.persona_id = p.id
          WHERE tp.fecha BETWEEN ? AND ?
            AND esp.nombre NOT IN ('LABORATORIO', 'TRIAGE','NO USAR CLÍNICA MÉDICA','RADIOLOGÍA GENERAL','ODONTOLOGÍA DEMANDA ESPONTÁNEA','ANATOMÍA PATOLÓGICA','PEDIATRÍA TURNOS DEL DIA','PEDIAT. DEMANDA ESPONTÁNEA')
            AND esp.nombre NOT LIKE '%UDEA%'
            AND esp.nombre NOT LIKE '%prueba%'
            AND p.apellidos NOT LIKE '%prueba%'
            AND med.apellidos NOT LIKE '%prueba%'
            AND tp.estado_turno_id IN (3, 4, 5)
            AND asig.institucion_id = 1
          GROUP BY tp.persona_id
          HAVING total >= ?
        )
        SELECT
          p.documento,
          CONCAT(p.apellidos, ' ', p.nombres) AS paciente,
          CONCAT(p.telefono_codigo, '', p.telefono_numero) AS telefono,
          p.contacto_email_direccion AS mail,
          rp.total AS total_turnos,
          ROUND(rp.SCORE, 1) AS score,
          CASE
            WHEN rp.SCORE >= 90 THEN 5
            WHEN rp.SCORE >= 75 THEN 4
            WHEN rp.SCORE >= 60 THEN 3
            WHEN rp.SCORE >= 40 THEN 2
            ELSE 1
          END AS likert,
          ROUND(rp.REALIZADO, 1) AS realizados,
          ROUND(rp.CANCELADO, 1) AS cancelados,
          ROUND(rp.NO_ASISTIO, 1) AS no_asistio,
          MAX(tp.fecha) AS ultimo_turno,
          GROUP_CONCAT(DISTINCT CONCAT(esp.nombre, ' (', DATE_FORMAT(tp.fecha, '%d/%m'), ')') ORDER BY tp.fecha DESC SEPARATOR ', ') AS especialidades_fechas
        FROM turno_programado AS tp
        INNER JOIN agenda AS ag ON ag.id = tp.agenda_id
        INNER JOIN asignacion AS asig ON asig.id = ag.asignacion_id
        INNER JOIN especialidad AS esp ON esp.id = asig.especialidad_id
        INNER JOIN personal AS per ON per.id = asig.personal_id
        INNER JOIN persona AS med ON med.id = per.persona_id
        INNER JOIN persona AS p ON tp.persona_id = p.id
        LEFT JOIN resumen_pacientes AS rp ON rp.persona_id = p.id
        WHERE rp.total IS NOT NULL
        GROUP BY p.id, p.documento, p.apellidos, p.nombres, p.telefono_codigo, p.telefono_numero, p.contacto_email_direccion, rp.total, rp.SCORE, rp.REALIZADO, rp.CANCELADO, rp.NO_ASISTIO
        HAVING rp.total >= ?
          AND rp.SCORE < 40
        ORDER BY rp.SCORE ASC, rp.total DESC
        LIMIT ?
        ";

        try {
            $resultados = DB::connection('mysql_real')->select($consulta, [
                $fechaDesde, $fechaHasta, $minTurnos,
                $minTurnos, $limit
            ]);

            return [
                'pacientes' => $resultados,
                'estado_buscado' => 'Valoración más baja',
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'total_encontrados' => count($resultados),
                'min_turnos' => $minTurnos
            ];

        } catch (\Exception $e) {
            Log::error('Error en búsqueda por valoración baja: ' . $e->getMessage());
            return [
                'pacientes' => [],
                'estado_buscado' => 'Valoración más baja',
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'total_encontrados' => 0,
                'error' => 'Error en la consulta: ' . $e->getMessage()
            ];
        }
    }
}
