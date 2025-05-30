<?php
// Incluir archivo de conexión a la base de datos
require_once 'db.php';

// Iniciar sesión
session_start();

// Verificar si el usuario es administrador (rol 2)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {  
    header('Location: login.html');
    exit();
}

// Obtener filtros
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : [];
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 5;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$busqueda_input = $busqueda; // Guardar el valor original para el input

// Construir la consulta SQL
$sql = "SELECT l.*, u.nombre 
        FROM logs l 
        LEFT JOIN usuarios u ON l.usuario_id = u.id_usuario 
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($filtro_tipo)) {
    $placeholders = str_repeat('?,', count($filtro_tipo) - 1) . '?';
    $sql .= " AND l.tipo_accion IN ($placeholders)";
    $params = array_merge($params, $filtro_tipo);
    $types .= str_repeat('s', count($filtro_tipo));
}

if (!empty($busqueda)) {
    $sql .= " AND (LOWER(l.descripcion) LIKE LOWER(?) OR LOWER(u.nombre) LIKE LOWER(?))";
    $busqueda_sql = "%$busqueda%";
    $params[] = $busqueda_sql;
    $params[] = $busqueda_sql;
    $types .= 'ss';
}

$sql .= " ORDER BY l.fecha_hora DESC";

// Obtener el total de resultados para la paginación
$sql_count = "SELECT COUNT(*) as total FROM logs l LEFT JOIN usuarios u ON l.usuario_id = u.id_usuario WHERE 1=1";
if (!empty($filtro_tipo)) {
    $placeholders = str_repeat('?,', count($filtro_tipo) - 1) . '?';
    $sql_count .= " AND l.tipo_accion IN ($placeholders)";
}
if (!empty($busqueda)) {
    $sql_count .= " AND (LOWER(l.descripcion) LIKE LOWER(?) OR LOWER(u.nombre) LIKE LOWER(?))";
}

$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_resultados = $stmt_count->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_resultados / $por_pagina);

// Añadir LIMIT a la consulta principal
$sql .= " LIMIT ? OFFSET ?";
$offset = ($pagina - 1) * $por_pagina;
$params[] = $por_pagina;
$params[] = $offset;
$types .= 'ii';

// Obtener estadísticas para las tarjetas
$sql_stats = "SELECT 
    COUNT(*) as total_logs,
    SUM(CASE WHEN tipo_accion = 'REGISTRO' THEN 1 ELSE 0 END) as total_registros,
    SUM(CASE WHEN tipo_accion = 'EDICION' THEN 1 ELSE 0 END) as total_ediciones,
    SUM(CASE WHEN tipo_accion = 'BORRADO' THEN 1 ELSE 0 END) as total_borrados,
    SUM(CASE WHEN tipo_accion = 'CAMBIO_ROL' THEN 1 ELSE 0 END) as total_cambios_rol
FROM logs";

$stats = $conn->query($sql_stats)->fetch_assoc();

// Obtener la acción más frecuente
$acciones = [
    'REGISTRO' => $stats['total_registros'],
    'EDICION' => $stats['total_ediciones'],
    'BORRADO' => $stats['total_borrados'],
    'CAMBIO_ROL' => $stats['total_cambios_rol']
];
arsort($acciones);
$accion_mas_frecuente = key($acciones);
$total_accion_mas_frecuente = current($acciones);

try {
    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Error al obtener resultados: " . $stmt->error);
    }

} catch (Exception $e) {
    // Registrar el error
    error_log("Error en logs.php: " . $e->getMessage());
    // Mostrar un mensaje amigable al usuario
    echo "<div class='error-message'>
            <i class='ri-error-warning-line'></i>
            Ha ocurrido un error al realizar la búsqueda. Por favor, inténtelo de nuevo.
          </div>";
    $result = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/panel_admin.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.webp">
    <title>Logs | Saving Secure</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/logs.css">
    <style>
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
            min-width: 260px;
            min-height: 150px;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <i class="ri-database-2-line"></i>
            Panel Admin
        </div>
        <nav class="nav-links">
            <a href="panel_admin.php"><i class="ri-user-line"></i> Usuarios</a>
            <a href="logs.php" class="active"><i class="ri-history-line"></i> Logs</a>
            <a href="mostrar_sugerencias.php"><i class="ri-feedback-line"></i> Sugerencias</a>
            <a href="mostrar_ratings.php"><i class="ri-star-line"></i> Ratings</a>
            <a href="generar_informe_admin.php"><i class="ri-download-line"></i> Descargar Informe</a>
            <a href="login.html"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-panel">
            <div class="section-header">
                <h1>Logs del Sistema</h1>
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="" class="search-box">
                            <input type="text" name="busqueda" placeholder="Buscar en logs..." value="<?php echo htmlspecialchars($busqueda_input); ?>">
                            <button type="submit"><i class="ri-search-line" style="color: black;"></i></button>
                        </form>
                    </div>
                </div>
            </div>
    
            <div class="resumen-tarjetas-centrado">
                <div class="tarjeta-resumen-innovadora" style="min-height: 200px; padding: 25px;">
                    <div class="icono-tarjeta-amarillo" style="font-size: 2.2em; margin-bottom: 8px;"><i class="ri-database-2-line"></i></div>
                    <div class="info-tarjeta-innovadora" style="height: 150px;">
                        <span class="titulo-tarjeta-innovadora">Actividad Total</span>
                        <span class="valor-tarjeta-innovadora" style="font-size: 1.5em;"><?php echo $stats['total_logs']; ?></span>
                        <div style="margin-top: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Registros:</span>
                                <span><?php echo $stats['total_registros']; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Ediciones:</span>
                                <span><?php echo $stats['total_ediciones']; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Borrados:</span>
                                <span><?php echo $stats['total_borrados']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
               
                <div class="tarjeta-resumen-innovadora" style="min-height: 200px; padding: 25px;">
                    <div class="icono-tarjeta-amarillo" style="font-size: 2.2em; margin-bottom: 8px;"><i class="ri-bar-chart-line"></i></div>
                    <div class="info-tarjeta-innovadora" style="height: 150px;">
                        <span class="titulo-tarjeta-innovadora">Acción Más Frecuente</span>
                        <span class="valor-tarjeta-innovadora" style="font-size: 1.5em;"><?php echo ucfirst(strtolower($accion_mas_frecuente)); ?></span>
                        <div style="margin-top: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Total de acciones:</span>
                                <span><?php echo $total_accion_mas_frecuente; ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span>Porcentaje total:</span>
                                <span><?php echo round(($total_accion_mas_frecuente / $stats['total_logs']) * 100, 1); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
               
                <div class="tarjeta-resumen-innovadora" style="min-height: 200px; padding: 25px;">
                    <div class="icono-tarjeta-amarillo" style="font-size: 2.2em; margin-bottom: 8px;"><i class="ri-time-line"></i></div>
                    <div class="info-tarjeta-innovadora" style="height: 150px;">
                        <span class="titulo-tarjeta-innovadora">Actividad Reciente</span>
                        <div style="margin-top: 15px;">
                            <?php
                            $sql_reciente = "SELECT tipo_accion, COUNT(*) as total 
                                           FROM logs 
                                           WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                                           GROUP BY tipo_accion";
                            $reciente = $conn->query($sql_reciente);
                            while($row = $reciente->fetch_assoc()) {
                                echo "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'>";
                                echo "<span>" . ucfirst(strtolower($row['tipo_accion'])) . ":</span>";
                                echo "<span>" . $row['total'] . "</span>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="GET" action="" class="filtros">
                <div class="filtro-checkbox">
                    <input type="checkbox" name="tipo[]" value="REGISTRO" <?php echo in_array('REGISTRO', $filtro_tipo) ? 'checked' : ''; ?>>
                    <label>Registros</label>
                </div>
                <div class="filtro-checkbox">
                    <input type="checkbox" name="tipo[]" value="EDICION" <?php echo in_array('EDICION', $filtro_tipo) ? 'checked' : ''; ?>>
                    <label>Ediciones</label>
                </div>
                <div class="filtro-checkbox">
                    <input type="checkbox" name="tipo[]" value="BORRADO" <?php echo in_array('BORRADO', $filtro_tipo) ? 'checked' : ''; ?>>
                    <label>Borrados</label>
                </div>
                <div class="filtro-checkbox">
                    <input type="checkbox" name="tipo[]" value="CAMBIO_ROL" <?php echo in_array('CAMBIO_ROL', $filtro_tipo) ? 'checked' : ''; ?>>
                    <label>Cambios de Rol</label>
                </div>
                <button type="submit">Filtrar</button>
            </form>
            
            <div class="logs-container">
            <div style="margin-bottom: 15px; font-weight: bold; color: #ffd700; font-size: 1.1em; text-align: right;">
                Total de Logs: <?php echo $total_resultados; ?>
            </div>
                <?php while ($log = $result->fetch_assoc()): ?>
                    <div class="log-entry log-<?php echo strtolower($log['tipo_accion']); ?>">
                        <div class="log-info">
                            <div class="log-descripcion"><?php echo htmlspecialchars($log['descripcion']); ?></div>
                            <div class="log-usuario">Usuario: <?php echo $log['nombre'] ? htmlspecialchars($log['nombre']) : 'Usuario eliminado'; ?></div>
                        </div>
                        <div class="log-fecha">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['fecha_hora'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="pagination">
                <?php if ($total_paginas > 1): ?>
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=<?php echo $pagina-1; ?>&busqueda=<?php echo urlencode($busqueda); ?>" class="page-link">
                            <i class="ri-arrow-left-s-line"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>" 
                           class="page-link <?php echo $i == $pagina ? 'active' : ''; ?>" >
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($pagina < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina+1; ?>&busqueda=<?php echo urlencode($busqueda); ?>" class="page-link" >
                            Siguiente <i class="ri-arrow-right-s-line"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
