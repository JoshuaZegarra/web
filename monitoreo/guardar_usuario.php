<?php
// guardar_usuario.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

// Verificar si es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener datos del formulario
$id = isset($_POST['id']) ? $_POST['id'] : '';
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$nombre_completo = isset($_POST['nombre_completo']) ? trim($_POST['nombre_completo']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$rol = isset($_POST['rol']) ? $_POST['rol'] : 'usuario';
$microred = isset($_POST['microred']) ? $_POST['microred'] : 'TODAS';
$activo = isset($_POST['activo']) ? 1 : 0;

// Validaciones
if (empty($usuario) || empty($nombre_completo) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Complete todos los campos obligatorios']);
    exit;
}

if (empty($id) && empty($password)) {
    echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios']);
    exit;
}

if (!empty($password) && strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

// Verificar si el usuario ya existe (para nuevos usuarios)
if (empty($id)) {
    $sql_check = "SELECT id FROM usuarios WHERE usuario = ?";
    $params_check = array($usuario);
    $stmt_check = sqlsrv_query($conn, $sql_check, $params_check);
    
    if ($stmt_check && sqlsrv_has_rows($stmt_check)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe']);
        sqlsrv_free_stmt($stmt_check);
        sqlsrv_close($conn);
        exit;
    }
    sqlsrv_free_stmt($stmt_check);
}

if (!empty($id)) {
    // ACTUALIZAR usuario existente
    if (!empty($password)) {
        // Con contraseña nueva
        $sql = "UPDATE usuarios SET 
                usuario = ?, 
                password = ?, 
                nombre_completo = ?, 
                email = ?, 
                rol = ?, 
                microred = ?, 
                activo = ? 
                WHERE id = ?";
        $params = array($usuario, $password, $nombre_completo, $email, $rol, $microred, $activo, $id);
    } else {
        // Sin cambiar contraseña
        $sql = "UPDATE usuarios SET 
                usuario = ?, 
                nombre_completo = ?, 
                email = ?, 
                rol = ?, 
                microred = ?, 
                activo = ? 
                WHERE id = ?";
        $params = array($usuario, $nombre_completo, $email, $rol, $microred, $activo, $id);
    }
} else {
    // NUEVO usuario
    $sql = "INSERT INTO usuarios (usuario, password, nombre_completo, email, rol, microred, activo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $params = array($usuario, $password, $nombre_completo, $email, $rol, $microred, $activo);
}

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . print_r($errors, true)]);
    sqlsrv_close($conn);
    exit;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

$mensaje = empty($id) ? 'Usuario creado exitosamente' : 'Usuario actualizado exitosamente';
echo json_encode(['success' => true, 'message' => $mensaje]);
?>