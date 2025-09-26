
<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexionDB.php';

$sql = "SELECT * FROM pacientes";
$result = $conn->query($sql);

$pacientes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pacientes[] = $row;
    }
}
echo json_encode($pacientes, JSON_UNESCAPED_UNICODE);
// No se cierra la conexión explícitamente con MySQLi
?>
