<?php
// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si la extensión MySQLi está disponible
if (!extension_loaded('mysqli')) {
    $error_msg = "La extensión MySQLi no está instalada o habilitada en PHP.";
    $error_msg .= " Verifica tu configuración de php.ini.";
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "success" => false,
            "message" => $error_msg,
            "debug_info" => [
                "php_version" => phpversion(),
                "loaded_extensions" => get_loaded_extensions(),
                "error_type" => "missing_extension"
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        die($error_msg);
    }
}

// Datos de conexión para MySQL
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'clinica_db';
$port = 3306;

// Intentar conectar
$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    $error_details = $conn->connect_error;
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "success" => false,
            "message" => "Error de conexión a MySQL",
            "error_details" => $error_details,
            "debug_info" => [
                "host" => $host,
                "port" => $port,
                "dbname" => $dbname,
                "user" => $user
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        die("Error de conexión a MySQL: " . $error_details);
    }
}

// Establecer codificación UTF-8
$conn->set_charset('utf8');

// Conexión exitosa - opcional: log para debug
// error_log("Conexión MySQL exitosa - " . date('Y-m-d H:i:s'));
?>