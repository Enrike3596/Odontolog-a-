<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexionDB.php';

$pacientes = [];
$sql = "SELECT * FROM pacientes ORDER BY id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pacientes[] = $row;
    }
}
echo json_encode($pacientes, JSON_UNESCAPED_UNICODE);
// No se cierra la conexión explícitamente con pg_connect
// No se cierra la conexión explícitamente con MySQLi
?>
