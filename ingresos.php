<?php
session_start();
include('db.php');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// CONSULTAS PARA RESUMENES Y GRÁFICOS
// Resumen por mes
$queryResumenMes = "SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as mes,
    SUM(monto) as total_mes
    FROM ingresos 
    WHERE id_usuario = $id_usuario 
    GROUP BY mes 
    ORDER BY mes DESC 
    LIMIT 12";

$resultResumenMes = mysqli_query($conn, $queryResumenMes);
$resumenMeses = [];
while ($row = mysqli_fetch_assoc($resultResumenMes)) {
    $resumenMeses[] = $row;
}

// Resumen por año
$queryResumenAnio = "SELECT 
    YEAR(fecha) as anio,
    SUM(monto) as total_anio
    FROM ingresos 
    WHERE id_usuario = $id_usuario 
    GROUP BY anio 
    ORDER BY anio DESC";

$resultResumenAnio = mysqli_query($conn, $queryResumenAnio);
$resumenAnios = [];
while ($row = mysqli_fetch_assoc($resultResumenAnio)) {
    $resumenAnios[] = $row;
}

// Datos para gráficos
$queryGraficoMensual = "SELECT 
    DATE_FORMAT(fecha, '%Y-%m') as mes,
    SUM(monto) as total
    FROM ingresos 
    WHERE id_usuario = $id_usuario 
    GROUP BY mes 
    ORDER BY mes DESC 
    LIMIT 12";

$resultGraficoMensual = mysqli_query($conn, $queryGraficoMensual);
$datosGraficoMensual = [];
while ($row = mysqli_fetch_assoc($resultGraficoMensual)) {
    $datosGraficoMensual[] = $row;
}

// AGREGAR INGRESO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $monto = str_replace(['.',',',' '], '', $_POST['monto']);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $creado_en = date('Y-m-d H:i:s');
    if ($nombre && is_numeric($monto) && $monto > 0) {
        $stmt = $conn->prepare("INSERT INTO ingresos (id_usuario, nombre, descripcion, monto, fecha, creado_en) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $id_usuario, $nombre, $descripcion, $monto, $fecha, $creado_en);
        $stmt->execute();
        $mensaje = $stmt->affected_rows > 0 ? 'Ingreso agregado exitosamente.' : 'Error al agregar ingreso.';
        $tipo = $stmt->affected_rows > 0 ? 'success' : 'error';
        $stmt->close();
    } else {
        $mensaje = 'Por favor, ingrese un nombre y un monto válido.';
        $tipo = 'error';
    }
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo'] = $tipo;
    header("Location: ingresos.php");
    exit();
}

// EDITAR INGRESO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $monto = str_replace(['.',',',' '], '', $_POST['monto']);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    if ($nombre && is_numeric($monto) && $monto > 0) {
        $stmt = $conn->prepare("UPDATE ingresos SET nombre=?, descripcion=?, monto=?, fecha=? WHERE id=? AND id_usuario=?");
        $stmt->bind_param("ssisii", $nombre, $descripcion, $monto, $fecha, $id, $id_usuario);
        $stmt->execute();
        $mensaje = $stmt->affected_rows > 0 ? 'Ingreso actualizado.' : 'No se realizaron cambios.';
        $tipo = $stmt->affected_rows > 0 ? 'success' : 'info';
        $stmt->close();
    } else {
        $mensaje = 'Por favor, ingrese un nombre y un monto válido.';
        $tipo = 'error';
    }
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo'] = $tipo;
    header("Location: ingresos.php");
    exit();
}

// ELIMINAR INGRESO
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("DELETE FROM ingresos WHERE id=? AND id_usuario=?");
    $stmt->bind_param("ii", $id, $id_usuario);
    $stmt->execute();
    $mensaje = $stmt->affected_rows > 0 ? 'Ingreso eliminado.' : 'No se pudo eliminar.';
    $tipo = $stmt->affected_rows > 0 ? 'success' : 'error';
    $stmt->close();
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo'] = $tipo;
    header("Location: ingresos.php");
    exit();
}

// PAGINACIÓN Y LISTADO
$queryTotal = "SELECT COUNT(*) AS total FROM ingresos WHERE id_usuario = $id_usuario";
$resultTotal = mysqli_query($conn, $queryTotal);
$total = mysqli_fetch_assoc($resultTotal)['total'];
$total_paginas = ceil($total / $registros_por_pagina);

$query = "SELECT * FROM ingresos WHERE id_usuario = $id_usuario ORDER BY fecha DESC, creado_en DESC LIMIT $inicio, $registros_por_pagina";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresos | Saving Secure</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/categoria.css">
    <link rel="icon" href="assets/img/logo.webp">
    <link href="https://fonts.googleapis.com/css?family=Quicksand:600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos para SweetAlert2 */
        .swal2-popup {
            background-color: white !important;
            color: black !important;
        }

        .swal2-title {
            color: black !important;
        }

        .swal2-content {
            color: #fff !important;
        }

        .swal2-confirm {
            background-color: #fecd02 !important;
            color: #1a1b26 !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }

        .swal2-deny {
            background-color: #4CAF50 !important;
            color: #fff !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }

        .swal2-cancel {
            background-color: #6c757d !important;
            color: #fff !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }

        .swal2-confirm:hover,
        .swal2-deny:hover,
        .swal2-cancel:hover {
            opacity: 0.9;
        }

        .resumen-tarjetas-centrado {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 2vw;
            margin: 40px auto 50px auto;
            flex-wrap: wrap;
            max-width: 1200px;
            position: relative;
            z-index: 2;
        }
        .tarjeta-resumen-innovadora {
            background: rgba(26,27,38,0.85);
            border-radius: 22px;
            box-shadow: 0 8px 32px 0 rgba(254,205,2,0.10), 0 1.5px 8px 0 #fecd0255;
            padding: 38px 38px 30px 38px;
            min-width: 280px;
            max-width: 280px;
            min-height: 220px;
            max-height: 220px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            flex: 1 1 260px;
            margin: 10px 0;
            overflow: hidden;
            transition: transform 0.18s cubic-bezier(.4,2,.3,1), box-shadow 0.18s;
            border: 2.5px solid transparent;
            background: linear-gradient(135deg, rgba(26,27,38,0.95) 80%, rgba(254,205,2,0.08) 100%);
            backdrop-filter: blur(6px);
            animation: tarjetaFadeIn 0.7s cubic-bezier(.4,2,.3,1);
        }
        .tarjeta-resumen-innovadora:hover {
            transform: translateY(-8px) scale(1.03) rotate(-1deg);
            box-shadow: 0 12px 40px 0 #fecd0255, 0 2px 12px 0 #fecd02cc;
            border: 2.5px solid #fecd02;
        }
        @keyframes tarjetaFadeIn {
            0% { opacity: 0; transform: scale(0.95) translateY(30px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        .icono-tarjeta-amarillo {
            font-size: 2.8em;
            color: #fecd02;
            margin-bottom: 12px;
            filter: drop-shadow(0 0 8px #fecd02aa);
            transition: color 0.2s, filter 0.2s;
        }
        .tarjeta-resumen-innovadora:hover .icono-tarjeta-amarillo {
            color: #fff700;
            filter: drop-shadow(0 0 16px #fff700cc);
        }
        .info-tarjeta-innovadora {
            color: #fff;
            margin-bottom: 5px;
            width: 100%;
        }
        .titulo-tarjeta-innovadora {
            font-size: 1.15em;
            color: #fecd02;
            font-weight: 700;
            display: block;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .valor-tarjeta-innovadora {
            font-size: 1.7em;
            font-weight: bold;
            color: #fff;
            margin-top: 2px;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 8px #00000033;
        }
        @media (max-width: 1200px) {
            .resumen-tarjetas-centrado { max-width: 98vw; }
        }
        @media (max-width: 900px) {
            .resumen-tarjetas-centrado { flex-direction: column; gap: 18px; align-items: center; }
            .tarjeta-resumen-innovadora { min-width: unset; width: 98vw; max-width: 400px; min-height: 160px; max-height: none; }
        }
        /* Modal de menú personalizado */
        #customMenuModal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(31, 32, 41, 0.85);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            animation: menuFadeInBg 0.5s cubic-bezier(.4,2,.3,1);
        }
        #customMenuModal.show {
            display: flex;
        }
        .custom-menu-modal-content {
            background: #23232e;
            color: #fff;
            padding: 40px 40px 30px 40px;
            border-radius: 28px;
            border: none;
            min-width: 300px;
            box-shadow: 0 8px 32px 0 rgba(254,205,2,0.10), 0 1.5px 8px 0 #fecd0255;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            animation: menuScaleIn 0.6s cubic-bezier(.4,2,.3,1);
        }
        .custom-menu-close {
            position: absolute;
            top: 22px;
            right: 32px;
            font-size: 2.2em;
            color: #fecd02;
            cursor: pointer;
            font-weight: bold;
            transition: color 0.2s;
        }
        .custom-menu-close:hover {
            color: #fff700;
        }
        .custom-menu-modal-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
        .custom-menu-modal-content li {
            margin: 28px 0;
            text-align: left;
            display: flex;
            align-items: center;
        }
        .custom-menu-modal-content li .menu-bar {
            width: 4px;
            height: 32px;
            background: #fecd02;
            border-radius: 2px;
            margin-right: 18px;
            display: inline-block;
        }
        .custom-menu-modal-content a {
            color: #fff;
            text-decoration: none;
            font-size: 1.35em;
            font-weight: 500;
            padding: 0;
            display: block;
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
        }
        .custom-menu-modal-content a:hover {
            color: #fecd02;
            background: none;
        }
        @keyframes menuFadeInBg {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        @keyframes menuScaleIn {
            0% { opacity: 0; transform: scale(0.85) translateY(40px); }
            60% { opacity: 1; transform: scale(1.05) translateY(-8px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        /* Modal de filtros de ingresos */
        #buscarModalIngresos {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(31, 32, 41, 0.85);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
        }
        #buscarModalIngresos.show {
            display: flex;
        }
        .filtros-modal-content {
            background: #1f2029;
            color: white;
            padding: 40px 30px 30px 30px;
            border-radius: 18px;
            border: 2px solid #fecd02;
            min-width: 300px;
            max-width: 400px;
            width: 90vw;
            box-shadow: 0 4px 24px #0002;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .filtros-modal-close {
            position: absolute;
            top: 18px;
            right: 22px;
            font-size: 2em;
            color: #fecd02;
            cursor: pointer;
        }
        .filtros-modal-content label {
            color: #fff;
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        .filtros-modal-content input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #fecd02;
            border-radius: 8px;
            background: #2a2b38;
            color: white;
        }
        .filtros-modal-content button {
            background: #fecd02;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            color: #1a1b26;
        }
        .filtros-modal-content button:hover {
            background: #e0b702;
        }
        .filtros-modal-content .modal-actions {
            margin-top: 18px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .swal2-title-black {
            color: #222 !important;
        }
        .swal2-confirm-yellow {
            background-color: #fecd02 !important;
            color: #222 !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }
        .swal2-cancel-gray {
            background-color: #e0e0e0 !important;
            color: #222 !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }
    </style>
</head>
<body>
<header class="header" id="header">
    <figure class="logo">
        <img src="assets/img/logo.webp" height="60" alt="Logo de la página">
        <p class="site-title" style="color: #fecd02;  font-size: 20px;">Saving <span class="nombre">Secure</span></p>
    </figure>
    <div class="navbar-header">
        <span class="welcome-message">Bienvenido, <span class="nombre"><?php echo $_SESSION['nombre']; ?></span></span>
    </div>
</header>
<nav class="menu">
    <ol>
        <li>
            <button onclick="abrirMenuModal()" class="btn" style="color: #fecd02; border: 2px solid #fecd02; border-radius: 8px; padding: 2px 8px; font-size: 1.1em; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center; background: none; min-width: 25px; min-height: 25px;">
                <span style="font-size: 1.2em; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">&#9776;</span>
            </button>
        </li>
    </ol>
</nav>
<!-- Modal de menú personalizado -->
<div id="customMenuModal" class="custom-menu-modal">
    <div class="custom-menu-modal-content">
        <span class="custom-menu-close" onclick="cerrarMenuModal()">&times;</span>
        <ul>
            <li><span class="menu-bar"></span><a href="#" onclick="cerrarMenuModal(); generarInforme(); return false;">Informe</a></li>
            <li><span class="menu-bar"></span><a href="dashboard.php">Volver</a></li>
        </ul>
    </div>
</div>
<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; min-height: 60vh;">
            <!-- Tarjetas de Resumen Mejoradas -->
            <div class="resumen-tarjetas-centrado">
                <div class="tarjeta-resumen-innovadora">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-calendar-alt"></i></div>
                    <div class="info-tarjeta-innovadora">
                        <span class="titulo-tarjeta-innovadora">Ingresos del Mes</span>
                        <span class="valor-tarjeta-innovadora">$ <?= number_format($resumenMeses[0]['total_mes'] ?? 0, 0, ',', '.') ?> COP</span>
                    </div>
                </div>
                <div class="tarjeta-resumen-innovadora">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-calendar"></i></div>
                    <div class="info-tarjeta-innovadora">
                        <span class="titulo-tarjeta-innovadora">Ingresos del Año</span>
                        <span class="valor-tarjeta-innovadora">$ <?= number_format($resumenAnios[0]['total_anio'] ?? 0, 0, ',', '.') ?> COP</span>
                    </div>
                </div>
                <div class="tarjeta-resumen-innovadora">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-line"></i></div>
                    <div class="info-tarjeta-innovadora">
                        <span class="titulo-tarjeta-innovadora">Promedio Mensual</span>
                        <span class="valor-tarjeta-innovadora">$ <?= number_format(($resumenAnios[0]['total_anio'] ?? 0) / 12, 0, ',', '.') ?> COP</span>
                    </div>
                </div>
            </div>
<main class="dashboard-main">
    <div class="dashboard-content">
        
        <br>

        <!-- Buscador y tabla -->
            
                <!-- Botón Buscar y Modal de Filtros Avanzados -->
                <button id="btnBuscarIngresos" class="btnBuscar" type="button" style="margin-bottom: 18px; margin-right: 10px; background: #1a1b26; color: #fecd02; border: 2px solid #fecd02; border-radius: 0.5em; font-size: 1.1em; font-weight: bold; cursor: pointer;">
                    🔍 Buscar
                </button>

                <div id="buscarModalIngresos" class="modal" style="display:none; align-items:center; justify-content:center;">
                    <div class="modalDialogContent" style="background:#1f2029; color:white; padding:30px; border-radius:18px; border:2px solid #fecd02; max-width:400px; width:90vw;">
                        <span class="closeButton" onclick="cerrarModalIngresos()" style="position:absolute; top:10px; right:18px; font-size:2em; color:#fecd02; cursor:pointer;">&times;</span>
                        <h2 style="color:#fecd02; text-align:center;">Filtrar Ingresos</h2>
                        <label>Nombre:</label>
                        <input type="text" id="filtroNombreIngreso" class="form-style3">
                        <label>Descripción:</label>
                        <input type="text" id="filtroDescripcionIngreso" class="form-style3">
                        <label>Fecha Desde:</label>
                        <input type="date" id="filtroFechaDesdeIngreso" class="form-style3">
                        <label>Fecha Hasta:</label>
                        <input type="date" id="filtroFechaHastaIngreso" class="form-style3">
                        <label>Monto Mínimo:</label>
                        <input type="text" id="filtroMontoMinIngreso" class="form-style3">
                        <label>Monto Máximo:</label>
                        <input type="text" id="filtroMontoMaxIngreso" class="form-style3">
                        <div style="margin-top:18px; display:flex; gap:10px; justify-content:center;">
                            <button onclick="aplicarFiltrosIngresos()" type="button" style="width: 10em; height: 2.0em; border: 3px solid #fecd02; background-color: #1a1b26; color: #fecd02; border-radius: 0.5em; font-size: 16px; font-weight: bold; cursor: pointer;">Aplicar Filtros</button>
                            <button onclick="resetearFiltrosIngresos()" type="button" style="width: 10em; height: 2.0em; border: 3px solid #fecd02; background-color: #1a1b26; color: #fecd02; border-radius: 0.5em; font-size: 16px; font-weight: bold; cursor: pointer;">Resetear</button>
                        </div>
                    </div>
                </div>
                
        <table class="tabla3" id="tablaIngresos">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr data-id="<?php echo $row['id']; ?>">
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                            <td><?php echo number_format($row['monto'], 0, ',', '.'); ?> COP</td>
                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                            <td>
                                <button class="btn-editar" onclick="abrirEditarIngreso(
                                    <?php echo $row['id']; ?>,
                                    '<?php echo htmlspecialchars(addslashes($row['nombre'])); ?>',
                                    '<?php echo htmlspecialchars(addslashes($row['descripcion'])); ?>',
                                    '<?php echo number_format($row['monto'], 0, '', ''); ?>',
                                    '<?php echo $row['fecha']; ?>'
                                )">Editar</button>
                                <button type="button" class="btn-eliminar" onclick="confirmarEliminar(<?php echo $row['id']; ?>)">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="paginacion">
                    <div class="paginacion-container">
                        <?php if ($total_paginas > 0): ?>
                            <?php if ($pagina_actual > 1) : ?>
                                <a href="?pagina=<?= $pagina_actual - 1 ?>" class="btn nav">« Anterior</a>
                            <?php endif; ?>
                            <?php
                            $total_visible = 5;
                            $mitad = floor($total_visible / 2);
                            if ($total_paginas <= $total_visible) {
                                $inicio = 1;
                                $fin = $total_paginas;
                            } else {
                                if ($pagina_actual <= $mitad) {
                                    $inicio = 1;
                                    $fin = $total_visible;
                                } elseif ($pagina_actual > $total_paginas - $mitad) {
                                    $inicio = $total_paginas - $total_visible + 1;
                                    $fin = $total_paginas;
                                } else {
                                    $inicio = $pagina_actual - $mitad;
                                    $fin = $pagina_actual + $mitad;
                                }
                            }
                            if ($inicio > 1) {
                                echo '<a href="?pagina=1" class="btn">1</a>';
                                if ($inicio > 2) {
                                    echo '<span class="separator">...</span>';
                                }
                            }
                            for ($i = $inicio; $i <= $fin; $i++) {
                                echo '<a href="?pagina=' . $i . '" class="btn ' . ($i == $pagina_actual ? 'activo' : '') . '">' . $i . '</a>';
                            }
                            if ($fin < $total_paginas) {
                                if ($fin < $total_paginas - 1) {
                                    echo '<span class="separator">...</span>';
                                }
                                echo '<a href="?pagina=' . $total_paginas . '" class="btn">' . $total_paginas . '</a>';
                            }
                            ?>
                            <?php if ($pagina_actual < $total_paginas) : ?>
                                <a href="?pagina=<?= $pagina_actual + 1 ?>" class="btn nav">Siguiente »</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="no-data">No hay registros disponibles</p>
                        <?php endif; ?>
                    </div>
                    <div class="page-counter">
                        Página <?= $total_paginas > 0 ? $pagina_actual : 0 ?> de <?= $total_paginas ?>
                    </div>
                </div>
        <!-- Contenedor principal centrado -->
       

            <!-- Gráficos Mejorados -->
            <div class="grafico-container" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2em; margin-bottom: 2em;">
                <div class="grafico-circular" style="background: #23243a; border-radius: 1em; padding: 1.5em; min-width: 320px; min-height: 320px; box-shadow: 0 4px 24px #0002; display: flex; align-items: center; justify-content: center;">
                    <canvas id="graficoCircular" style="width: 100%; height: 260px;"></canvas>
                </div>
                <div class="grafico-barras" style="background: #23243a; border-radius: 1em; padding: 1.5em; min-width: 320px; min-height: 320px; box-shadow: 0 4px 24px #0002; display: flex; align-items: center; justify-content: center;">
                    <canvas id="graficoBarras" style="width: 100%; height: 260px;"></canvas>
                </div>
            </div>

            

                
            </div>
        </div>
    </div>
</main>

<div class="caja">
            <h2 style="color: #fecd02; text-align: center; margin-bottom: 20px;">Gestionar Ingresos</h2>
            <?php if (isset($mensaje)): ?>
                <div class="alerta <?php echo $tipo; ?>"> <?php echo $mensaje; ?> </div>
            <?php endif; ?>
            <form id="formAgregar" method="POST" action="" autocomplete="off">
                <input type="hidden" name="accion" value="agregar">
                <label for="nombre">Nombre del Ingreso</label>
                <input type="text" id="nombre" name="nombre" class="form-style3" required>
                <label for="descripcion">Descripción</label>
                <input type="text" id="descripcion" name="descripcion" class="form-style3">
                <label for="monto">Monto</label>
                <input type="text" id="monto" name="monto" class="form-style3" required>
                <label for="fecha">Fecha</label>
                <input type="date" id="fecha" name="fecha" class="form-style3" value="<?php echo date('Y-m-d'); ?>" required>
                <button type="submit" class="btn-editar" style="width: 100%;">Agregar Ingreso</button>
            </form>
        </div>
<script>
// Formatear monto COP en el input
$(document).on('input', '#monto', function() {
    let value = $(this).val().replace(/\D/g, "");
    $(this).val(new Intl.NumberFormat("es-CO").format(value));
});

// Nueva función para editar con SweetAlert2
function abrirEditarIngreso(id, nombre, descripcion, monto, fecha) {
    Swal.fire({
        title: 'Editar Ingreso',
        html:
            `<input id="swal-nombre" class="swal2-input" placeholder="Nombre" value="${nombre}">` +
            `<input id="swal-descripcion" class="swal2-input" placeholder="Descripción" value="${descripcion}">` +
            `<input id="swal-monto" class="swal2-input" placeholder="Monto" value="${new Intl.NumberFormat('es-CO').format(monto)}">` +
            `<input id="swal-fecha" type="date" class="swal2-input" value="${fecha}">`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        didOpen: () => {
            const inputMonto = document.getElementById('swal-monto');
            inputMonto.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, "");
                this.value = new Intl.NumberFormat("es-CO").format(value);
            });
        },
        preConfirm: () => {
            return {
                nombre: document.getElementById('swal-nombre').value,
                descripcion: document.getElementById('swal-descripcion').value,
                monto: document.getElementById('swal-monto').value,
                fecha: document.getElementById('swal-fecha').value
            }
        },
        background: '#fff',
        color: '#222',
        customClass: {
            title: 'swal2-title-black',
            confirmButton: 'swal2-confirm-yellow',
            cancelButton: 'swal2-cancel-gray'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (!result.value.nombre || !result.value.monto || isNaN(result.value.monto.replace(/\D/g, '')) || parseInt(result.value.monto.replace(/\D/g, '')) <= 0) {
                Swal.fire('Error', 'Por favor, ingrese un nombre y un monto válido.', 'error');
                return;
            }
            $.post('ingresos.php', {
                accion: 'editar',
                id: id,
                nombre: result.value.nombre,
                descripcion: result.value.descripcion,
                monto: result.value.monto,
                fecha: result.value.fecha
            }, function() {
                Swal.fire('¡Actualizado!', 'El ingreso ha sido actualizado.', 'success').then(() => {
                    location.reload();
                });
            });
        }
    });
}

// Abrir modal
$(document).on('click', '#btnBuscarIngresos', function() {
    $('#buscarModalIngresos').css('display', 'flex');
});
// Cerrar modal
function cerrarModalIngresos() {
    $('#buscarModalIngresos').css('display', 'none');
}
// Cerrar modal al hacer click fuera
window.addEventListener('click', function(event) {
    let modal = document.getElementById('buscarModalIngresos');
    if (event.target === modal) {
        cerrarModalIngresos();
    }
});
// Formatear monto en filtros
$('#filtroMontoMinIngreso, #filtroMontoMaxIngreso').on('input', function() {
    let value = $(this).val().replace(/\D/g, "");
    $(this).val(new Intl.NumberFormat("es-CO").format(value));
});
// Aplicar filtros
function aplicarFiltrosIngresos() {
    let nombre = $('#filtroNombreIngreso').val().toLowerCase();
    let descripcion = $('#filtroDescripcionIngreso').val().toLowerCase();
    let fechaDesde = $('#filtroFechaDesdeIngreso').val();
    let fechaHasta = $('#filtroFechaHastaIngreso').val();
    let montoMin = parseInt($('#filtroMontoMinIngreso').val().replace(/\D/g, '')) || 0;
    let montoMax = parseInt($('#filtroMontoMaxIngreso').val().replace(/\D/g, '')) || Infinity;
    $('#tablaIngresos tbody tr').each(function() {
        let tds = $(this).find('td');
        let nombreIngreso = tds.eq(0).text().toLowerCase();
        let descripcionIngreso = tds.eq(1).text().toLowerCase();
        let montoIngreso = parseInt(tds.eq(2).text().replace(/\D/g, ''));
        let fechaIngreso = tds.eq(3).text();
        let mostrar = true;
        if (nombre && !nombreIngreso.includes(nombre)) mostrar = false;
        if (descripcion && !descripcionIngreso.includes(descripcion)) mostrar = false;
        if (fechaDesde && fechaIngreso < fechaDesde) mostrar = false;
        if (fechaHasta && fechaIngreso > fechaHasta) mostrar = false;
        if (montoIngreso < montoMin || montoIngreso > montoMax) mostrar = false;
        $(this).css('display', mostrar ? '' : 'none');
    });
    cerrarModalIngresos();
}
// Resetear filtros
function resetearFiltrosIngresos() {
    $('#filtroNombreIngreso').val('');
    $('#filtroDescripcionIngreso').val('');
    $('#filtroFechaDesdeIngreso').val('');
    $('#filtroFechaHastaIngreso').val('');
    $('#filtroMontoMinIngreso').val('');
    $('#filtroMontoMaxIngreso').val('');
    $('#tablaIngresos tbody tr').css('display', '');
    cerrarModalIngresos();
}

// Gráficos
document.addEventListener('DOMContentLoaded', function() {
    // Datos para los gráficos
    const meses = <?php 
    $meses_graf = array_column($datosGraficoMensual, 'mes');
    $totales_graf = array_column($datosGraficoMensual, 'total');
    if (count($meses_graf) === 0) {
        $meses_graf = [date('Y-m')];
        $totales_graf = [0];
    }
    echo json_encode($meses_graf); 
    ?>;
    const totales = <?php echo json_encode($totales_graf); ?>;

    // Gráfico Circular
    const ctxCircular = document.getElementById('graficoCircular').getContext('2d');
    new Chart(ctxCircular, {
        type: 'doughnut',
        data: {
            labels: meses,
            datasets: [{
                data: totales,
                backgroundColor: [
                    'rgba(254, 205, 2, 0.9)',
                    'rgba(254, 205, 2, 0.8)',
                    'rgba(254, 205, 2, 0.7)',
                    'rgba(254, 205, 2, 0.6)',
                    'rgba(254, 205, 2, 0.5)',
                    'rgba(255, 213, 79, 0.9)',
                    'rgba(255, 213, 79, 0.8)',
                    'rgba(255, 213, 79, 0.7)'
                ],
                borderWidth: 3,
                borderColor: '#1a1b26',
                hoverOffset: 8,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#FFF',
                        padding: 15,
                        font: {
                            size: 14,
                            family: 'Quicksand',
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1a1b26',
                    titleColor: '#fecd02',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${new Intl.NumberFormat('es-CO', {
                                style: 'decimal',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(context.raw)} COP`;
                        }
                    }
                }
            },
            cutout: '75%',
            rotation: Math.PI * 0.5
        }
    });

    // Gráfico de Barras
    const ctxBarras = document.getElementById('graficoBarras').getContext('2d');
    new Chart(ctxBarras, {
        type: 'bar',
        data: {
            labels: meses,
            datasets: [{
                label: 'Ingresos por Mes',
                data: totales,
                backgroundColor: 'rgba(254, 205, 2, 0.9)',
                borderWidth: 2,
                borderColor: '#1a1b26',
                hoverBackgroundColor: '#fecd02',
                borderRadius: 12,
                barPercentage: 0.85,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#FFF',
                        padding: 15,
                        font: {
                            size: 14,
                            family: 'Quicksand',
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#1a1b26',
                    titleColor: '#fecd02',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return `${context.dataset.label}: ${new Intl.NumberFormat('es-CO', {
                                style: 'decimal',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(context.raw)} COP`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#FFF',
                        font: {
                            family: 'Quicksand',
                            weight: '600'
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#FFF',
                        font: {
                            family: 'Quicksand',
                            weight: '600'
                        },
                        callback: function(value) {
                            return new Intl.NumberFormat('es-CO', {
                                style: 'decimal',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(value) + ' COP';
                        }
                    },
                    grid: {
                        color: 'rgba(254, 205, 2, 0.1)',
                        borderDash: [8, 8]
                    }
                }
            }
        }
    });
});

// Función para generar el informe
function generarInforme() {
    Swal.fire({
        title: "Generando informe...",
        text: "Por favor, espere.",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch("generar_informe_ingresos.php?enviar=1")
        .then(response => response.text())
        .then(data => {
            let msg = 'El informe ha sido enviado a tu correo.';
            try { const json = JSON.parse(data); if (typeof json === 'string') msg = json; else if (json.message) msg = json.message; } catch(e) {}
            Swal.fire("Informe Generado", msg, "success");
        })
        .catch(error => {
            Swal.fire("Error", "No se pudo generar el informe.", "error");
        });
}

function abrirMenuModal() {
    document.getElementById('customMenuModal').classList.add('show');
}

function cerrarMenuModal() {
    document.getElementById('customMenuModal').classList.remove('show');
}

window.addEventListener('click', function(event) {
    let modal = document.getElementById('customMenuModal');
    if (event.target === modal) {
        cerrarMenuModal();
    }
});

function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#fecd02',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            form.innerHTML = `
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
<?php if (isset($_SESSION['mensaje'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '<?php echo ($_SESSION['tipo'] === "success") ? "¡Éxito!" : (($_SESSION["tipo"] === "error") ? "Error" : "Aviso"); ?>',
            text: '<?php echo $_SESSION['mensaje']; ?>',
            icon: '<?php echo $_SESSION['tipo']; ?>',
            confirmButtonColor: '#fecd02'
        });
    });
</script>
<?php unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
<?php endif; ?>
</body>
</html> 