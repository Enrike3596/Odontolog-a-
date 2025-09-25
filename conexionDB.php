
<?php
// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Datos de conexión para PostgreSQL
$host = 'localhost';
$port = '5432';
$dbname = 'clinica_db'; // Cambia si tu base tiene otro nombre
$user = 'postgres'; // Cambia por tu usuario de PostgreSQL
$password = '3596'; // Cambia por tu contraseña

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "success" => false,
            "message" => "Error de conexión a la base de datos PostgreSQL: " . pg_last_error(),
            "debug_info" => [
                "host" => $host,
                "port" => $port,
                "dbname" => $dbname,
                "user" => $user
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } else {
        die("Error de conexión a PostgreSQL: " . pg_last_error());
    }
}
// Conexión exitosa
?>