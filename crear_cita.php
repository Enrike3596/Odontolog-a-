<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexionDB.php';

$response = ["success" => false, "message" => ""];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $paciente_id = $_POST['paciente_id'] ?? '';
        $fecha       = $_POST['fecha'] ?? '';
        $hora        = $_POST['hora'] ?? '';
        $odontologo  = trim($_POST['odontologo'] ?? '');

        if (empty($paciente_id) || empty($fecha) || empty($hora) || empty($odontologo)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Validar que la fecha no sea pasada
        if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
            throw new Exception("No se pueden agendar citas en fechas pasadas");
        }

        // Verificar que el paciente existe
        $sql_check = "SELECT id FROM pacientes WHERE id = $1";
        $result_check = pg_query_params($conn, $sql_check, [$paciente_id]);
        if (!$result_check) {
            throw new Exception("Error al verificar paciente: " . pg_last_error($conn));
        }
        if (pg_num_rows($result_check) === 0) {
            throw new Exception("El paciente no existe");
        }

        // Insertar la cita
        $sql_insert = "INSERT INTO citas (paciente_id, fecha, hora, odontologo) VALUES ($1, $2, $3, $4) RETURNING id";
        $result_insert = pg_query_params($conn, $sql_insert, [$paciente_id, $fecha, $hora, $odontologo]);
        if (!$result_insert) {
            throw new Exception("Error al agendar la cita: " . pg_last_error($conn));
        }
        $row = pg_fetch_assoc($result_insert);
        $response["success"] = true;
        $response["message"] = "Cita agendada exitosamente";
        $response["id"] = $row['id'];
    } else {
        throw new Exception("Método no permitido");
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
// No se cierra la conexión explícitamente con pg_connect
?>