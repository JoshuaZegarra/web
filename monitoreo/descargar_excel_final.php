<?php
// descargar_excel_final.php
session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener microred del usuario logueado
$microred_usuario = isset($_SESSION['microred']) ? $_SESSION['microred'] : 'TODAS';

// Obtener filtros
$establecimiento = isset($_GET['establecimiento']) ? $_GET['establecimiento'] : '';
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$año = isset($_GET['año']) ? $_GET['año'] : '';
$documento = isset($_GET['documento']) ? $_GET['documento'] : '';

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

if (!empty($documento)) {
    agregarCondicion($condiciones, $where_agregado, "num_doc LIKE ?", $params, "%$documento%");
}

// Consulta para obtener los datos SIN duplicados (usando DISTINCT y subconsulta)
$sql = "WITH DatosUnicos AS (
            SELECT DISTINCT 
                num_doc,
                PACIENTE,
                Nombre_Establecimiento,
                fecha_nac,
                numerador_vacuna_BCG,
                numerador_vacuna_HVB,
                numerador_CRED_Rn1,
                numerador_CRED_Rn2,
                numerador_CRED_Rn3,
                numerador_Tamizaje_neo,
                num_cred1,
                num_cred2,
                num_cred3,
                num_cred4,
                num_cred5,
                num_cred6,
                num_cred7,
                num_neumococo1,
                num_rotavirus1,
                num_polio1,
                num_pentavalente1,
                num_neumococo2,
                num_rotavirus2,
                num_polio2,
                num_pentavalente2,
                num_polio3,
                num_pentavalente3
            FROM dbo.INDICADOR_RN
            $condiciones
        )
        SELECT 
            ROW_NUMBER() OVER (ORDER BY num_doc) as Nº,
            ISNULL(num_doc, '') as Documento,
            ISNULL(PACIENTE, 'S/N') as Paciente,
            ISNULL(Nombre_Establecimiento, 'N/A') as Establecimiento,
            CONVERT(varchar, fecha_nac, 103) as [Fecha Nac.],
            CASE 
                WHEN DATEDIFF(year, fecha_nac, GETDATE()) > 0 
                THEN CAST(DATEDIFF(year, fecha_nac, GETDATE()) AS VARCHAR) + ' año(s) ' 
                ELSE '' 
            END + 
            CASE 
                WHEN DATEDIFF(month, fecha_nac, GETDATE()) % 12 > 0 
                THEN CAST(DATEDIFF(month, fecha_nac, GETDATE()) % 12 AS VARCHAR) + ' mes(es)'
                ELSE ''
            END as Edad,
            
            -- Vacunas RN (BCG y HVB)
            CASE 
                WHEN numerador_vacuna_BCG = 1 AND numerador_vacuna_HVB = 1 THEN 'cumple'
                ELSE 'no cumple'
            END as [Vacunas RN],
            
            -- CRED RN (3 controles)
            CASE 
                WHEN numerador_CRED_Rn1 = 1 AND numerador_CRED_Rn2 = 1 AND numerador_CRED_Rn3 = 1 THEN 'cumple'
                ELSE 'no cumple'
            END as [CRED RN],
            
            -- Tamizaje Neonatal
            CASE 
                WHEN numerador_Tamizaje_neo = 1 THEN 'cumple'
                ELSE 'no cumple'
            END as Tamizaje,
            
            -- CRED Mensual (7 controles)
            CASE 
                WHEN num_cred1 = 1 AND num_cred2 = 1 AND num_cred3 = 1 AND num_cred4 = 1 
                     AND num_cred5 = 1 AND num_cred6 = 1 AND num_cred7 = 1 THEN 'cumple'
                ELSE 'no cumple'
            END as [Cred Mensual],
            
            -- Vacuna 1 Dosis
            CASE 
                WHEN num_neumococo1 = 1 AND num_rotavirus1 = 1 AND num_polio1 = 1 AND num_pentavalente1 = 1 THEN 'cumple'
                ELSE 'no cumple'
            END as [Vacuna 1 Dosis],
            
            -- Vacuna 2 Dosis
            CASE 
                WHEN num_neumococo2 = 1 AND num_rotavirus2 = 1 AND num_polio2 = 1 AND num_pentavalente2 = 1 THEN 'cumple'
                ELSE 'no cumple'
            END as [Vacuna 2 Dosis],
            
            -- Vacuna 3 Dosis
            CASE 
                WHEN num_polio3 = 1 AND num_pentavalente3 = 1 THEN 'cumple'
                ELSE 'no cumple'
            END as [Vacuna 3 Dosis]
            
        FROM DatosUnicos
        ORDER BY num_doc";

// Depuración
error_log("========== DESCARGA EXCEL FINAL ==========");
error_log("SQL: " . $sql);
error_log("Params: " . print_r($params, true));

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    error_log("Error en consulta: " . print_r($errors, true));
    
    // Mostrar error en pantalla (solo para depuración)
    die("Error en la consulta: " . print_r($errors, true));
}

// Configurar headers para descarga de Excel (formato CSV)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="lista_ninos_' . date('Y-m-d_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Abrir salida
$output = fopen('php://output', 'w');

// Escribir BOM para UTF-8 (para caracteres especiales)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Escribir cabeceras de Excel (separadas por punto y coma)
$cabeceras = [
    'Nº',
    'Documento',
    'Paciente',
    'Establecimiento',
    'Fecha Nacimiento',
    'Edad',
    'Vacunas RN',
    'CRED RN',
    'Tamizaje',
    'Cred Mensual',
    'Vacuna 1 Dosis',
    'Vacuna 2 Dosis',
    'Vacuna 3 Dosis'
];
fputcsv($output, $cabeceras, ';');

// Contar registros para depuración
$contador = 0;
$documentos_procesados = [];

// Escribir datos
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Verificar duplicados por documento (seguridad extra)
    if (in_array($row['Documento'], $documentos_procesados)) {
        continue; // Saltar duplicado
    }
    $documentos_procesados[] = $row['Documento'];
    
    $fila = [
        $row['Nº'],
        $row['Documento'],
        $row['Paciente'],
        $row['Establecimiento'],
        $row['Fecha Nac.'],
        $row['Edad'],
        $row['Vacunas RN'],
        $row['CRED RN'],
        $row['Tamizaje'],
        $row['Cred Mensual'],
        $row['Vacuna 1 Dosis'],
        $row['Vacuna 2 Dosis'],
        $row['Vacuna 3 Dosis']
    ];
    
    fputcsv($output, $fila, ';');
    $contador++;
}

error_log("Registros exportados (sin duplicados): " . $contador);

fclose($output);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
exit;
?>