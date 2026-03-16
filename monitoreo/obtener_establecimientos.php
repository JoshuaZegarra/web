<?php
// obtener_establecimientos.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

// Verificar conexión
if (!isset($conn) || $conn === false) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener microred del usuario logueado
$microred_usuario = isset($_SESSION['microred']) ? $_SESSION['microred'] : 'TODAS';

// Registrar en log para depuración
error_log("========== ESTABLECIMIENTOS ==========");
error_log("Usuario ID: " . ($_SESSION['usuario_id'] ?? 'No definido'));
error_log("Usuario: " . ($_SESSION['usuario'] ?? 'No definido'));
error_log("MicroRed desde sesión: " . $microred_usuario);

// Obtener establecimientos filtrados por microred
$sql_est = "SELECT DISTINCT 
                Nombre_Establecimiento as id, 
                Nombre_Establecimiento as nombre
            FROM dbo.INDICADOR_RN 
            WHERE Nombre_Establecimiento IS NOT NULL 
              AND Nombre_Establecimiento != ''";

$params_est = array();

// Si el usuario tiene una microred específica (no TODAS), filtrar por esa microred
if ($microred_usuario !== 'TODAS') {
    $sql_est .= " AND MicroRed = ?";
    $params_est[] = $microred_usuario;
    error_log("APLICANDO FILTRO: MicroRed = " . $microred_usuario);
} else {
    error_log("SIN FILTRO - Usuario ve TODAS las microredes");
}

$sql_est .= " ORDER BY Nombre_Establecimiento ASC";

error_log("SQL: " . $sql_est);
error_log("Params: " . print_r($params_est, true));

$stmt_est = sqlsrv_query($conn, $sql_est, $params_est);
$establecimientos = [];

if ($stmt_est === false) {
    error_log("Error en consulta: " . print_r(sqlsrv_errors(), true));
    echo json_encode(['error' => 'Error en la consulta']);
    sqlsrv_close($conn);
    exit;
}

while ($row = sqlsrv_fetch_array($stmt_est, SQLSRV_FETCH_ASSOC)) {
    $establecimientos[] = [
        'id' => trim($row['id']),
        'nombre' => trim($row['nombre'])
    ];
}

error_log("Total establecimientos encontrados: " . count($establecimientos));

// También registrar los primeros 5 establecimientos para verificar
$primeros = array_slice($establecimientos, 0, 5);
error_log("Primeros 5: " . print_r($primeros, true));

sqlsrv_free_stmt($stmt_est);

// Obtener años disponibles
$sql_años = "SELECT DISTINCT año 
             FROM dbo.INDICADOR_RN 
             WHERE año IS NOT NULL 
             ORDER BY año DESC";

$stmt_años = sqlsrv_query($conn, $sql_años);
$años = [];

if ($stmt_años !== false) {
    while ($row = sqlsrv_fetch_array($stmt_años, SQLSRV_FETCH_ASSOC)) {
        $años[] = $row['año'];
    }
    sqlsrv_free_stmt($stmt_años);
}

// Obtener meses disponibles
$sql_meses = "SELECT DISTINCT mes 
              FROM dbo.INDICADOR_RN 
              WHERE mes IS NOT NULL 
              ORDER BY mes";

$stmt_meses = sqlsrv_query($conn, $sql_meses);
$meses = [];

if ($stmt_meses !== false) {
    while ($row = sqlsrv_fetch_array($stmt_meses, SQLSRV_FETCH_ASSOC)) {
        $meses[] = $row['mes'];
    }
    sqlsrv_free_stmt($stmt_meses);
}

sqlsrv_close($conn);

$response = [
    'establecimientos' => $establecimientos,
    'años' => $años,
    'meses' => $meses
];

error_log("Respuesta enviada: " . count($establecimientos) . " establecimientos");
error_log("========================================");

echo json_encode($response);
?>