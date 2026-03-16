<?php
// obtener_usuarios.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

// Verificar si es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Si se pide un usuario específico
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT id, usuario, nombre_completo, email, rol, microred, activo 
            FROM usuarios WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
    }
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    exit;
}

// Obtener todos los usuarios
$sql = "SELECT id, usuario, nombre_completo, email, rol, microred, activo 
        FROM usuarios ORDER BY id DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Error en la consulta']);
    sqlsrv_close($conn);
    exit;
}

$usuarios = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $usuarios[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode($usuarios);
?>