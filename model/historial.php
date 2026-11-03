<?php
require_once __DIR__ . '/imagenAtraccion.php';
require_once __DIR__ . '/catalogoI18n.php';
// model/historial.php

class HistorialModel {

    private $pdo;

    public function __construct() {
        $this->pdo = Database::Conectar();
    }

    /**
     * Obtiene todos los itinerarios con estado = 'visitado' del usuario,
     * junto con imagen, ciudad y calificación si existe en back_testing_mr.
     */
    public function obtenerVisitados(int $id_usuario): array {
        try {
            // DEBUG — verificar qué usuario llega
            error_log("HistorialModel - buscando visitados para usuario_id: $id_usuario");

            // Query simplificada sin back_testing_mr para aislar el problema
            $sql = "
                SELECT
                    i.id,
                    i.nombre,
                    i.descripcion,
                    i.atracciones_id,
                    i.fecha_visita,
                    i.estado,
                    a.ciudad,
                    a.pais,
                    a.ubicacion AS ubicaciones,
                    NULL AS calificacion,
                    NULL AS nota
                FROM itinerarios.itinerarios i
                JOIN " . CatalogoI18n::atracciones() . " a ON a.id = i.atracciones_id
                WHERE i.usuario_id = ?
                  AND i.estado = 'visitado'
                ORDER BY i.fecha_visita DESC
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_usuario]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("HistorialModel - filas encontradas: " . count($rows));

            foreach ($rows as &$row) {
                $row['imagen_url'] = null;
                if (!empty($row['ubicaciones'])) {
                    $ub = is_string($row['ubicaciones'])
                        ? json_decode($row['ubicaciones'], true)
                        : $row['ubicaciones'];
                    $row['imagen_url'] = $ub['rutas'][0] ?? null;
                }
                // Foto garantizada (propia, de Wikimedia o referencial)
                $idAtr = (int)($row['atracciones_id'] ?? $row['atraccion_id'] ?? 0);
                if ($idAtr) $row['imagen_url'] = ImagenAtraccion::galeria($idAtr, 1)['fotos'][0];
                $row['categoria'] = 'Atracción';
            }

            return $rows;

        } catch (PDOException $e) {
            error_log('HistorialModel::obtenerVisitados ERROR: ' . $e->getMessage());
            return [];
        }
    }
}