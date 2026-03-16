<?php
// validar_login.php
session_start();
header('Content-Type: application/json');

require_once 'conexion.php';

// Verificar conexión
if (!isset($conn) || $conn === false) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la BD']);
    exit;
}

$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($usuario) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Complete todos los campos']);
    exit;
}

// Consultar usuario incluyendo TODOS los campos necesarios
$sql = "SELECT id, usuario, password, nombre_completo, email, rol, microred, activo 
        FROM usuarios 
        WHERE usuario = ?";
$params = array($usuario);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
    sqlsrv_close($conn);
    exit;
}

if (sqlsrv_has_rows($stmt)) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    // Verificar si el usuario está activo
    if ($row['activo'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario inactivo']);
        exit;
    }
    
    // Verificar contraseña (comparación directa por ahora)
    if ($password === $row['password']) {
        // Login exitoso - GUARDAR TODOS LOS DATOS EN SESIÓN
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['nombre_completo'] = $row['nombre_completo'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['rol'] = $row['rol'];
        $_SESSION['microred'] = $row['microred']; // ← ESTA ES LA LÍNEA CLAVE
        
        // Actualizar último acceso
        $sql_update = "UPDATE usuarios SET ultimo_acceso = GETDATE() WHERE id = ?";
        $params_update = array($row['id']);
        sqlsrv_query($conn, $sql_update, $params_update);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'redirect' => 'index.php',
            'usuario' => [
                'nombre' => $row['nombre_completo'],
                'rol' => $row['rol'],
                'microred' => $row['microred']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>