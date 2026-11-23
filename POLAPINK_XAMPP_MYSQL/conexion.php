<?php
// Configura estos valores según tu instalación de XAMPP.
$host = '127.0.0.1';
$base = 'polapink';
$usuario = 'root';
$contrasena = ''; // En XAMPP local suele estar vacía; cámbiala si configuraste otra.
try {
    $pdo = new PDO("mysql:host=$host;dbname=$base;charset=utf8mb4", $usuario, $contrasena, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'No se pudo conectar a MySQL. Comprueba XAMPP y conexion.php.']);
    exit;
}
