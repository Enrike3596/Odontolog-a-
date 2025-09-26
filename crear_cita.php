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
        $sql_check = "SELECT id FROM pacientes WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $paciente_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows === 0) {
            throw new Exception("El paciente no existe");
        }
        $stmt_check->close();

        // Insertar la cita
        $sql_insert = "INSERT INTO citas (paciente_id, fecha, hora, odontologo) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isss", $paciente_id, $fecha, $hora, $odontologo);
        if (!$stmt_insert->execute()) {
            throw new Exception("Error al agendar la cita: " . $stmt_insert->error);
        }
        $nuevo_id = $stmt_insert->insert_id;
        $stmt_insert->close();

        $response["success"] = true;
        $response["message"] = "Cita agendada exitosamente";
        $response["id"] = $nuevo_id;
    } else {
        throw new Exception("Método no permitido");
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>