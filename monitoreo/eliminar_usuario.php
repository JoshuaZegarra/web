<?php
// eliminar_usuario.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

// Verificar si es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id = isset($_POST['id']) ? $_POST['id'] : '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

// No permitir eliminar el propio usuario
if ($id == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'message' => 'No puede eliminarse a sí mismo']);
    exit;
}

$sql = "DELETE FROM usuarios WHERE id = ?";
$params = array($id);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario']);
    sqlsrv_close($conn);
    exit;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
?>