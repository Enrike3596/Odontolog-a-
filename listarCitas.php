<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexionDB.php';

try {
    $sql = "SELECT c.*, p.nombre as paciente_nombre 
            FROM citas c 
            INNER JOIN pacientes p ON c.paciente_id = p.id 
            ORDER BY c.fecha DESC, c.hora DESC";
            

    $result = pg_query($conn, $sql);
    if ($result === false) {
        throw new Exception("Error en la consulta: " . pg_last_error($conn));
    }
    $citas = [];
    while ($row = pg_fetch_assoc($result)) {
        $citas[] = $row;
    }
    echo json_encode($citas, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}

// No se cierra la conexión explícitamente con pg_connect
?>