<?php
// obtener_datos_graficos.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

if (!isset($conn) || $conn === false) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Obtener microred del usuario logueado
$microred_usuario = isset($_SESSION['microred']) ? $_SESSION['microred'] : 'TODAS';

// Obtener filtros
$establecimiento = isset($_GET['establecimiento']) ? $_GET['establecimiento'] : '';
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$año = isset($_GET['año']) ? $_GET['año'] : '';

// Construir condiciones
$condiciones = "";
$params = array();
$where_agregado = false;

function agregarCondicion(&$condiciones, &$where_agregado, $nueva_condicion, &$params, $valor) {
    if ($where_agregado) {
        $condiciones .= " AND " . $nueva_condicion;
    } else {
        $condiciones = "WHERE " . $nueva_condicion;
        $where_agregado = true;
    }
    $params[] = $valor;
}

// FILTRO POR MICRORED (según permisos del usuario)
if ($microred_usuario !== 'TODAS') {
    agregarCondicion($condiciones, $where_agregado, "MicroRed = ?", $params, $microred_usuario);
}

// Aplicar filtros
if (!empty($año) && $año !== 'todos') {
    agregarCondicion($condiciones, $where_agregado, "YEAR(fecha_nac) = ?", $params, $año);
}

if (!empty($mes) && $mes !== 'todos') {
    agregarCondicion($condiciones, $where_agregado, "MONTH(fecha_nac) = ?", $params, $mes);
}

if (!empty($establecimiento) && $establecimiento !== 'todos') {
    agregarCondicion($condiciones, $where_agregado, "Id_Establecimiento = ?", $params, $establecimiento);
}

// Gráfico de pastel
$sql_pie = "SELECT 
                COUNT(DISTINCT CASE WHEN numerador_Tamizaje_neo = 1 THEN num_doc END) as con_indicador,
                COUNT(DISTINCT CASE WHEN numerador_Tamizaje_neo = 0 OR numerador_Tamizaje_neo IS NULL THEN num_doc END) as sin_indicador
            FROM dbo.INDICADOR_RN
            $condiciones";

$stmt_pie = sqlsrv_query($conn, $sql_pie, $params);
$pieData = [0, 0];

if ($stmt_pie !== false) {
    $row_pie = sqlsrv_fetch_array($stmt_pie, SQLSRV_FETCH_ASSOC);
    $pieData = [
        (int)($row_pie['con_indicador'] ?? 0),
        (int)($row_pie['sin_indicador'] ?? 0)
    ];
    sqlsrv_free_stmt($stmt_pie);
}

// Gráfico de línea
$sql_linea = "SELECT 
                MONTH(fecha_nac) as mes,
                COUNT(DISTINCT num_doc) as total
            FROM dbo.INDICADOR_RN
            $condiciones
            GROUP BY MONTH(fecha_nac)
            ORDER BY mes";

$stmt_linea = sqlsrv_query($conn, $sql_linea, $params);
$datos_meses = array_fill(1, 12, 0);

if ($stmt_linea !== false) {
    while ($row = sqlsrv_fetch_array($stmt_linea, SQLSRV_FETCH_ASSOC)) {
        $datos_meses[$row['mes']] = (int)$row['total'];
    }
    sqlsrv_free_stmt($stmt_linea);
}

$lineData = [
    $datos_meses[1],
    $datos_meses[2],
    $datos_meses[3],
    $datos_meses[4],
    $datos_meses[5],
    $datos_meses[6]
];

sqlsrv_close($conn);

echo json_encode([
    'pie' => ['data' => $pieData],
    'line' => ['data' => $lineData]
]);
exit;
?>