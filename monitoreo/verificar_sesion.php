<?php
// verificar_sesion.php
session_start();

echo "<h2>Verificación de Sesión</h2>";

if (isset($_SESSION['usuario_id'])) {
    echo "<p style='color:green'>✅ Sesión activa</p>";
    echo "<h3>Datos de sesión:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    echo "<h3>MicroRed del usuario:</h3>";
    if (isset($_SESSION['microred'])) {
        echo "<p style='color:blue'><strong>MicroRed: " . $_SESSION['microred'] . "</strong></p>";
    } else {
        echo "<p style='color:red'>❌ La variable 'microred' NO está definida en la sesión</p>";
    }
} else {
    echo "<p style='color:red'>❌ No hay sesión activa</p>";
    echo '<a href="login.php">Ir al Login</a>';
}

echo '<hr>';
echo '<a href="index.php">Ir al Dashboard</a><br>';
echo '<a href="logout.php">Cerrar Sesión</a>';
?>