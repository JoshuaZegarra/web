<?php
// conexion.php
$serverName = "LAPTOP-C2NFDPF4"; // Tu servidor (correcto)

// Opciones de conexión - ¡SIN USUARIO Y CONTRASEÑA si usas autenticación Windows!
$connectionOptions = array(
    "Database" => "proyecto1", // VERIFICA que este sea el nombre exacto de tu BD
    "CharacterSet" => "UTF-8"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(json_encode(['error' => 'Error de conexión: ' . print_r(sqlsrv_errors(), true)]));
}
?>