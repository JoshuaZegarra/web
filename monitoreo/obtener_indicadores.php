<?php
// obtener_indicadores.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

// Verificar conexión
if (!isset($conn) || $conn === false) {
    echo json_encode(['error' => 'Error de conexión a la BD']);
    exit;
}

// Obtener microred del usuario logueado
$microred_usuario = isset($_SESSION['microred']) ? $_SESSION['microred'] : 'TODAS';

// Obtener filtros
$establecimiento = isset($_GET['establecimiento']) ? $_GET['establecimiento'] : '';
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$año = isset($_GET['año']) ? $_GET['año'] : '';

// Construir consulta base
$sql_base = "FROM dbo.INDICADOR_RN";
$params = array();
$where_agregado = false;

function agregarCondicion(&$sql_base, &$where_agregado, $nueva_condicion, &$params, $valor) {
    if ($where_agregado) {
        $sql_base .= " AND " . $nueva_condicion;
    } else {
        $sql_base .= " WHERE " . $nueva_condicion;
        $where_agregado = true;
    }
    $params[] = $valor;
}

// FILTRO POR MICRORED
if ($microred_usuario !== 'TODAS') {
    agregarCondicion($sql_base, $where_agregado, "MicroRed = ?", $params, $microred_usuario);
}

// Aplicar filtros de fecha
if (!empty($año) && $año !== 'todos') {
    agregarCondicion($sql_base, $where_agregado, "YEAR(fecha_nac) = ?", $params, $año);
}

if (!empty($mes) && $mes !== 'todos') {
    agregarCondicion($sql_base, $where_agregado, "MONTH(fecha_nac) = ?", $params, $mes);
}

// Filtrar por establecimiento
if (!empty($establecimiento) && $establecimiento !== 'todos') {
    agregarCondicion($sql_base, $where_agregado, "Id_Establecimiento = ?", $params, $establecimiento);
}

// TOTAL EVALUADOS
$sql_total = "SELECT COUNT(*) as total_evaluados " . $sql_base;
$stmt_total = sqlsrv_query($conn, $sql_total, $params);
$totalEvaluados = 0;

if ($stmt_total !== false) {
    $row_total = sqlsrv_fetch_array($stmt_total, SQLSRV_FETCH_ASSOC);
    $totalEvaluados = $row_total['total_evaluados'] ?? 0;
    sqlsrv_free_stmt($stmt_total);
}

// Función para contar indicadores
function contarIndicador($conn, $sql_base, $campo, $params) {
    if (stripos($sql_base, 'WHERE') !== false) {
        $sql = "SELECT COUNT(*) as total " . $sql_base . " AND " . $campo . " = 1";
    } else {
        $sql = "SELECT COUNT(*) as total " . $sql_base . " WHERE " . $campo . " = 1";
    }
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    $total = 0;
    
    if ($stmt !== false) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $total = $row['total'] ?? 0;
        sqlsrv_free_stmt($stmt);
    }
    
    return $total;
}

// Calcular indicadores
$totalVacunasRN = contarIndicador($conn, $sql_base, "numerador_vacuna", $params);
$totalCredRN = contarIndicador($conn, $sql_base, "num_cred_rn", $params);
$totalTamizaje = contarIndicador($conn, $sql_base, "numerador_Tamizaje_neo", $params);
$totalCredMensual = contarIndicador($conn, $sql_base, "num_cred7", $params); // Ajusta según tu lógica

// Vacunas 1 Dosis (neumococo1, rotavirus1, polio1, pentavalente1)
$sql_vacuna1 = "SELECT COUNT(*) as total " . $sql_base;
if (stripos($sql_base, 'WHERE') !== false) {
    $sql_vacuna1 .= " AND num_neumococo1 = 1 AND num_rotavirus1 = 1 AND num_polio1 = 1 AND num_pentavalente1 = 1";
} else {
    $sql_vacuna1 .= " WHERE num_neumococo1 = 1 AND num_rotavirus1 = 1 AND num_polio1 = 1 AND num_pentavalente1 = 1";
}
$stmt_vacuna1 = sqlsrv_query($conn, $sql_vacuna1, $params);
$totalVacuna1D = 0;
if ($stmt_vacuna1 !== false) {
    $row = sqlsrv_fetch_array($stmt_vacuna1, SQLSRV_FETCH_ASSOC);
    $totalVacuna1D = $row['total'] ?? 0;
    sqlsrv_free_stmt($stmt_vacuna1);
}

// Vacunas 2 Dosis
$sql_vacuna2 = "SELECT COUNT(*) as total " . $sql_base;
if (stripos($sql_base, 'WHERE') !== false) {
    $sql_vacuna2 .= " AND num_neumococo2 = 1 AND num_rotavirus2 = 1 AND num_polio2 = 1 AND num_pentavalente2 = 1";
} else {
    $sql_vacuna2 .= " WHERE num_neumococo2 = 1 AND num_rotavirus2 = 1 AND num_polio2 = 1 AND num_pentavalente2 = 1";
}
$stmt_vacuna2 = sqlsrv_query($conn, $sql_vacuna2, $params);
$totalVacuna2D = 0;
if ($stmt_vacuna2 !== false) {
    $row = sqlsrv_fetch_array($stmt_vacuna2, SQLSRV_FETCH_ASSOC);
    $totalVacuna2D = $row['total'] ?? 0;
    sqlsrv_free_stmt($stmt_vacuna2);
}

// Vacunas 3 Dosis
$sql_vacuna3 = "SELECT COUNT(*) as total " . $sql_base;
if (stripos($sql_base, 'WHERE') !== false) {
    $sql_vacuna3 .= " AND num_polio3 = 1 AND num_pentavalente3 = 1";
} else {
    $sql_vacuna3 .= " WHERE num_polio3 = 1 AND num_pentavalente3 = 1";
}
$stmt_vacuna3 = sqlsrv_query($conn, $sql_vacuna3, $params);
$totalVacuna3D = 0;
if ($stmt_vacuna3 !== false) {
    $row = sqlsrv_fetch_array($stmt_vacuna3, SQLSRV_FETCH_ASSOC);
    $totalVacuna3D = $row['total'] ?? 0;
    sqlsrv_free_stmt($stmt_vacuna3);
}

sqlsrv_close($conn);

$indicadores = [
    'totalEvaluados' => $totalEvaluados,
    'Vacunas' => $totalVacunasRN,
    'Cred_RN' => $totalCredRN,
    'Tamizaje_Neonatal' => $totalTamizaje,
    'Cred_Mensual' => $totalCredMensual,
    'Vacuna_1D' => $totalVacuna1D,
    'Vacuna_2D' => $totalVacuna2D,
    'Vacuna_3D' => $totalVacuna3D
];

echo json_encode($indicadores);
exit;
?>