<?php
header('Content-Type: application/json; charset=utf-8'); 
include 'conexionDB.php';

$sql = "SELECT * FROM pacientes";
$result = pg_query($conn, $sql);

$pacientes = [];
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $pacientes[] = $row;
    }
}
echo json_encode($pacientes, JSON_UNESCAPED_UNICODE);
// No se cierra la conexión explícitamente con pg_connect
?>
