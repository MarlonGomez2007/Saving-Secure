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

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    die("Acceso denegado.");
}

$id_usuario = $_SESSION['id_usuario'];
$email_usuario = $_SESSION['email'] ?? null;
if (empty($email_usuario) || !filter_var($email_usuario, FILTER_VALIDATE_EMAIL)) {
    die("Error: dirección de correo no válida.");
}
$nombre_usuario = $_SESSION['nombre'];

// Obtener los datos de gastos
$query = "SELECT * FROM gastos WHERE id_usuario = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$gastos = [];
while ($row = $result->fetch_assoc()) {
    $gastos[] = $row;
}
$stmt->close();

// Obtener datos para los gráficos
$query_categorias = "SELECT categoria, SUM(monto) as total FROM gastos WHERE id_usuario = ? GROUP BY categoria ORDER BY total DESC";
$stmt = $conn->prepare($query_categorias);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result_categorias = $stmt->get_result();

$categorias = [];
$totales_por_categoria = [];
while ($row = $result_categorias->fetch_assoc()) {
    $categorias[] = $row['categoria'];
    $totales_por_categoria[] = $row['total'];
}
$stmt->close();

// Crear carpeta si no existe
if (!file_exists('informes')) {
    mkdir('informes', 0777, true);
}

// ** Generar Excel con estilos y gráficos **
$spreadsheet = new Spreadsheet();

// Establecer propiedades del documento
$spreadsheet->getProperties()
    ->setCreator("Saving Secure")
    ->setLastModifiedBy($nombre_usuario)
    ->setTitle("Informe de Gastos - Saving Secure")
    ->setSubject("Informe de Gastos")
    ->setDescription("Informe detallado de gastos generado por Saving Secure");

// Configurar la hoja principal de gastos
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Gastos");

// Añadir logo y título
$sheet->mergeCells('A1:E1');
$sheet->setCellValue('A1', 'SAVING SECURE - INFORME DE GASTOS');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
$sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FECD02');
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Información del usuario
$sheet->mergeCells('A2:E2');
$sheet->setCellValue('A2', "Usuario: $nombre_usuario | Fecha: " . date('d-m-Y'));
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Encabezados de la tabla
$sheet->setCellValue('A4', 'Nombre del Gasto');
$sheet->setCellValue('B4', 'Descripción');
$sheet->setCellValue('C4', 'Fecha');
$sheet->setCellValue('D4', 'Monto');
$sheet->setCellValue('E4', 'Categoría');

// Estilo de los encabezados
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '000000'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];
$sheet->getStyle('A4:E4')->applyFromArray($headerStyle);

// Datos y estilos alternados para las filas
$fila = 5;
$totalGastos = 0;

foreach ($gastos as $index => $gasto) {
    $sheet->setCellValue("A$fila", $gasto['nombre']);
    $sheet->setCellValue("B$fila", $gasto['descripcion']);
    $sheet->setCellValue("C$fila", date("d-m-Y", strtotime($gasto['fecha'])));
    $sheet->setCellValue("D$fila", $gasto['monto']);
    $sheet->setCellValue("E$fila", $gasto['categoria']);
    
    // Formato condicional para filas alternadas
    $rowColor = ($index % 2 == 0) ? 'F0F0F0' : 'FFFFFF';
    $sheet->getStyle("A$fila:E$fila")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rowColor);
    
    // Bordes para todas las celdas
    $sheet->getStyle("A$fila:E$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
    // Alineación
    $sheet->getStyle("A$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("B$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("C$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("D$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("E$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Formato para montos
    $sheet->getStyle("D$fila")->getNumberFormat()->setFormatCode('#,##0 "COP"');
    
    $totalGastos += $gasto['monto'];
    $fila++;
}

// Fila de totales
$sheet->mergeCells("A$fila:C$fila");
$sheet->setCellValue("A$fila", "TOTAL");
$sheet->setCellValue("D$fila", $totalGastos);
$sheet->mergeCells("E$fila:E$fila");

$sheet->getStyle("A$fila:E$fila")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FECD02');
$sheet->getStyle("A$fila:E$fila")->getFont()->setBold(true);
$sheet->getStyle("A$fila:E$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle("D$fila")->getNumberFormat()->setFormatCode('#,##0 "COP"');

// Ajustar anchos de columnas
$sheet->getColumnDimension('A')->setWidth(25);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(15);

// Crear nueva hoja para gráficos
$spreadsheet->createSheet();
$chartSheet = $spreadsheet->getSheet(1);
$chartSheet->setTitle("Gráficos");

// Preparar datos para gráficos
$chartSheet->setCellValue('A1', 'Categoría');
$chartSheet->setCellValue('B1', 'Total');

for ($i = 0; $i < count($categorias); $i++) {
    $chartSheet->setCellValue('A' . ($i + 2), $categorias[$i]);
    $chartSheet->setCellValue('B' . ($i + 2), $totales_por_categoria[$i]);
    $chartSheet->getStyle('B' . ($i + 2))->getNumberFormat()->setFormatCode('#,##0 "COP"');
}

// Crear gráfico circular
$dataSeriesLabels = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Gráficos!$A$1', null, 1),
];

$dataSeriesValues = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Gráficos!$B$2:$B$' . (count($categorias) + 1), null, count($categorias)),
];

$dataSeriesCategories = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Gráficos!$A$2:$A$' . (count($categorias) + 1), null, count($categorias)),
];

// Crear la serie de datos
$series = new DataSeries(
    DataSeries::TYPE_PIECHART,
    null,
    range(0, count($dataSeriesValues) - 1),
    $dataSeriesLabels,
    $dataSeriesCategories,
    $dataSeriesValues
);

// Crear el área de gráfico
$plotArea = new PlotArea(null, [$series]);

// Crear el título del gráfico
$title = new Title('Distribución de Gastos por Categoría');

// Crear la leyenda
$legend = new Legend(Legend::POSITION_RIGHT, null, false);

// Crear el gráfico
$chart = new Chart(
    'chart1',
    $title,
    $legend,
    $plotArea
);

// Establecer la posición donde se dibujará el gráfico (desde celda B3 a H15)
$chart->setTopLeftPosition('B3');
$chart->setBottomRightPosition('H15');

// Añadir el gráfico a la hoja
$chartSheet->addChart($chart);

// Crear gráfico de barras
$dataSeriesLabels2 = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Gráficos!$A$1', null, 1),
];

$dataSeriesValues2 = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Gráficos!$B$2:$B$' . (count($categorias) + 1), null, count($categorias)),
];

$dataSeriesCategories2 = [
    new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Gráficos!$A$2:$A$' . (count($categorias) + 1), null, count($categorias)),
];

// Crear la serie de datos para el gráfico de barras
$series2 = new DataSeries(
    DataSeries::TYPE_BARCHART,
    DataSeries::GROUPING_STANDARD,
    range(0, count($dataSeriesValues2) - 1),
    $dataSeriesLabels2,
    $dataSeriesCategories2,
    $dataSeriesValues2
);

// Crear el área de gráfico para el gráfico de barras
$plotArea2 = new PlotArea(null, [$series2]);

// Crear el título del gráfico de barras
$title2 = new Title('Gastos por Categoría');

// Crear la leyenda para el gráfico de barras
$legend2 = new Legend(Legend::POSITION_RIGHT, null, false);

// Crear el gráfico de barras
$chart2 = new Chart(
    'chart2',
    $title2,
    $legend2,
    $plotArea2
);

// Establecer la posición donde se dibujará el gráfico de barras
$chart2->setTopLeftPosition('B18');
$chart2->setBottomRightPosition('H30');

// Añadir el gráfico de barras a la hoja
$chartSheet->addChart($chart2);

$excelFile = "informes/informe_$id_usuario.xlsx";
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

// Estilos CSS para el PDF
$stylesheet = '
    body {
        font-family: Arial, sans-serif;
        color: #333;
    }
    .header {
        background-color: #FECD02;
        color: #000;
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    .logo {
        text-align: center;
        margin-bottom: 10px;
    }
    .user-info {
        text-align: right;
        margin-bottom: 20px;
        font-size: 12px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th {
        background-color: #000;
        color: #fff;
        padding: 8px;
        text-align: center;
        font-weight: bold;
    }
    td {
        padding: 8px;
        border: 1px solid #ddd;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .total-row {
        background-color: #FECD02;
        font-weight: bold;
    }
    .chart-container {
        text-align: center;
        margin: 20px 0;
    }
    .footer {
        text-align: center;
        font-size: 10px;
        padding-top: 30px;
        color: #777;
    }
    .category-block {
        margin-bottom: 20px;
    }
    .category-title {
        background-color: #f0f0f0;
        padding: 5px;
        font-weight: bold;
        border-left: 5px solid #FECD02;
    }
    .amount {
        text-align: right;
    }
    .date {
        text-align: center;
    }
    .category {
        text-align: center;
    }
';

$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

// Logo y cabecera
$html = '
<div class="header">
    <h1>SAVING SECURE</h1>
    <h2>Informe Detallado de Gastos</h2>
</div>

<div class="user-info">
    <p><strong>Usuario:</strong> ' . $nombre_usuario . '</p>
    <p><strong>Fecha de generación:</strong> ' . date('d-m-Y') . '</p>
</div>

<h3>Resumen de Gastos</h3>
';

// Tabla de gastos
$html .= '
<table>
    <thead>
        <tr>
            <th>Nombre del Gasto</th>
            <th>Descripción</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Categoría</th>
        </tr>
    </thead>
    <tbody>';

$totalGastos = 0;
foreach ($gastos as $gasto) {
    $html .= '<tr>
        <td>' . $gasto['nombre'] . '</td>
        <td>' . $gasto['descripcion'] . '</td>
        <td class="date">' . date("d-m-Y", strtotime($gasto['fecha'])) . '</td>
        <td class="amount">' . number_format($gasto['monto'], 0, ',', '.') . ' COP</td>
        <td class="category">' . $gasto['categoria'] . '</td>
    </tr>';
    $totalGastos += $gasto['monto'];
}

$html .= '
        <tr class="total-row">
            <td colspan="3"><strong>TOTAL</strong></td>
            <td class="amount"><strong>' . number_format($totalGastos, 0, ',', '.') . ' COP</strong></td>
            <td></td>
        </tr>
    </tbody>
</table>';

// Resumen por categoría
$html .= '<h3>Resumen por Categoría</h3>
<table>
    <thead>
        <tr>
            <th>Categoría</th>
            <th>Total</th>
            <th>Porcentaje</th>
        </tr>
    </thead>
    <tbody>';

foreach ($categorias as $i => $categoria) {
    $porcentaje = ($totales_por_categoria[$i] / $totalGastos) * 100;
    $html .= '<tr>
        <td>' . $categoria . '</td>
        <td class="amount">' . number_format($totales_por_categoria[$i], 0, ',', '.') . ' COP</td>
        <td class="amount">' . number_format($porcentaje, 2) . '%</td>
    </tr>';
}

$html .= '
    </tbody>
</table>';

// Pie de página
$html .= '
<div class="footer">
    <p>Este informe fue generado automáticamente por Saving Secure. Para más información, visita nuestra aplicación.</p>
    <p>&copy; 2025 Saving Secure. Todos los derechos reservados.</p>
</div>';

$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$pdfFile = "informes/informe_$id_usuario.pdf";
$mpdf->Output($pdfFile, "F");

// ** Enviar Correo con PHPMailer **
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
    $mail->Subject = '=?UTF-8?B?' . base64_encode('Tu Informe de Gastos Personalizado') . '?=';
    $mail->isHTML(true);

    // Mensaje HTML mejorado
    $mail->Body = "
    <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    color: #333;
                    line-height: 1.6;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px; 
                }
                .header { 
                    background-color: #fecd02; 
                    color: black; 
                    padding: 15px; 
                    text-align: center; 
                    font-size: 24px; 
                    font-weight: bold; 
                    border-radius: 5px;
                }
                .content {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .footer { 
                    font-size: 12px; 
                    color: #666; 
                    margin-top: 30px; 
                    text-align: center;
                    border-top: 1px solid #eee;
                    padding-top: 20px;
                }
                .button {
                    display: inline-block;
                    background-color: #fecd02;
                    color: #000;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    margin-top: 15px;
                }
                .highlight {
                    font-weight: bold;
                    color: #000;
                }
                .files {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>SAVING SECURE</div>
                
                <div class='content'>
                    <h2>¡Hola <span class='highlight'>$nombre_usuario</span>!</h2>
                    
                    <p>Adjunto encontrarás tu <span class='highlight'>Informe Personalizado de Gastos</span> 
                    que has solicitado desde nuestra aplicación Saving Secure.</p>
                    
                    <div class='files'>
                        <p><strong>Archivos adjuntos:</strong></p>
                        <ul>
                            <li>Informe detallado en formato Excel (con gráficos interactivos)</li>
                            <li>Informe en formato PDF para fácil visualización</li>
                        </ul>
                    </div>
                    
                    <p>Este informe incluye:</p>
                    <ul>
                        <li>Detalle de todos tus gastos registrados</li>
                        <li>Análisis por categorías</li>
                        <li>Gráficos visuales para mejor comprensión de tus finanzas</li>
                    </ul>
                    
                    <p>Continúa utilizando nuestra aplicación para mantener un mejor control de tus finanzas personales.</p>
                    
                    <center><a href='https://savingsecure.ct.ws/dashboard.php' class='button'>Ir a Mi Dashboard</a></center>
                </div>
                
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responder.</p>
                    <p>&copy; 2025 Saving Secure. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
    </html>";

    // Adjuntar archivos
    if (file_exists($excelFile)) {
        $mail->addAttachment($excelFile, "Informe_Gastos_SavingSecure.xlsx");
    }
    if (file_exists($pdfFile)) {
        $mail->addAttachment($pdfFile, "Informe_Gastos_SavingSecure.pdf");
    }

    if ($mail->send()) {
        echo json_encode("El informe ha sido enviado a tu correo.");
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
}
?>