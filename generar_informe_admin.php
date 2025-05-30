<?php
ini_set('display_errors', 0);
error_reporting(0);
session_start();
// Control de acceso solo para admins (rol 2)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\XAxis;
use PhpOffice\PhpSpreadsheet\Chart\YAxis;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use Mpdf\Mpdf;

// --- 1. Obtener datos de la base de datos ---
// LOGS
$logs = [];
$sql_logs = "SELECT l.*, u.nombre FROM logs l LEFT JOIN usuarios u ON l.usuario_id = u.id_usuario ORDER BY l.fecha_hora DESC";
$res_logs = $conn->query($sql_logs);
if ($res_logs) {
    while ($row = $res_logs->fetch_assoc()) {
        $logs[] = $row;
    }
}

// SUGERENCIAS
$sugerencias = [];
$sql_sug = "SELECT * FROM sugerencia ORDER BY Fecha DESC";
$res_sug = $conn->query($sql_sug);
if ($res_sug) {
    while ($row = $res_sug->fetch_assoc()) {
        $sugerencias[] = $row;
    }
}

// RATINGS
$ratings = [];
$sql_rat = "SELECT * FROM ratings ORDER BY created_at DESC";
$res_rat = $conn->query($sql_rat);
if ($res_rat) {
    while ($row = $res_rat->fetch_assoc()) {
        $ratings[] = $row;
    }
}

// --- 2. Descarga directa si se solicita ---
if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    $fecha = date('Ymd_His');
    $nombre_admin = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Administrador';
    // Limpiar el nombre del admin para el archivo
    $nombre_admin_file = preg_replace('/[^a-zA-Z0-9_]/', '', str_replace(' ', '_', iconv('UTF-8', 'ASCII//TRANSLIT', $nombre_admin)));
    $nombre_archivo = 'informe_admin_' . $nombre_admin_file;
    $logo_path = __DIR__ . '/assets/img/logo.png';
    $frase = 'Tu seguridad financiera, nuestra prioridad';

    // KPIs
    $total_logs = count($logs);
    $total_sugerencias = count($sugerencias);
    $total_ratings = count($ratings);
    $promedio_rating = $total_ratings > 0 ? round(array_sum(array_column($ratings, 'rating')) / $total_ratings, 2) : 0;

    // Datos para gráficos
    // Logs por tipo de acción
    $logs_por_tipo = [];
    foreach ($logs as $log) {
        $tipo_accion = $log['tipo_accion'];
        $logs_por_tipo[$tipo_accion] = ($logs_por_tipo[$tipo_accion] ?? 0) + 1;
    }
    // Ratings distribución
    $ratings_dist = [];
    foreach ($ratings as $rat) {
        $val = $rat['rating'];
        $ratings_dist[$val] = ($ratings_dist[$val] ?? 0) + 1;
    }
    // Sugerencias por fecha (últimos 10 días)
    $sug_por_fecha = [];
    foreach ($sugerencias as $sug) {
        $fecha_sug = $sug['Fecha'];
        $sug_por_fecha[$fecha_sug] = ($sug_por_fecha[$fecha_sug] ?? 0) + 1;
    }
    ksort($sug_por_fecha);
    $sug_por_fecha = array_slice($sug_por_fecha, -10, 10, true);

    if ($tipo === 'excel') {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Saving Secure')->setTitle('Informe Detallado Admin');
        // --- Portada ---
        $cover = $spreadsheet->getActiveSheet();
        $cover->setTitle('Portada');
        $cover->mergeCells('A1:F1');
        $cover->setCellValue('A1', 'INFORME ADMINISTRATIVO');
        $cover->getStyle('A1')->getFont()->setBold(true)->setSize(22)->getColor()->setRGB('1f2029');
        $cover->getStyle('A1')->getAlignment()->setHorizontal('center');
        // Logo
        if (file_exists($logo_path)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            $drawing->setPath($logo_path);
            $drawing->setHeight(120);
            $drawing->setCoordinates('A2');
            $drawing->setWorksheet($cover);
        }
        $cover->mergeCells('A6:F6');
        $cover->setCellValue('A6', 'Saving Secure');
        $cover->getStyle('A6')->getFont()->setBold(true)->setSize(18)->getColor()->setRGB('fecd02');
        $cover->getStyle('A6')->getAlignment()->setHorizontal('center');
        $cover->mergeCells('A7:F7');
        $cover->setCellValue('A7', $frase);
        $cover->getStyle('A7')->getFont()->setItalic(true)->setSize(13)->getColor()->setRGB('1f2029');
        $cover->getStyle('A7')->getAlignment()->setHorizontal('center');
        $cover->mergeCells('A9:F9');
        $cover->setCellValue('A9', 'Administrador: ' . $nombre_admin);
        $cover->getStyle('A9')->getFont()->setBold(true)->setSize(12);
        $cover->getStyle('A9')->getAlignment()->setHorizontal('center');
        $cover->mergeCells('A10:F10');
        $cover->setCellValue('A10', 'Fecha de generación: ' . date('d/m/Y H:i'));
        $cover->getStyle('A10')->getAlignment()->setHorizontal('center');
        // KPIs
        $cover->setCellValue('B12', 'Total Logs');
        $cover->setCellValue('C12', 'Total Sugerencias');
        $cover->setCellValue('D12', 'Total Ratings');
        $cover->setCellValue('E12', 'Promedio Rating');
        $cover->getStyle('B12:E12')->getFont()->setBold(true)->getColor()->setRGB('1f2029');
        $cover->setCellValue('B13', $total_logs);
        $cover->setCellValue('C13', $total_sugerencias);
        $cover->setCellValue('D13', $total_ratings);
        $cover->setCellValue('E13', $promedio_rating);
        $cover->getStyle('B13:E13')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('fecd02');
        $cover->getStyle('B13:E13')->getAlignment()->setHorizontal('center');
        // --- Hoja Logs ---
        $sheetLogs = $spreadsheet->createSheet();
        $sheetLogs->setTitle('Logs');
        $sheetLogs->fromArray(['ID','Usuario','Tipo de Acción','Descripción','Fecha y Hora'],NULL,'A1');
        $row = 2;
        foreach ($logs as $log) {
            $sheetLogs->fromArray([
                $log['id_log'], $log['nombre'], $log['tipo_accion'], $log['descripcion'], $log['fecha_hora']
            ],NULL,'A'.$row);
            $row++;
        }
        $sheetLogs->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setRGB('1f2029');
        $sheetLogs->getStyle('A1:E'.$row)->getAlignment()->setWrapText(true);
        // --- Hoja Sugerencias ---
        $sheetSug = $spreadsheet->createSheet();
        $sheetSug->setTitle('Sugerencias');
        $sheetSug->fromArray(['ID','Nombre','Correo','Sugerencia','Fecha'],NULL,'A1');
        $row = 2;
        foreach ($sugerencias as $sug) {
            $sheetSug->fromArray([
                $sug['id_sugerencia'], $sug['Nombre'], $sug['Correo'], $sug['Sugerencia'], $sug['Fecha']
            ],NULL,'A'.$row);
            $row++;
        }
        $sheetSug->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setRGB('1f2029');
        $sheetSug->getStyle('A1:E'.$row)->getAlignment()->setWrapText(true);
        // --- Hoja Ratings ---
        $sheetRat = $spreadsheet->createSheet();
        $sheetRat->setTitle('Ratings');
        $sheetRat->fromArray(['ID','Rating','Fecha'],NULL,'A1');
        $row = 2;
        foreach ($ratings as $rat) {
            $sheetRat->fromArray([
                $rat['id'], $rat['rating'], $rat['created_at']
            ],NULL,'A'.$row);
            $row++;
        }
        $sheetRat->getStyle('A1:C1')->getFont()->setBold(true)->getColor()->setRGB('1f2029');
        $sheetRat->getStyle('A1:C'.$row)->getAlignment()->setWrapText(true);
        // --- Hoja Gráficos ---
        $sheetCharts = $spreadsheet->createSheet();
        $sheetCharts->setTitle('Gráficos');
        // Logs por tipo (barras)
        $sheetCharts->setCellValue('A1','Tipo de Acción');
        $sheetCharts->setCellValue('B1','Cantidad');
        $i=2;
        foreach($logs_por_tipo as $tipo=>$cant){
            $sheetCharts->setCellValue('A'.$i,$tipo);
            $sheetCharts->setCellValue('B'.$i,$cant);
            $i++;
        }
        // Ratings distribución (barras)
        $sheetCharts->setCellValue('D1','Rating');
        $sheetCharts->setCellValue('E1','Cantidad');
        $i=2;
        foreach($ratings_dist as $val=>$cant){
            $sheetCharts->setCellValue('D'.$i,$val);
            $sheetCharts->setCellValue('E'.$i,$cant);
            $i++;
        }
        // Sugerencias por fecha (línea)
        $sheetCharts->setCellValue('G1','Fecha');
        $sheetCharts->setCellValue('H1','Cantidad');
        $i=2;
        foreach($sug_por_fecha as $fecha_sug=>$cant){
            $sheetCharts->setCellValue('G'.$i,$fecha_sug);
            $sheetCharts->setCellValue('H'.$i,$cant);
            $i++;
        }
        // Aquí puedes agregar gráficos nativos de PhpSpreadsheet si lo deseas
        // Descargar Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    } elseif ($tipo === 'pdf') {
        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->SetTitle('Informe Detallado Admin');

        // --- PORTADA NUEVO DISEÑO ---
        $html = '<div style="background:#fecd02;padding:30px 0 10px 0;text-align:center;">
            '.(file_exists($logo_path)?'<img src="data:image/png;base64,'.base64_encode(file_get_contents($logo_path)).'" style="height:90px;margin-bottom:10px;"><br>':'').'
            <span style="font-size:2.2em;font-weight:bold;color:#1f2029;letter-spacing:2px;font-family:Space Grotesk,sans-serif;">SAVING SECURE</span><br>
            <span style="font-size:1.3em;font-weight:bold;color:#1f2029;font-family:Space Grotesk,sans-serif;">Informe Detallado de Administración</span>
        </div>';
        $html .= '<div style="margin:30px 0 10px 0;text-align:right;font-size:1em;color:#1f2029;">
            <b>Administrador:</b> '.htmlspecialchars($nombre_admin).'<br>
            <b>Fecha de generación:</b> '.date('d-m-Y').'
        </div>';
        $html .= '<hr style="border:0;border-top:2px solid #fecd02;margin:0 0 30px 0;">';
        $mpdf->WriteHTML($html);

        // --- SECCIÓN LOGS ---
        $html = '<h2 style="color:#1f2029;background:#fecd02;padding:16px 0 16px 30px;margin:30px 0 0 0;font-size:1.5em;font-family:Space Grotesk,sans-serif;font-weight:bold;text-align:left;">Resumen de Logs</h2>';
        $html .= '<table style="width:100%;border-collapse:collapse;font-size:15px;margin-bottom:30px;">';
        $html .= '<tr style="background:#1f2029;color:#fff;font-weight:bold;">'
            .'<th style="padding:12px 0 12px 20px;color:#fff;">ID</th><th style="padding:12px 0 12px 20px;color:#fff;">Usuario</th><th style="padding:12px 0 12px 20px;color:#fff;">Tipo</th><th style="padding:12px 0 12px 20px;color:#fff;">Descripción</th><th style="padding:12px 0 12px 20px;color:#fff;">Fecha y Hora</th></tr>';
        $i=0;
        foreach ($logs as $log) {
            $bg = $i%2==0 ? '#fff' : '#f6f6f6';
            $html .= '<tr style="background:'.$bg.';">'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($log['id_log']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($log['nombre']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($log['tipo_accion']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($log['descripcion']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($log['fecha_hora']).'</td>'
                .'</tr>';
            $i++;
        }
        $html .= '</table>';
        $mpdf->WriteHTML($html);

        // --- SECCIÓN SUGERENCIAS ---
        $html = '<h2 style="color:#1f2029;background:#fecd02;padding:16px 0 16px 30px;margin:30px 0 0 0;font-size:1.5em;font-family:Space Grotesk,sans-serif;font-weight:bold;text-align:left;">Resumen de Sugerencias</h2>';
        $html .= '<table style="width:100%;border-collapse:collapse;font-size:15px;margin-bottom:30px;">';
        $html .= '<tr style="background:#1f2029;color:#fff;font-weight:bold;">'
            .'<th style="padding:12px 0 12px 20px;color:#fff;">ID</th><th style="padding:12px 0 12px 20px;color:#fff;">Nombre</th><th style="padding:12px 0 12px 20px;color:#fff;">Correo</th><th style="padding:12px 0 12px 20px;color:#fff;">Sugerencia</th><th style="padding:12px 0 12px 20px;color:#fff;">Fecha</th></tr>';
        $i=0;
        foreach ($sugerencias as $sug) {
            $bg = $i%2==0 ? '#fff' : '#f6f6f6';
            $html .= '<tr style="background:'.$bg.';">'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($sug['id_sugerencia']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($sug['Nombre']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($sug['Correo']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($sug['Sugerencia']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($sug['Fecha']).'</td>'
                .'</tr>';
            $i++;
        }
        $html .= '</table>';
        $mpdf->WriteHTML($html);

        // --- SECCIÓN RATINGS ---
        $html = '<h2 style="color:#1f2029;background:#fecd02;padding:16px 0 16px 30px;margin:30px 0 0 0;font-size:1.5em;font-family:Space Grotesk,sans-serif;font-weight:bold;text-align:left;">Resumen de Ratings</h2>';
        $html .= '<table style="width:100%;border-collapse:collapse;font-size:15px;margin-bottom:30px;">';
        $html .= '<tr style="background:#1f2029;color:#fff;font-weight:bold;">'
            .'<th style="padding:12px 0 12px 20px;color:#fff;">ID</th><th style="padding:12px 0 12px 20px;color:#fff;">Rating</th><th style="padding:12px 0 12px 20px;color:#fff;">Fecha</th></tr>';
        $i=0;
        foreach ($ratings as $rat) {
            $bg = $i%2==0 ? '#fff' : '#f6f6f6';
            $html .= '<tr style="background:'.$bg.';">'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($rat['id']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($rat['rating']).'</td>'
                .'<td style="padding:10px 0 10px 20px;">'.htmlspecialchars($rat['created_at']).'</td>'
                .'</tr>';
            $i++;
        }
        $html .= '</table>';

        // Pie de página
        $html .= '<div style="text-align:center;color:#1f2029;font-size:12px;margin-top:40px;">Este informe fue generado automáticamente por Saving Secure.<br>&copy; '.date('Y').' Saving Secure. Todos los derechos reservados.</div>';
        $mpdf->WriteHTML($html);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '.pdf"');
        $mpdf->Output($nombre_archivo . '.pdf', 'D');
        exit();
    }
}
// --- 3. Si no hay parámetro, mostrar la interfaz y SweetAlert ---
$pdfName = $nombre_archivo . '.pdf';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="assets/cdn/sweetalert2@11.js"></script>
    <style>
        body {
            background: #1f2029;
            color: #fecd02;
            font-family: 'Space Grotesk', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .loading {
            text-align: center;
        }
        .loading p {
            margin: 10px 0;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
<script>
function descargarArchivo(url, nombre) {
    return new Promise((resolve, reject) => {
        var a = document.createElement('a');
        a.href = url;
        a.download = nombre;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        resolve();
    });
}
window.onload = async function() {
    try {
        Swal.fire({
            title: "Generando informe...",
            text: "Por favor, espere.",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        await descargarArchivo('generar_informe_admin.php?tipo=pdf', '<?php echo $pdfName; ?>');
        Swal.fire({
            icon: 'success',
            title: '¡Informe descargado!',
            text: 'Se ha descargado el PDF correctamente.',
            timer: 3000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = 'panel_admin.php';
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al descargar el archivo PDF.',
            confirmButtonText: 'Volver',
            confirmButtonColor: '#fecd02'
        }).then(() => {
            window.location.href = 'panel_admin.php';
        });
    }
}
</script>
</body>
</html>
<?php exit; ?>
