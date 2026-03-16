<?php
// crear_usuario.php - Ejecutar una sola vez para crear usuario admin
require_once 'conexion.php';

// Verificar si ya existe el admin
$sql_check = "SELECT id FROM usuarios WHERE usuario = 'admin'";
$stmt_check = sqlsrv_query($conn, $sql_check);

if ($stmt_check && sqlsrv_has_rows($stmt_check)) {
    echo "El usuario admin ya existe.<br>";
    echo '<a href="login.php">Ir al Login</a>';
} else {
    // Crear usuario admin
    $sql = "INSERT INTO usuarios (usuario, password, nombre_completo, email, rol, microred, activo) 
            VALUES ('admin', 'admin123', 'Administrador', 'admin@elchochua.com', 'administrador', 'TODAS', 1)";
    
    $stmt = sqlsrv_query($conn, $sql);
    
    if ($stmt) {
        echo "Usuario admin creado exitosamente!<br>";
        echo "Usuario: admin<br>";
        echo "Contraseña: admin123<br>";
        echo '<a href="login.php">Ir al Login</a>';
    } else {
        echo "Error al crear usuario: " . print_r(sqlsrv_errors(), true);
    }
}

sqlsrv_close($conn);
?>