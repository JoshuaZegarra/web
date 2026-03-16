<?php
// obtener_lista_niños.php
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
$documento = isset($_GET['documento']) ? trim($_GET['documento']) : '';

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

// FILTRO POR MICRORED
if ($microred_usuario !== 'TODAS' && !empty($microred_usuario)) {
    agregarCondicion($condiciones, $where_agregado, "MicroRed = ?", $params, $microred_usuario);
}

// Aplicar filtros de fecha
if (!empty($año) && $año !== 'todos' && $año !== '') {
    agregarCondicion($condiciones, $where_agregado, "YEAR(fecha_nac) = ?", $params, $año);
}

if (!empty($mes) && $mes !== 'todos' && $mes !== '') {
    agregarCondicion($condiciones, $where_agregado, "MONTH(fecha_nac) = ?", $params, $mes);
}

// Filtrar por establecimiento
if (!empty($establecimiento) && $establecimiento !== 'todos' && $establecimiento !== '') {
    agregarCondicion($condiciones, $where_agregado, "Id_Establecimiento = ?", $params, $establecimiento);
}

// Filtrar por documento
if (!empty($documento)) {
    $doc_param = "%" . $documento . "%";
    if ($where_agregado) {
        $condiciones .= " AND num_doc LIKE ?";
    } else {
        $condiciones = "WHERE num_doc LIKE ?";
        $where_agregado = true;
    }
    $params[] = $doc_param;
}

// Consulta SQL - SIN GROUP BY, cada num_doc es independiente
$sql = "SELECT 
            num_doc,
            Tipo_doc,
            PACIENTE,
            Nombre_Establecimiento,
            CONVERT(varchar, fecha_nac, 103) as fecha_nac_str,
            fecha_nac,
            -- Indicadores - Cada registro tiene sus propios valores
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                -- Para DNI, solo mostrar indicadores a partir de los 9 meses
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(numerador_vacuna_BCG, 0)
                    ELSE 0
                END
            ELSE ISNULL(numerador_vacuna_BCG, 0) END as bcg,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(numerador_vacuna_HVB, 0)
                    ELSE 0
                END
            ELSE ISNULL(numerador_vacuna_HVB, 0) END as hvb,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(numerador_CRED_Rn1, 0)
                    ELSE 0
                END
            ELSE ISNULL(numerador_CRED_Rn1, 0) END as rn1,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(numerador_CRED_Rn2, 0)
                    ELSE 0
                END
            ELSE ISNULL(numerador_CRED_Rn2, 0) END as rn2,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(numerador_CRED_Rn3, 0)
                    ELSE 0
                END
            ELSE ISNULL(numerador_CRED_Rn3, 0) END as rn3,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(numerador_Tamizaje_neo, 0)
                    ELSE 0
                END
            ELSE ISNULL(numerador_Tamizaje_neo, 0) END as tamizaje,
            
            -- CRED Mensual - También condicional
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_cred1, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_cred1, 0) END as cred1,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_cred2, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_cred2, 0) END as cred2,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_cred3, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_cred3, 0) END as cred3,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_cred4, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_cred4, 0) END as cred4,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_cred5, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_cred5, 0) END as cred5,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_cred6, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_cred6, 0) END as cred6,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_cred7, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_cred7, 0) END as cred7,
            
            -- Vacunas 1 Dosis
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_neumococo1, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_neumococo1, 0) END as neumococo1,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_rotavirus1, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_rotavirus1, 0) END as rotavirus1,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_polio1, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_polio1, 0) END as polio1,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_pentavalente1, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_pentavalente1, 0) END as pentavalente1,
            
            -- Vacunas 2 Dosis
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_neumococo2, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_neumococo2, 0) END as neumococo2,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_rotavirus2, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_rotavirus2, 0) END as rotavirus2,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_polio2, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_polio2, 0) END as polio2,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_pentavalente2, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_pentavalente2, 0) END as pentavalente2,
            
            -- Vacunas 3 Dosis
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_polio3, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_polio3, 0) END as polio3,
            
            CASE WHEN Tipo_doc LIKE '%DNI%' THEN 
                CASE 
                    WHEN DATEDIFF(month, fecha_nac, GETDATE()) >= 9 THEN ISNULL(num_pentavalente3, 0)
                    ELSE 0
                END
            ELSE ISNULL(num_pentavalente3, 0) END as pentavalente3
            
        FROM dbo.INDICADOR_RN
        $condiciones
        ORDER BY num_doc";

// Registrar la consulta en el log
error_log("SQL Búsqueda: " . $sql);
error_log("Parámetros: " . print_r($params, true));

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    error_log("Error SQL: " . print_r($errors, true));
    echo json_encode([]);
    sqlsrv_close($conn);
    exit;
}

$niños = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    
    // Calcular edad usando DateTime
    $fecha_nac = $row['fecha_nac'];
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac);
    
    $años = $edad->y;
    $meses = $edad->m;
    $dias = $edad->d;
    
    // Formatear edad
    $edad_texto = '';
    if ($años > 0) {
        $edad_texto .= $años . ' año' . ($años != 1 ? 's' : '');
    }
    if ($meses > 0) {
        $edad_texto .= ($edad_texto ? ', ' : '') . $meses . ' mes' . ($meses != 1 ? 'es' : '');
    }
    if ($años == 0 && $meses == 0 && $dias > 0) {
        $edad_texto = $dias . ' día' . ($dias != 1 ? 's' : '');
    }
    
    if (empty($edad_texto)) {
        $edad_texto = 'Recién nacido';
    }
    
    $niños[] = [
        'documento' => $row['num_doc'],
        'paciente' => $row['PACIENTE'] ?? 'S/N',
        'establecimiento' => $row['Nombre_Establecimiento'] ?? 'N/A',
        'fecha_nac' => $row['fecha_nac_str'] ?? 'N/A',
        'edad' => $edad_texto,
        'tipo_documento' => $row['Tipo_doc'] ?? '',
        'bcg' => (int)$row['bcg'],
        'hvb' => (int)$row['hvb'],
        'rn1' => (int)$row['rn1'],
        'rn2' => (int)$row['rn2'],
        'rn3' => (int)$row['rn3'],
        'tamizaje' => (int)$row['tamizaje'],
        'cred1' => (int)$row['cred1'],
        'cred2' => (int)$row['cred2'],
        'cred3' => (int)$row['cred3'],
        'cred4' => (int)$row['cred4'],
        'cred5' => (int)$row['cred5'],
        'cred6' => (int)$row['cred6'],
        'cred7' => (int)$row['cred7'],
        'neumococo1' => (int)$row['neumococo1'],
        'rotavirus1' => (int)$row['rotavirus1'],
        'polio1' => (int)$row['polio1'],
        'pentavalente1' => (int)$row['pentavalente1'],
        'neumococo2' => (int)$row['neumococo2'],
        'rotavirus2' => (int)$row['rotavirus2'],
        'polio2' => (int)$row['polio2'],
        'pentavalente2' => (int)$row['pentavalente2'],
        'polio3' => (int)$row['polio3'],
        'pentavalente3' => (int)$row['pentavalente3']
    ];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode($niños);
?>