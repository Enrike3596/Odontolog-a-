<?php
// Activar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Configurar header antes que cualquier salida
header('Content-Type: application/json; charset=utf-8');

$response = ["success" => false, "message" => "", "debug_info" => []];

try {
    // Verificar si el archivo de conexión existe
    if (!file_exists('conexionDB.php')) {
        throw new Exception("El archivo conexionDB.php no existe");
    }
    
    // Incluir conexión
    include 'conexionDB.php';

    // Verificar si la conexión se estableció
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("No se pudo establecer la conexión a la base de datos");
    }

    // Verificar método HTTP
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Método no permitido. Se requiere POST, recibido: " . $_SERVER["REQUEST_METHOD"]);
    }

    // Debug: mostrar datos recibidos
    $response["debug_info"]["post_data"] = $_POST;
    $response["debug_info"]["method"] = $_SERVER["REQUEST_METHOD"];

    // Verificar que lleguen datos POST
    if (empty($_POST)) {
        throw new Exception("No se recibieron datos POST");
    }

    // Obtener y sanitizar datos
    $nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
    $telefono  = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $correo    = isset($_POST['correo']) ? trim($_POST['correo']) : '';

    $response["debug_info"]["datos_recibidos"] = [
        "nombre" => $nombre,
        "documento" => $documento, 
        "telefono" => $telefono,
        "correo" => $correo
    ];

    // Validar campos obligatorios
    if (empty($nombre)) {
        throw new Exception("El campo nombre es obligatorio");
    }
    if (empty($documento)) {
        throw new Exception("El campo documento es obligatorio");
    }
    if (empty($telefono)) {
        throw new Exception("El campo teléfono es obligatorio");
    }
    if (empty($correo)) {
        throw new Exception("El campo correo es obligatorio");
    }

    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El formato del correo electrónico no es válido");
    }

    // Verificar si ya existe el documento
    $sql_check = "SELECT id FROM pacientes WHERE documento = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $documento);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        throw new Exception("Ya existe un paciente registrado con el documento: " . $documento);
    }
    $stmt_check->close();

    // Insertar nuevo paciente
    $sql_insert = "INSERT INTO pacientes (nombre, documento, telefono, correo) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $nombre, $documento, $telefono, $correo);
    if (!$stmt_insert->execute()) {
        throw new Exception("Error al insertar paciente: " . $stmt_insert->error);
    }
    $nuevo_id = $stmt_insert->insert_id;
    $stmt_insert->close();

    $response["success"] = true;
    $response["message"] = "Paciente registrado exitosamente";
    $response["id"] = $nuevo_id;
    $response["debug_info"]["nuevo_id"] = $nuevo_id;
    
} catch (Exception $e) {
    $response["success"] = false;
    $response["message"] = $e->getMessage();
    $response["debug_info"]["error"] = $e->getMessage();
    $response["debug_info"]["file"] = __FILE__;
    $response["debug_info"]["line"] = $e->getLine();
    
    // Log del error
    error_log("Error en crear_paciente.php: " . $e->getMessage());
} catch (Error $e) {
    $response["success"] = false;
    $response["message"] = "Error fatal: " . $e->getMessage();
    $response["debug_info"]["fatal_error"] = $e->getMessage();
    
    error_log("Error fatal en crear_paciente.php: " . $e->getMessage());
}

// Cerrar conexión si existe
// No se cierra la conexión explícitamente con pg_connect

// Enviar respuesta JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>