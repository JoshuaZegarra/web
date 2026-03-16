<?php
// obtener_lista_niños_simple.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

// Verificar conexión
if (!isset($conn) || $conn === false) {
    echo json_encode(['error' => 'Error de conexión']);
    exit;
}

// Consulta SIMPLE sin filtros
$sql = "SELECT TOP 10 
            num_doc,
            PACIENTE,
            Nombre_Establecimiento,
            CONVERT(varchar, fecha_nac, 103) as fecha_nac
        FROM INDICADOR_RN";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(['error' => 'Error en consulta', 'details' => sqlsrv_errors()]);
    sqlsrv_close($conn);
    exit;
}

$niños = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $niños[] = [
        'documento' => $row['num_doc'] ?? 'S/N',
        'paciente' => $row['PACIENTE'] ?? 'S/N',
        'establecimiento' => $row['Nombre_Establecimiento'] ?? 'N/A',
        'fecha_nac' => $row['fecha_nac'] ?? 'N/A',
        'edad' => 'Calculando...',
        'bcg' => 0,
        'hvb' => 0,
        'rn1' => 0,
        'rn2' => 0,
        'rn3' => 0,
        'tamizaje' => 0,
        'cred1' => 0,
        'cred2' => 0,
        'cred3' => 0,
        'cred4' => 0,
        'cred5' => 0,
        'cred6' => 0,
        'cred7' => 0,
        'neumococo1' => 0,
        'rotavirus1' => 0,
        'polio1' => 0,
        'pentavalente1' => 0,
        'neumococo2' => 0,
        'rotavirus2' => 0,
        'polio2' => 0,
        'pentavalente2' => 0,
        'polio3' => 0,
        'pentavalente3' => 0
    ];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode($niños);
?>