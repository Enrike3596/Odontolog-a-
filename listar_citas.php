<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexionDB.php';

$sql = "SELECT c.id, c.fecha, c.hora, c.odontologo, c.estado, p.nombre AS paciente
        FROM citas c
        INNER JOIN pacientes p ON c.paciente_id = p.id
        ORDER BY c.fecha, c.hora";

$result = pg_query($conn, $sql);

$citas = [];
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $citas[] = $row;
    }
}
echo json_encode($citas, JSON_UNESCAPED_UNICODE);
// No se cierra la conexión explícitamente con pg_connect
?>
