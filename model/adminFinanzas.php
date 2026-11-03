<?php
/*
 * model/adminFinanzas.php
 * Soporta filtros de fecha en TODOS los métodos.
 * Compatible con PHP 7.0+
 *
 * $filtro puede contener:
 *   'desde' => 'YYYY-MM-DD'
 *   'hasta' => 'YYYY-MM-DD'
 *   'mes'   => 'YYYY-MM'   (calcula primer y último día automáticamente)
 */

class AdminFinanzas
{
    private $pdo;

    public function __CONSTRUCT()
    {
        try {
            $this->pdo = Database::Conectar();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPER: normalizar filtro de fecha
    //  Devuelve array('desde' => 'YYYY-MM-DD'|null, 'hasta' => 'YYYY-MM-DD'|null)
    // ─────────────────────────────────────────────────────────────────────────
    private function normalizarFiltro($filtro)
    {
        $desde = null;
        $hasta = null;

        if (!empty($filtro['mes'])) {
            $parts = explode('-', $filtro['mes']);
            if (count($parts) === 2) {
                $y = (int)$parts[0];
                $m = (int)$parts[1];
                $desde = sprintf('%04d-%02d-01', $y, $m);
                $hasta = date('Y-m-t', mktime(0, 0, 0, $m, 1, $y));
            }
        } else {
            if (!empty($filtro['desde'])) $desde = $filtro['desde'];
            if (!empty($filtro['hasta'])) $hasta = $filtro['hasta'];
        }

        return array('desde' => $desde, 'hasta' => $hasta);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPER: WHERE fecha para pagos actuales (configuracion_json)
    // ─────────────────────────────────────────────────────────────────────────
    private function whereActual($f)
    {
        $w = '';
        if ($f['desde']) {
            $w .= " AND (configuracion_json->'registro_pago'->>'fecha_aprobacion')::date >= '{$f['desde']}'";
        }
        if ($f['hasta']) {
            $w .= " AND (configuracion_json->'registro_pago'->>'fecha_aprobacion')::date <= '{$f['hasta']}'";
        }
        return $w;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPER: WHERE fecha para historial_json
    // ─────────────────────────────────────────────────────────────────────────
    private function whereHist($f)
    {
        $w = '';
        if ($f['desde']) {
            $w .= " AND (pago->>'_archivado_en')::date >= '{$f['desde']}'";
        }
        if ($f['hasta']) {
            $w .= " AND (pago->>'_archivado_en')::date <= '{$f['hasta']}'";
        }
        return $w;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  KPIs GENERALES
    // ═══════════════════════════════════════════════════════════════════════════
    public function obtenerKpis($filtro = array())
    {
        $f  = $this->normalizarFiltro($filtro);
        $wA = $this->whereActual($f);
        $wH = $this->whereHist($f);

        $sqlA = "
            SELECT
                COUNT(*)                                                               AS total_pagos,
                COALESCE(SUM((configuracion_json->'registro_pago'->>'monto')::numeric), 0) AS total_monto,
                COUNT(*) FILTER (
                    WHERE DATE(configuracion_json->'registro_pago'->>'fecha_aprobacion') = CURRENT_DATE
                )                                                                      AS pagos_hoy,
                COALESCE(SUM((configuracion_json->'registro_pago'->>'monto')::numeric) FILTER (
                    WHERE DATE(configuracion_json->'registro_pago'->>'fecha_aprobacion') = CURRENT_DATE
                ), 0)                                                                  AS monto_hoy,
                COALESCE(SUM((configuracion_json->'registro_pago'->>'monto')::numeric) FILTER (
                    WHERE EXTRACT(YEAR  FROM (configuracion_json->'registro_pago'->>'fecha_aprobacion')::timestamp) = EXTRACT(YEAR  FROM NOW())
                    AND   EXTRACT(MONTH FROM (configuracion_json->'registro_pago'->>'fecha_aprobacion')::timestamp) = EXTRACT(MONTH FROM NOW())
                ), 0)                                                                  AS monto_mes
            FROM usuarios.usuarios
            WHERE rol = 1
              AND configuracion_json->'registro_pago'->>'estado'     = 'activo_pagado'
              AND configuracion_json->'registro_pago'->>'verificado' = 'true'
              $wA
        ";
        $actuales = $this->pdo->query($sqlA)->fetch(PDO::FETCH_ASSOC);

        $sqlH = "
            SELECT
                COUNT(*)                                                   AS total_pagos,
                COALESCE(SUM((pago->>'monto')::numeric), 0)               AS total_monto,
                COALESCE(SUM((pago->>'monto')::numeric) FILTER (
                    WHERE DATE((pago->>'_archivado_en')::timestamp) = CURRENT_DATE
                ), 0)                                                      AS monto_hoy,
                COALESCE(SUM((pago->>'monto')::numeric) FILTER (
                    WHERE EXTRACT(YEAR  FROM (pago->>'_archivado_en')::timestamp) = EXTRACT(YEAR  FROM NOW())
                    AND   EXTRACT(MONTH FROM (pago->>'_archivado_en')::timestamp) = EXTRACT(MONTH FROM NOW())
                ), 0)                                                      AS monto_mes
            FROM usuarios.usuarios,
                 jsonb_array_elements(historial_json) AS pago
            WHERE rol = 1
              AND historial_json IS NOT NULL
              AND jsonb_array_length(historial_json) > 0
              AND pago->>'estado' = 'activo_pagado'
              $wH
        ";
        $hist = $this->pdo->query($sqlH)->fetch(PDO::FETCH_ASSOC);

        $sqlP = "
            SELECT
                COUNT(*)                                                               AS total_pendientes,
                COALESCE(SUM((configuracion_json->'registro_pago'->>'monto')::numeric), 0) AS monto_pendiente
            FROM usuarios.usuarios
            WHERE rol = 1
              AND configuracion_json->'registro_pago'->>'estado' = 'pendiente_pago'
        ";
        $pend = $this->pdo->query($sqlP)->fetch(PDO::FETCH_ASSOC);

        return array(
            'total_confirmado' => (float)$actuales['total_monto']  + (float)$hist['total_monto'],
            'total_pagos'      => (int)$actuales['total_pagos']    + (int)$hist['total_pagos'],
            'monto_mes'        => (float)$actuales['monto_mes']    + (float)$hist['monto_mes'],
            'monto_hoy'        => (float)$actuales['monto_hoy']    + (float)$hist['monto_hoy'],
            'pagos_hoy'        => (int)$actuales['pagos_hoy'],
            'total_pendiente'  => (float)$pend['monto_pendiente'],
            'pagos_pendientes' => (int)$pend['total_pendientes'],
            '_desde'           => $f['desde'],
            '_hasta'           => $f['hasta'],
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  INGRESOS POR DÍA
    // ═══════════════════════════════════════════════════════════════════════════
    public function ingresosPorDia($dias = 30, $filtro = array())
    {
        $f = $this->normalizarFiltro($filtro);

        $desde = $f['desde'] ? $f['desde'] : date('Y-m-d', strtotime("-{$dias} days"));
        $hasta = $f['hasta'] ? $f['hasta'] : date('Y-m-d');

        $serie = array();
        $cur   = $desde;
        while ($cur <= $hasta) {
            $serie[$cur] = 0;
            $cur = date('Y-m-d', strtotime($cur . ' +1 day'));
        }

        $sqlA = "
            SELECT
                DATE(configuracion_json->'registro_pago'->>'fecha_aprobacion') AS dia,
                SUM((configuracion_json->'registro_pago'->>'monto')::numeric)  AS monto
            FROM usuarios.usuarios
            WHERE rol = 1
              AND configuracion_json->'registro_pago'->>'estado'     = 'activo_pagado'
              AND configuracion_json->'registro_pago'->>'verificado' = 'true'
              AND (configuracion_json->'registro_pago'->>'fecha_aprobacion')::date
                  BETWEEN '$desde' AND '$hasta'
            GROUP BY 1
        ";
        foreach ($this->pdo->query($sqlA)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($serie[$r['dia']])) $serie[$r['dia']] += (float)$r['monto'];
        }

        $sqlH = "
            SELECT
                DATE((pago->>'_archivado_en')::timestamp) AS dia,
                SUM((pago->>'monto')::numeric)            AS monto
            FROM usuarios.usuarios,
                 jsonb_array_elements(historial_json) AS pago
            WHERE rol = 1
              AND historial_json IS NOT NULL
              AND jsonb_array_length(historial_json) > 0
              AND pago->>'estado' = 'activo_pagado'
              AND (pago->>'_archivado_en')::date BETWEEN '$desde' AND '$hasta'
            GROUP BY 1
        ";
        foreach ($this->pdo->query($sqlH)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($serie[$r['dia']])) $serie[$r['dia']] += (float)$r['monto'];
        }

        $result = array();
        foreach ($serie as $fecha => $monto) {
            $result[] = array('fecha' => $fecha, 'monto' => $monto);
        }
        return $result;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  INGRESOS POR MES
    // ═══════════════════════════════════════════════════════════════════════════
    public function ingresosPorMes($meses = 6, $filtro = array())
    {
        $f = $this->normalizarFiltro($filtro);

        $serie = array();
        if ($f['desde'] && $f['hasta']) {
            // Construir serie entre los meses del rango
            $cur = substr($f['desde'], 0, 7); // YYYY-MM
            $fin = substr($f['hasta'], 0, 7);
            while ($cur <= $fin) {
                $serie[$cur] = 0;
                $parts = explode('-', $cur);
                $y = (int)$parts[0];
                $m = (int)$parts[1];
                $m++;
                if ($m > 12) { $m = 1; $y++; }
                $cur = sprintf('%04d-%02d', $y, $m);
            }
        } else {
            for ($i = $meses - 1; $i >= 0; $i--) {
                $serie[date('Y-m', strtotime("-{$i} months"))] = 0;
            }
        }

        $wA = $this->whereActual($f);
        $wH = $this->whereHist($f);

        $sqlA = "
            SELECT
                TO_CHAR((configuracion_json->'registro_pago'->>'fecha_aprobacion')::timestamp, 'YYYY-MM') AS mes,
                SUM((configuracion_json->'registro_pago'->>'monto')::numeric) AS monto
            FROM usuarios.usuarios
            WHERE rol = 1
              AND configuracion_json->'registro_pago'->>'estado'     = 'activo_pagado'
              AND configuracion_json->'registro_pago'->>'verificado' = 'true'
              $wA
            GROUP BY 1
        ";
        foreach ($this->pdo->query($sqlA)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($serie[$r['mes']])) $serie[$r['mes']] += (float)$r['monto'];
            else $serie[$r['mes']] = (float)$r['monto'];
        }

        $sqlH = "
            SELECT
                TO_CHAR((pago->>'_archivado_en')::timestamp, 'YYYY-MM') AS mes,
                SUM((pago->>'monto')::numeric)                          AS monto
            FROM usuarios.usuarios,
                 jsonb_array_elements(historial_json) AS pago
            WHERE rol = 1
              AND historial_json IS NOT NULL
              AND jsonb_array_length(historial_json) > 0
              AND pago->>'estado' = 'activo_pagado'
              $wH
            GROUP BY 1
        ";
        foreach ($this->pdo->query($sqlH)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($serie[$r['mes']])) $serie[$r['mes']] += (float)$r['monto'];
            else $serie[$r['mes']] = (float)$r['monto'];
        }

        ksort($serie);
        $result = array();
        foreach ($serie as $mes => $monto) {
            $result[] = array('mes' => $mes, 'monto' => $monto);
        }
        return $result;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  INGRESOS POR PLAN
    // ═══════════════════════════════════════════════════════════════════════════
    public function ingresosPorPlan($filtro = array())
    {
        $f  = $this->normalizarFiltro($filtro);
        $wA = $this->whereActual($f);
        $wH = $this->whereHist($f);
        $planes = array();

        $sqlA = "
            SELECT
                configuracion_json->'registro_pago'->>'tipo_plan' AS plan,
                configuracion_json->'registro_pago'->>'periodo'   AS periodo,
                COUNT(*)                                           AS cantidad,
                SUM((configuracion_json->'registro_pago'->>'monto')::numeric) AS monto
            FROM usuarios.usuarios
            WHERE rol = 1
              AND configuracion_json->'registro_pago'->>'estado'     = 'activo_pagado'
              AND configuracion_json->'registro_pago'->>'verificado' = 'true'
              $wA
            GROUP BY 1, 2
        ";
        foreach ($this->pdo->query($sqlA)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $k = $r['plan'] . '|' . $r['periodo'];
            $planes[$k] = array('plan'=>$r['plan'],'periodo'=>$r['periodo'],
                                'cantidad'=>(int)$r['cantidad'],'monto'=>(float)$r['monto']);
        }

        $sqlH = "
            SELECT
                pago->>'tipo_plan' AS plan,
                pago->>'periodo'   AS periodo,
                COUNT(*)           AS cantidad,
                SUM((pago->>'monto')::numeric) AS monto
            FROM usuarios.usuarios,
                 jsonb_array_elements(historial_json) AS pago
            WHERE rol = 1
              AND historial_json IS NOT NULL
              AND jsonb_array_length(historial_json) > 0
              AND pago->>'estado' = 'activo_pagado'
              $wH
            GROUP BY 1, 2
        ";
        foreach ($this->pdo->query($sqlH)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $k = $r['plan'] . '|' . $r['periodo'];
            if (isset($planes[$k])) {
                $planes[$k]['cantidad'] += (int)$r['cantidad'];
                $planes[$k]['monto']    += (float)$r['monto'];
            } else {
                $planes[$k] = array('plan'=>$r['plan'],'periodo'=>$r['periodo'],
                                    'cantidad'=>(int)$r['cantidad'],'monto'=>(float)$r['monto']);
            }
        }

        $planesList = array_values($planes);
        usort($planesList, array($this, '_sortMonto'));
        return $planesList;
    }

    public function _sortMonto($a, $b) {
        if ($b['monto'] > $a['monto']) return 1;
        if ($b['monto'] < $a['monto']) return -1;
        return 0;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  LISTADO DE PAGOS CONFIRMADOS
    // ═══════════════════════════════════════════════════════════════════════════
    public function listadoPagosConfirmados($limit = 25, $offset = 0, $filtro = array())
    {
        $f  = $this->normalizarFiltro($filtro);
        $wA = $this->whereActual($f);
        $wH = $this->whereHist($f);
        $pagos = array();

        $sqlA = "
            SELECT
                u.id AS usuario_id, u.nombre, u.apellido, u.email,
                (configuracion_json->'registro_pago'->>'monto')::numeric AS monto,
                configuracion_json->'registro_pago'->>'tipo_plan'        AS plan,
                configuracion_json->'registro_pago'->>'periodo'          AS periodo,
                configuracion_json->'registro_pago'->>'motivo_pago'      AS motivo,
                configuracion_json->'registro_pago'->>'fecha_pago'       AS fecha_pago,
                configuracion_json->'registro_pago'->>'fecha_aprobacion' AS fecha_confirmacion,
                configuracion_json->'registro_pago'->>'aprobado_por'     AS aprobado_por,
                'activo' AS origen
            FROM usuarios.usuarios u
            WHERE u.rol = 1
              AND configuracion_json->'registro_pago'->>'estado'     = 'activo_pagado'
              AND configuracion_json->'registro_pago'->>'verificado' = 'true'
              $wA
        ";
        foreach ($this->pdo->query($sqlA)->fetchAll(PDO::FETCH_ASSOC) as $r) $pagos[] = $r;

        $sqlH = "
            SELECT
                u.id AS usuario_id, u.nombre, u.apellido, u.email,
                (pago->>'monto')::numeric AS monto,
                pago->>'tipo_plan'        AS plan,
                pago->>'periodo'          AS periodo,
                pago->>'motivo_pago'      AS motivo,
                pago->>'fecha_pago'       AS fecha_pago,
                pago->>'_archivado_en'    AS fecha_confirmacion,
                pago->>'aprobado_por'     AS aprobado_por,
                'historial' AS origen
            FROM usuarios.usuarios u,
                 jsonb_array_elements(u.historial_json) AS pago
            WHERE u.rol = 1
              AND u.historial_json IS NOT NULL
              AND jsonb_array_length(u.historial_json) > 0
              AND pago->>'estado' = 'activo_pagado'
              $wH
        ";
        foreach ($this->pdo->query($sqlH)->fetchAll(PDO::FETCH_ASSOC) as $r) $pagos[] = $r;

        usort($pagos, array($this, '_sortFecha'));

        return array('pagos' => array_slice($pagos, $offset, $limit), 'total' => count($pagos));
    }

    public function _sortFecha($a, $b) {
        $ta = isset($a['fecha_confirmacion']) ? strtotime($a['fecha_confirmacion']) : 0;
        $tb = isset($b['fecha_confirmacion']) ? strtotime($b['fecha_confirmacion']) : 0;
        return $tb - $ta;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  TOP CLIENTES
    // ═══════════════════════════════════════════════════════════════════════════
    public function topClientes($limit = 10, $filtro = array())
    {
        $f  = $this->normalizarFiltro($filtro);
        $wA = $this->whereActual($f);
        $wH = $this->whereHist($f);
        $clientes = array();

        $sqlA = "
            SELECT u.id, u.nombre, u.apellido, u.email,
                   (configuracion_json->'registro_pago'->>'monto')::numeric AS monto
            FROM usuarios.usuarios u
            WHERE u.rol = 1
              AND configuracion_json->'registro_pago'->>'estado'     = 'activo_pagado'
              AND configuracion_json->'registro_pago'->>'verificado' = 'true'
              $wA
        ";
        foreach ($this->pdo->query($sqlA)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $id = (int)$r['id'];
            if (!isset($clientes[$id])) {
                $clientes[$id] = array('id'=>$id,'nombre'=>$r['nombre'],'apellido'=>$r['apellido'],
                                       'email'=>$r['email'],'total'=>0,'pagos'=>0);
            }
            $clientes[$id]['total'] += (float)$r['monto'];
            $clientes[$id]['pagos']++;
        }

        $sqlH = "
            SELECT u.id, u.nombre, u.apellido, u.email,
                   SUM((pago->>'monto')::numeric) AS monto, COUNT(*) AS pagos
            FROM usuarios.usuarios u,
                 jsonb_array_elements(u.historial_json) AS pago
            WHERE u.rol = 1
              AND u.historial_json IS NOT NULL
              AND jsonb_array_length(u.historial_json) > 0
              AND pago->>'estado' = 'activo_pagado'
              $wH
            GROUP BY u.id, u.nombre, u.apellido, u.email
        ";
        foreach ($this->pdo->query($sqlH)->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $id = (int)$r['id'];
            if (!isset($clientes[$id])) {
                $clientes[$id] = array('id'=>$id,'nombre'=>$r['nombre'],'apellido'=>$r['apellido'],
                                       'email'=>$r['email'],'total'=>0,'pagos'=>0);
            }
            $clientes[$id]['total'] += (float)$r['monto'];
            $clientes[$id]['pagos'] += (int)$r['pagos'];
        }

        $lista = array_values($clientes);
        usort($lista, array($this, '_sortTotal'));
        return array_slice($lista, 0, $limit);
    }

    public function _sortTotal($a, $b) {
        if ($b['total'] > $a['total']) return 1;
        if ($b['total'] < $a['total']) return -1;
        return 0;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  AÑOS DISPONIBLES — para el combobox del filtro
    // ═══════════════════════════════════════════════════════════════════════════
    public function aniosDisponibles()
    {
        $anios = array();

        try {
            $sqlA = "
                SELECT DISTINCT
                    EXTRACT(YEAR FROM (configuracion_json->'registro_pago'->>'fecha_aprobacion')::timestamp)::int AS anio
                FROM usuarios.usuarios
                WHERE rol = 1
                  AND configuracion_json->'registro_pago'->>'estado' = 'activo_pagado'
                  AND configuracion_json->'registro_pago'->>'fecha_aprobacion' IS NOT NULL
                  AND configuracion_json->'registro_pago'->>'fecha_aprobacion' != ''
            ";
            foreach ($this->pdo->query($sqlA)->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if ($r['anio']) $anios[] = (int)$r['anio'];
            }
        } catch (Exception $e) {}

        try {
            $sqlH = "
                SELECT DISTINCT
                    EXTRACT(YEAR FROM (pago->>'_archivado_en')::timestamp)::int AS anio
                FROM usuarios.usuarios,
                     jsonb_array_elements(historial_json) AS pago
                WHERE rol = 1
                  AND historial_json IS NOT NULL
                  AND jsonb_array_length(historial_json) > 0
                  AND pago->>'estado' = 'activo_pagado'
                  AND pago->>'_archivado_en' IS NOT NULL
                  AND pago->>'_archivado_en' != ''
            ";
            foreach ($this->pdo->query($sqlH)->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if ($r['anio']) $anios[] = (int)$r['anio'];
            }
        } catch (Exception $e) {}

        $anios = array_unique($anios);
        rsort($anios);

        $anioActual = (int)date('Y');
        if (!in_array($anioActual, $anios)) $anios[] = $anioActual;
        rsort($anios);

        return $anios;
    }
}