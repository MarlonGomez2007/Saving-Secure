<?php
session_start();
require 'db.php';
require 'vendor/autoload.php';
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use Mpdf\Mpdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id_usuario'])) {
    die("Acceso denegado.");
}

$id_usuario = $_SESSION['id_usuario'];
$email_usuario = $_SESSION['email'] ?? null;
if (empty($email_usuario) || !filter_var($email_usuario, FILTER_VALIDATE_EMAIL)) {
    die("Error: dirección de correo no válida.");
}
$nombre_usuario = $_SESSION['nombre'];

// Obtener los datos de ingresos
$query = "SELECT * FROM ingresos WHERE id_usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$ingresos = [];
while ($row = $result->fetch_assoc()) {
    $ingresos[] = $row;
}
$stmt->close();

// Resumen por mes
$query_mes = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, SUM(monto) as total FROM ingresos WHERE id_usuario = ? GROUP BY mes ORDER BY mes DESC LIMIT 12";
$stmt = $conn->prepare($query_mes);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_mes = $stmt->get_result();
$meses = [];
$totales_mes = [];
while ($row = $result_mes->fetch_assoc()) {
    $meses[] = $row['mes'];
    $totales_mes[] = $row['total'];
}
$stmt->close();

// Resumen por año
$query_anio = "SELECT YEAR(fecha) as anio, SUM(monto) as total FROM ingresos WHERE id_usuario = ? GROUP BY anio ORDER BY anio DESC";
$stmt = $conn->prepare($query_anio);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_anio = $stmt->get_result();
$anios = [];
$totales_anio = [];
while ($row = $result_anio->fetch_assoc()) {
    $anios[] = $row['anio'];
    $totales_anio[] = $row['total'];
}
$stmt->close();

// Crear carpeta si no existe
if (!file_exists('informes')) {
    mkdir('informes', 0777, true);
}

// ** Generar Excel con estilos y gráficos **
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("Saving Secure")
    ->setLastModifiedBy($nombre_usuario)
    ->setTitle("Informe de Ingresos - Saving Secure")
    ->setSubject("Informe de Ingresos")
    ->setDescription("Informe detallado de ingresos generado por Saving Secure");

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Ingresos");
$sheet->mergeCells('A1:E1');
$sheet->setCellValue('A1', 'SAVING SECURE - INFORME DE INGRESOS');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
$sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FECD02');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells('A2:E2');
$sheet->setCellValue('A2', "Usuario: $nombre_usuario | Fecha: " . date('d-m-Y'));
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->setCellValue('A4', 'Nombre del Ingreso');
$sheet->setCellValue('B4', 'Descripción');
$sheet->setCellValue('C4', 'Fecha');
$sheet->setCellValue('D4', 'Monto');
$sheet->setCellValue('E4', 'Categoría');
$headerStyle = [
    'font' => [ 'bold' => true, 'color' => ['rgb' => 'FFFFFF'], ],
    'fill' => [ 'fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000'], ],
    'borders' => [ 'allBorders' => [ 'borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000'], ], ],
    'alignment' => [ 'horizontal' => Alignment::HORIZONTAL_CENTER, ],
];
$sheet->getStyle('A4:E4')->applyFromArray($headerStyle);
$fila = 5;
$totalIngresos = 0;
foreach ($ingresos as $index => $ingreso) {
    $sheet->setCellValue("A$fila", $ingreso['nombre']);
    $sheet->setCellValue("B$fila", $ingreso['descripcion']);
    $sheet->setCellValue("C$fila", date("d-m-Y", strtotime($ingreso['fecha'])));
    $sheet->setCellValue("D$fila", $ingreso['monto']);
    $sheet->setCellValue("E$fila", $ingreso['id_categoria'] ?? '');
    $rowColor = ($index % 2 == 0) ? 'F0F0F0' : 'FFFFFF';
    $sheet->getStyle("A$fila:E$fila")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rowColor);
    $sheet->getStyle("A$fila:E$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("A$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("B$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("C$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("D$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("E$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("D$fila")->getNumberFormat()->setFormatCode('#,##0 "COP"');
    $totalIngresos += $ingreso['monto'];
    $fila++;
}
$sheet->mergeCells("A$fila:C$fila");
$sheet->setCellValue("A$fila", "TOTAL");
$sheet->setCellValue("D$fila", $totalIngresos);
$sheet->mergeCells("E$fila:E$fila");
$sheet->getStyle("A$fila:E$fila")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FECD02');
$sheet->getStyle("A$fila:E$fila")->getFont()->setBold(true);
$sheet->getStyle("A$fila:E$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle("D$fila")->getNumberFormat()->setFormatCode('#,##0 "COP"');
$sheet->getColumnDimension('A')->setWidth(25);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(15);
// Hoja de gráficos por mes y año
$spreadsheet->createSheet();
$chartSheet = $spreadsheet->getSheet(1);
$chartSheet->setTitle("Gráficos");
$chartSheet->setCellValue('A1', 'Mes');
$chartSheet->setCellValue('B1', 'Total');
for ($i = 0; $i < count($meses); $i++) {
    $chartSheet->setCellValue('A' . ($i + 2), $meses[$i]);
    $chartSheet->setCellValue('B' . ($i + 2), $totales_mes[$i]);
    $chartSheet->getStyle('B' . ($i + 2))->getNumberFormat()->setFormatCode('#,##0 "COP"');
}
$chartSheet->setCellValue('D1', 'Año');
$chartSheet->setCellValue('E1', 'Total');
for ($i = 0; $i < count($anios); $i++) {
    $chartSheet->setCellValue('D' . ($i + 2), $anios[$i]);
    $chartSheet->setCellValue('E' . ($i + 2), $totales_anio[$i]);
    $chartSheet->getStyle('E' . ($i + 2))->getNumberFormat()->setFormatCode('#,##0 "COP"');
}
// Gráfico de barras por mes
$dataSeriesLabels = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Gráficos!$A$1', null, 1),
];
$dataSeriesValues = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Gráficos!$B$2:$B$' . (count($meses) + 1), null, count($meses)),
];
$dataSeriesCategories = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Gráficos!$A$2:$A$' . (count($meses) + 1), null, count($meses)),
];
$series = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($dataSeriesValues) - 1),
    $dataSeriesLabels,
    $dataSeriesCategories,
    $dataSeriesValues
);
$plotArea = new PlotArea(null, [$series]);
$title = new Title('Ingresos por Mes');
$legend = new Legend(Legend::POSITION_RIGHT, null, false);
$chart = new Chart(
    'chart1',
    $title,
    $legend,
    $plotArea
);
$chart->setTopLeftPosition('G2');
$chart->setBottomRightPosition('N16');
$chartSheet->addChart($chart);
$excelFile = "informes/informe_ingresos_$id_usuario.xlsx";
$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true);
$writer->save($excelFile);
// ** Generar PDF mejorado **
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 15,
    'margin_bottom' => 15
]);
$stylesheet = 'body { font-family: Arial, sans-serif; color: #333; } .header { background-color: #FECD02; color: #000; padding: 10px; text-align: center; border-radius: 5px; margin-bottom: 20px; } .logo { text-align: center; margin-bottom: 10px; } .user-info { text-align: right; margin-bottom: 20px; font-size: 12px; } table { width: 100%; border-collapse: collapse; margin-bottom: 20px; } th { background-color: #000; color: #fff; padding: 8px; text-align: center; font-weight: bold; } td { padding: 8px; border: 1px solid #ddd; } tr:nth-child(even) { background-color: #f9f9f9; } .total-row { background-color: #FECD02; font-weight: bold; } .footer { text-align: center; font-size: 10px; padding-top: 30px; color: #777; }';
$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
$html = '<div class="header"><h1>SAVING SECURE</h1><h2>Informe Detallado de Ingresos</h2></div><div class="user-info"><p><strong>Usuario:</strong> ' . $nombre_usuario . '</p><p><strong>Fecha de generación:</strong> ' . date('d-m-Y') . '</p></div><h3>Resumen de Ingresos</h3>';
$html .= '<table><thead><tr><th>Nombre del Ingreso</th><th>Descripción</th><th>Fecha</th><th>Monto</th><th>Categoría</th></tr></thead><tbody>';
$totalIngresos = 0;
foreach ($ingresos as $ingreso) {
    $html .= '<tr><td>' . $ingreso['nombre'] . '</td><td>' . $ingreso['descripcion'] . '</td><td>' . date("d-m-Y", strtotime($ingreso['fecha'])) . '</td><td style="text-align:right;">' . number_format($ingreso['monto'], 0, ',', '.') . ' COP</td><td>' . ($ingreso['id_categoria'] ?? '') . '</td></tr>';
    $totalIngresos += $ingreso['monto'];
}
$html .= '<tr class="total-row"><td colspan="3"><strong>TOTAL</strong></td><td style="text-align:right;"><strong>' . number_format($totalIngresos, 0, ',', '.') . ' COP</strong></td><td></td></tr></tbody></table>';
$html .= '<h3>Resumen por Mes</h3><table><thead><tr><th>Mes</th><th>Total</th></tr></thead><tbody>';
for ($i = 0; $i < count($meses); $i++) {
    $html .= '<tr><td>' . $meses[$i] . '</td><td style="text-align:right;">' . number_format($totales_mes[$i], 0, ',', '.') . ' COP</td></tr>';
}
$html .= '</tbody></table>';
$html .= '<h3>Resumen por Año</h3><table><thead><tr><th>Año</th><th>Total</th></tr></thead><tbody>';
for ($i = 0; $i < count($anios); $i++) {
    $html .= '<tr><td>' . $anios[$i] . '</td><td style="text-align:right;">' . number_format($totales_anio[$i], 0, ',', '.') . ' COP</td></tr>';
}
$html .= '</tbody></table>';
$html .= '<div class="footer"><p>Este informe fue generado automáticamente por Saving Secure. Para más información, visita nuestra aplicación.</p><p>&copy; 2025 Saving Secure. Todos los derechos reservados.</p></div>';
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$pdfFile = "informes/informe_ingresos_$id_usuario.pdf";
$mpdf->Output($pdfFile, "F");
// Envío por correo si se solicita
if (isset($_GET['enviar']) && $_GET['enviar'] == '1') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'savingsecure3@gmail.com'; 
        $mail->Password = 'vvkz zzba zftl sgik'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('no-reply@savingsecure.com', 'Saving Secure');
        $mail->addAddress($email_usuario, $nombre_usuario);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = '=?UTF-8?B?' . base64_encode('Tu Informe de Ingresos Personalizado') . '?=';
        $mail->isHTML(true);
        $mail->Body = "<html><head><style>body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; } .container { max-width: 600px; margin: 0 auto; padding: 20px; } .header { background-color: #fecd02; color: black; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px;} .content { background-color: #fff; padding: 20px; border-radius: 5px; margin-top: 20px;} .footer { font-size: 12px; color: #666; margin-top: 30px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;} .button { display: inline-block; background-color: #fecd02; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 15px;} .highlight { font-weight: bold; color: #000;} .files { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;} </style></head><body><div class='container'><div class='header'>SAVING SECURE</div><div class='content'><h2>¡Hola <span class='highlight'>$nombre_usuario</span>!</h2><p>Adjunto encontrarás tu <span class='highlight'>Informe Personalizado de Ingresos</span> que has solicitado desde nuestra aplicación Saving Secure.</p><div class='files'><p><strong>Archivos adjuntos:</strong></p><ul><li>Informe detallado en formato Excel (con gráficos por mes y año)</li><li>Informe en formato PDF para fácil visualización</li></ul></div><p>Este informe incluye:</p><ul><li>Detalle de todos tus ingresos registrados</li><li>Resúmenes por mes y año</li><li>Gráficos visuales para mejor comprensión de tus finanzas</li></ul><p>Continúa utilizando nuestra aplicación para mantener un mejor control de tus finanzas personales.</p><center><a href='https://savingsecure.ct.ws/dashboard.php' class='button'>Ir a Mi Dashboard</a></center></div><div class='footer'><p>Este es un correo automático, por favor no responder.</p><p>&copy; 2025 Saving Secure. Todos los derechos reservados.</p></div></div></body></html>";
        if (file_exists($excelFile)) {
            $mail->addAttachment($excelFile, "Informe_Ingresos_SavingSecure.xlsx");
        }
        if (file_exists($pdfFile)) {
            $mail->addAttachment($pdfFile, "Informe_Ingresos_SavingSecure.pdf");
        }
        if ($mail->send()) {
            echo json_encode("El informe ha sido enviado a tu correo.");
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
    }
    exit;
}
// Descarga directa
if (isset($_GET['formato']) && $_GET['formato'] === 'excel') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="informe_ingresos.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->setIncludeCharts(true);
    $writer->save('php://output');
    exit;
}
if (isset($_GET['formato']) && $_GET['formato'] === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="informe_ingresos.pdf"');
    readfile($pdfFile);
    exit;
}
// Por defecto, mostrar mensaje
http_response_code(400);
echo 'Solicitud inválida.';
?> 