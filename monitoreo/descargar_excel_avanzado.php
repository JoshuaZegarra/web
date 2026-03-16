<?php
// descargar_excel_avanzado.php
require 'vendor/autoload.php';
session_start();
require_once 'conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener microred del usuario
$microred_usuario = isset($_SESSION['microred']) ? $_SESSION['microred'] : 'TODAS';

// Obtener filtros (mismo código que antes)
// ... (código de filtros igual que en descargar_excel.php)

// Crear nuevo documento
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Título
$sheet->setCellValue('A1', 'LISTA DE NIÑOS EVALUADOS - ' . date('d/m/Y H:i:s'));
$sheet->mergeCells('A1:M1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Cabeceras
$headers = ['Nº', 'Documento', 'Paciente', 'Establecimiento', 'Fecha Nac.', 'Edad', 
            'Vacunas RN', 'CRED RN', 'Tamizaje', 'Cred Mensual', 'Vacuna 1 Dosis', 
            'Vacuna 2 Dosis', 'Vacuna 3 Dosis'];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '3', $header);
    $col++;
}

// Estilo de cabeceras
$sheet->getStyle('A3:M3')->getFont()->setBold(true);
$sheet->getStyle('A3:M3')->getFill()
      ->setFillType(Fill::FILL_SOLID)
      ->getStartColor()->setARGB('FF0F3C4C');
$sheet->getStyle('A3:M3')->getFont()->getColor()->setARGB('FFFFFFFF');
$sheet->getStyle('A3:M3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Consulta (mismo código que antes)
// ... (código de consulta igual que en descargar_excel.php)

// Escribir datos
$row_num = 4;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $sheet->setCellValue('A' . $row_num, $row['Nº']);
    $sheet->setCellValue('B' . $row_num, $row['Documento']);
    $sheet->setCellValue('C' . $row_num, $row['Paciente']);
    $sheet->setCellValue('D' . $row_num, $row['Establecimiento']);
    $sheet->setCellValue('E' . $row_num, $row['Fecha Nac.']);
    $sheet->setCellValue('F' . $row_num, $row['Edad']);
    $sheet->setCellValue('G' . $row_num, $row['Vacunas RN']);
    $sheet->setCellValue('H' . $row_num, $row['CRED RN']);
    $sheet->setCellValue('I' . $row_num, $row['Tamizaje']);
    $sheet->setCellValue('J' . $row_num, $row['Cred Mensual']);
    $sheet->setCellValue('K' . $row_num, $row['Vacuna 1 Dosis']);
    $sheet->setCellValue('L' . $row_num, $row['Vacuna 2 Dosis']);
    $sheet->setCellValue('M' . $row_num, $row['Vacuna 3 Dosis']);
    $row_num++;
}

// Autoajustar columnas
foreach (range('A', 'M') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Bordes
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
$sheet->getStyle('A3:M' . ($row_num-1))->applyFromArray($styleArray);

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="lista_ninos_' . date('Y-m-d_His') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
exit;
?>