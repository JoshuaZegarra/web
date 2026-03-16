<?php
// buscar_atenciones.php
header('Content-Type: application/json');
require_once 'conexion.php';

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener parámetros
$dni = isset($_GET['dni']) ? trim($_GET['dni']) : '';
$fecha_inicial = isset($_GET['fecha_inicial']) ? $_GET['fecha_inicial'] : '';
$fecha_final = isset($_GET['fecha_final']) ? $_GET['fecha_final'] : '';
$cie10 = isset($_GET['cie10']) ? trim($_GET['cie10']) : '';

if (empty($dni)) {
    echo json_encode(['error' => 'DNI no proporcionado']);
    exit;
}

// Construir condiciones - USANDO MPC (MAESTRO_PACIENTE_CONCAT)
$condiciones = "WHERE (MPC.Numero_Documento = ? OR MPC.Numero_Documento LIKE ?)";
$params = array($dni, "%$dni%");

// Filtro de fechas
if (!empty($fecha_inicial) && !empty($fecha_final)) {
    $condiciones .= " AND CONVERT(date, NTN.Fecha_Atencion) BETWEEN ? AND ?";
    $params[] = $fecha_inicial;
    $params[] = $fecha_final;
}

// Filtro por código
if (!empty($cie10)) {
    $condiciones .= " AND NTN.Codigo_Item LIKE ?";
    $params[] = "%$cie10%";
}

// Consulta principal - Usando MAESTRO_PACIENTE_CONCAT
$sql = "SELECT 
            NTN.Id_Cita as id_cita,
            
            -- DATOS DEL PACIENTE (desde MAESTRO_PACIENTE_CONCAT)
            MPC.Numero_Documento as documento_paciente,
            
            -- Nombre completo concatenado desde MAESTRO_PACIENTE_CONCAT
            LTRIM(RTRIM(
                ISNULL(MPC.Apellido_Paterno_Paciente, '') + ' ' + 
                ISNULL(MPC.Apellido_Materno_Paciente, '') + ' ' + 
                ISNULL(MPC.Nombres_Paciente, '')
            )) as nombre_completo,
            
            -- Fecha de nacimiento
            CONVERT(varchar, MPC.Fecha_Nacimiento, 103) as fecha_nacimiento,
            
            -- Tipo de Documento
            CASE 
                WHEN TRY_CAST(MPC.Id_Tipo_Documento AS INT) = 1 THEN 'DNI'
                WHEN TRY_CAST(MPC.Id_Tipo_Documento AS INT) = 2 THEN 'CUI'
                WHEN TRY_CAST(MPC.Id_Tipo_Documento AS INT) = 3 THEN 'CNV'
                WHEN TRY_CAST(MPC.Id_Tipo_Documento AS INT) = 4 THEN 'COD. PAD'
                ELSE 'OTROS'
            END as tipo_doc,
            
            -- Cond EESS - Serv
            CONCAT(ISNULL(CAST(NTN.Id_Condicion_Establecimiento AS VARCHAR), ''), ' - ', 
                   ISNULL(CAST(NTN.Id_Condicion_Servicio AS VARCHAR), '')) as cond_eess,
            
            -- Fecha de atención
            CONVERT(varchar, NTN.Fecha_Atencion, 103) as fecha_atencion,
            
            -- Lote, Página, Registro
            NTN.Lote as lote,
            NTN.Num_Pag as pagina,
            NTN.Num_Reg as registro,
            
            -- Código
            NTN.Codigo_Item as codigo,
            
            -- Tipo Diagnóstico
            NTN.Tipo_Diagnostico as tipo_dx,
            
            -- Lab
            NTN.Valor_Lab as lab,
            
            -- Edad y tipo de edad (de la atención)
            NTN.Edad_Reg as edad_atencion,
            CASE 
                WHEN TRY_CAST(NTN.Tipo_Edad AS INT) = 1 THEN 'Días'
                WHEN TRY_CAST(NTN.Tipo_Edad AS INT) = 2 THEN 'Meses'
                WHEN TRY_CAST(NTN.Tipo_Edad AS INT) = 3 THEN 'Años'
                ELSE ''
            END as tipo_edad_atencion,
            
            -- Peso y Talla
            NTN.Peso as peso,
            NTN.Talla as talla,
            
            -- Hemoglobina
            NTN.Hemoglobina as hb,
            
            -- Lugar de atención
            LTRIM(RTRIM(
                ISNULL(MHE.Disa, '') + '/' + 
                ISNULL(MHE.Red, '') + '/' + 
                ISNULL(MHE.MicroRed, '') + '/' + 
                ISNULL(MHE.Nombre_Establecimiento, '')
            )) as lugar_atencion,
            
            -- Personal
            MPERS.Nombres_Personal as personal,
            
            -- Fecha de registro o modificación
            CASE 
                WHEN NTN.Fecha_Modificacion > ISNULL(NTN.Fecha_Registro, '1900-01-01') 
                THEN CONVERT(varchar, NTN.Fecha_Modificacion, 103) + ' ' + 
                     CASE 
                         WHEN DATEPART(hour, NTN.Fecha_Modificacion) >= 12 
                         THEN CONVERT(varchar, CASE WHEN DATEPART(hour, NTN.Fecha_Modificacion) > 12 
                                              THEN DATEPART(hour, NTN.Fecha_Modificacion)-12 
                                              ELSE 12 END) + ':' + 
                              RIGHT('0' + CONVERT(varchar, DATEPART(minute, NTN.Fecha_Modificacion)), 2) + ' PM'
                         ELSE CONVERT(varchar, DATEPART(hour, NTN.Fecha_Modificacion)) + ':' + 
                              RIGHT('0' + CONVERT(varchar, DATEPART(minute, NTN.Fecha_Modificacion)), 2) + ' AM'
                     END
                ELSE CONVERT(varchar, NTN.Fecha_Registro, 103) + ' ' + 
                     CASE 
                         WHEN DATEPART(hour, NTN.Fecha_Registro) >= 12 
                         THEN CONVERT(varchar, CASE WHEN DATEPART(hour, NTN.Fecha_Registro) > 12 
                                              THEN DATEPART(hour, NTN.Fecha_Registro)-12 
                                              ELSE 12 END) + ':' + 
                              RIGHT('0' + CONVERT(varchar, DATEPART(minute, NTN.Fecha_Registro)), 2) + ' PM'
                         ELSE CONVERT(varchar, DATEPART(hour, NTN.Fecha_Registro)) + ':' + 
                              RIGHT('0' + CONVERT(varchar, DATEPART(minute, NTN.Fecha_Registro)), 2) + ' AM'
                     END
            END as fecha_registro
        FROM proyecto1.dbo.NOMINAL_TRAMA_NUEVO NTN
        INNER JOIN proyecto1.dbo.MAESTRO_PACIENTE_CONCAT MPC ON NTN.Id_Paciente = MPC.Id_Paciente
        LEFT JOIN proyecto1.dbo.MAESTRO_HIS_ESTABLECIMIENTO MHE ON NTN.Id_Establecimiento = MHE.Id_Establecimiento
        LEFT JOIN proyecto1.dbo.MAESTRO_PERSONAL MPERS ON NTN.Id_Personal = MPERS.Id_Personal
        $condiciones
        ORDER BY NTN.Fecha_Atencion DESC, NTN.Id_Cita, NTN.Num_Reg";

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    echo json_encode(['error' => 'Error en la consulta: ' . print_r($errors, true)]);
    sqlsrv_close($conn);
    exit;
}

$atenciones = [];
$paciente_info = [
    'documento' => $dni,
    'nombre' => 'No disponible',
    'fecha_nacimiento' => null
];

$primer_registro = true;

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Si es el primer registro, obtenemos los datos del paciente
    if ($primer_registro) {
        $primer_registro = false;
        
        $paciente_info = [
            'documento' => $row['documento_paciente'] ?? $dni,
            'nombre' => !empty($row['nombre_completo']) ? trim($row['nombre_completo']) : 'No disponible',
            'fecha_nacimiento' => $row['fecha_nacimiento'] ?? null
        ];
    }
    
    $atenciones[] = [
        'id_cita' => $row['id_cita'],
        'tipo_doc' => $row['tipo_doc'] ?? 'DNI',
        'num_doc' => $row['documento_paciente'],
        'cond_eess' => $row['cond_eess'] ?? ' - ',
        'fecha_atencion' => $row['fecha_atencion'] ?? '',
        'lote' => $row['lote'] ?? '',
        'pagina' => $row['pagina'] ?? '',
        'registro' => $row['registro'] ?? '',
        'codigo' => $row['codigo'] ?? '',
        'tipo_dx' => $row['tipo_dx'] ?? '',
        'lab' => $row['lab'] ?? '',
        'edad_atencion' => $row['edad_atencion'] ?? '',
        'tipo_edad_atencion' => $row['tipo_edad_atencion'] ?? '',
        'peso' => $row['peso'] ?? '',
        'talla' => $row['talla'] ?? '',
        'hb' => $row['hb'] ?? '',
        'lugar_atencion' => $row['lugar_atencion'] ?? '',
        'personal' => $row['personal'] ?? '',
        'fecha_registro' => $row['fecha_registro'] ?? ''
    ];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

// Registrar en log para depuración
error_log("Paciente info: " . print_r($paciente_info, true));
error_log("Total atenciones: " . count($atenciones));

echo json_encode([
    'paciente' => $paciente_info,
    'atenciones' => $atenciones
]);
?>