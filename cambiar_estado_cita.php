<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexionDB.php';

$response = ["success" => false, "message" => ""];

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $cita_id = $_POST['cita_id'] ?? '';
        $estado = $_POST['estado'] ?? '';

        if (empty($cita_id) || empty($estado)) {
            throw new Exception("ID de cita y estado son obligatorios");
        }

        // Validar que el estado sea válido
        $estados_validos = ['pendiente', 'completada', 'cancelada'];
        if (!in_array($estado, $estados_validos)) {
            throw new Exception("Estado no válido");
        }

        // Verificar que la cita existe
        $sql_check = "SELECT id FROM citas WHERE id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $cita_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows === 0) {
            throw new Exception("La cita no existe");
        }
        $stmt_check->close();

        // Actualizar el estado de la cita
        $sql_update = "UPDATE citas SET estado = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $estado, $cita_id);
        
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar el estado: " . $stmt_update->error);
        }
        $stmt_update->close();

        $response["success"] = true;
        $response["message"] = "Estado actualizado exitosamente";
    } else {
        throw new Exception("Método no permitido");
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>