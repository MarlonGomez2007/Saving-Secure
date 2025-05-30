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

// Construir la consulta base para contar resultados
$sql_count = "SELECT COUNT(*) as total FROM ratings WHERE 1=1";
$sql = "SELECT id, rating, created_at FROM ratings WHERE 1=1";
if (!empty($busqueda)) {
    $cond = " AND (CAST(id AS CHAR) LIKE '%" . $conn->real_escape_string($busqueda) . "%' ";
    $cond .= "OR CAST(rating AS CHAR) LIKE '%" . $conn->real_escape_string($busqueda) . "%')";
    $sql_count .= $cond;
    $sql .= $cond;
}

// Obtener total de resultados
$res_count = $conn->query($sql_count);
$total_resultados = $res_count ? (int)$res_count->fetch_assoc()['total'] : 0;
$total_paginas = ($total_resultados > 0) ? ceil($total_resultados / $por_pagina) : 1;
$offset = ($pagina - 1) * $por_pagina;

$sql .= " ORDER BY created_at DESC LIMIT $por_pagina OFFSET $offset";

try {
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Error al obtener ratings: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error en mostrar_ratings.php: " . $e->getMessage());
    echo "<div class='error-message'>
            <i class='ri-error-warning-line'></i>
            Ha ocurrido un error al obtener los ratings. Por favor, inténtelo de nuevo.
          </div>";
    $result = null;
}

// --- Cálculos para las tarjetas resumen y gráficos ---
$promedio_rating = 0;
$total_ratings = 0;
$conteo_por_valor = [1=>0,2=>0,3=>0,4=>0,5=>0];
$ratings_por_fecha = [];

$res_stats = $conn->query("SELECT rating, created_at FROM ratings");
if ($res_stats) {
    $suma = 0;
    while ($row = $res_stats->fetch_assoc()) {
        $val = (int)$row['rating'];
        $suma += $val;
        $total_ratings++;
        if (isset($conteo_por_valor[$val])) $conteo_por_valor[$val]++;
        $fecha = date('Y-m-d', strtotime($row['created_at']));
        if (!isset($ratings_por_fecha[$fecha])) $ratings_por_fecha[$fecha] = 0;
        $ratings_por_fecha[$fecha]++;
    }
    $promedio_rating = $total_ratings > 0 ? round($suma / $total_ratings, 2) : 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/panel_admin.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.webp">
    <title>Ratings | Saving Secure</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/logs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            height: 280px; /* Altura fija para todas las tarjetas */
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
            height: 100%;
            display: flex;
            flex-direction: column;
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
        canvas {
            width: 100% !important;
            height: 120px !important;
            margin-top: 10px;
            object-fit: contain;
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
            <a href="logs.php"><i class="ri-history-line"></i> Logs</a>
            <a href="mostrar_sugerencias.php"><i class="ri-feedback-line"></i> Sugerencias</a>
            <a href="mostrar_ratings.php" class="active"><i class="ri-star-line"></i> Ratings</a>
            <a href="generar_informe_admin.php"><i class="ri-download-line"></i> Descargar Informe</a>
            <a href="login.html"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-panel">
            <div class="section-header">
                <h1>Ratings de los usuarios</h1>
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="" class="search-box">
                            <input type="text" name="busqueda" placeholder="Buscar por ID o rating..." value="<?php echo htmlspecialchars($busqueda_input); ?>">
                            <button type="submit"><i class="ri-search-line" style="color: black;"></i></button>
                        </form>
                    </div>
                </div>
            </div>
    
            <div class="resumen-tarjetas-centrado">
                <!-- Promedio de rating -->
                <div class="tarjeta-resumen-innovadora">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-star"></i></div>
                    <div class="info-tarjeta-innovadora">
                        <span class="titulo-tarjeta-innovadora">Promedio de rating</span>
                        <span class="valor-tarjeta-innovadora"> <?php echo $promedio_rating; ?>
                            <?php for($i=1;$i<=5;$i++) echo $i <= round($promedio_rating) ? '<i class=\'fas fa-star\' style=\'color:#fecd02;\'></i>' : '<i class=\'far fa-star\' style=\'color:#fecd02;\'></i>'; ?>
                        </span>
                        <span style="font-size:0.95em;color:#ccc;">Total de ratings: <?php echo $total_ratings; ?></span>
                    </div>
                </div>
                <!-- Distribución de ratings -->
                <div class="tarjeta-resumen-innovadora">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-bar"></i></div>
                    <div class="info-tarjeta-innovadora">
                        <span class="titulo-tarjeta-innovadora">Distribución de ratings</span>
                        <canvas id="graficoBarrasRatings" height="90"></canvas>
                    </div>
                </div>
                <!-- Evolución de ratings por fecha -->
                <div class="tarjeta-resumen-innovadora">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-line"></i></div>
                    <div class="info-tarjeta-innovadora">
                        <span class="titulo-tarjeta-innovadora">Evolución diaria de ratings</span>
                        <canvas id="graficoLineaRatings" height="90"></canvas>
                    </div>
                </div>
            </div>

            

            <div class="logs-container">
            <div style="margin-bottom: 15px; font-weight: bold; color: #ffd700; font-size: 1.1em; text-align: right;">
                Total de Ratings: <?php echo $total_resultados; ?>
            </div>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($rat = $result->fetch_assoc()): ?>
                        <div class="log-entry log-sugerencia">
                            <div class="log-info">
                                <div class="log-descripcion"><b style="color: #ffd700;">Rating:</b> <?php 
                                    $stars = intval($rat['rating']);
                                    for ($i = 0; $i < $stars; $i++) {
                                        echo '<i class="ri-star-fill" style="color: #ffd700;"></i>';
                                    }
                                ?></div>
                                <div class="log-usuario"><b>ID Rating:</b> <?php echo htmlspecialchars($rat['id']); ?></div>
                                <div class="log-usuario"><b>Valor numérico:</b> <?php echo htmlspecialchars($rat['rating']); ?></div>
                            </div>
                            <div class="log-fecha">
                                <?php echo date('d/m/Y H:i:s', strtotime($rat['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="log-entry log-sugerencia">
                        <div class="log-info">No hay ratings registrados.</div>
                    </div>
                <?php endif; ?>
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

    <script>
    // Gráfico de barras: distribución de ratings
    const ctxBarras = document.getElementById('graficoBarrasRatings').getContext('2d');
    new Chart(ctxBarras, {
        type: 'bar',
        data: {
            labels: [1,2,3,4,5],
            datasets: [{
                label: 'Cantidad',
                data: <?php echo json_encode(array_values($conteo_por_valor)); ?>,
                backgroundColor: '#fecd02',
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                x: { 
                    grid: { display: false }, 
                    title: { display: true, text: 'Rating' },
                    ticks: { color: '#fff' }
                },
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#444' }, 
                    title: { display: true, text: 'Cantidad' },
                    ticks: { color: '#fff' }
                }
            }
        }
    });
    // Gráfico de línea: evolución diaria de ratings
    const ctxLinea = document.getElementById('graficoLineaRatings').getContext('2d');
    new Chart(ctxLinea, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($ratings_por_fecha)); ?>,
            datasets: [{
                label: 'Ratings por día',
                data: <?php echo json_encode(array_values($ratings_por_fecha)); ?>,
                fill: true,
                borderColor: '#fecd02',
                backgroundColor: 'rgba(254,205,2,0.15)',
                tension: 0.3,
                pointRadius: 2
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                x: { 
                    grid: { display: false }, 
                    title: { display: true, text: 'Fecha' },
                    ticks: { 
                        color: '#fff',
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#444' }, 
                    title: { display: true, text: 'Cantidad' },
                    ticks: { color: '#fff' }
                }
            }
        }
    });
    </script>
</body>
</html>
