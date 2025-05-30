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
$sql_count = "SELECT COUNT(*) as total FROM sugerencia WHERE 1=1";
$sql = "SELECT id_sugerencia, Nombre, Correo, Sugerencia, Fecha FROM sugerencia WHERE 1=1";
if (!empty($busqueda)) {
    $cond = " AND (LOWER(Nombre) LIKE LOWER('%" . $conn->real_escape_string($busqueda) . "%') ";
    $cond .= "OR LOWER(Correo) LIKE LOWER('%" . $conn->real_escape_string($busqueda) . "%') ";
    $cond .= "OR LOWER(Sugerencia) LIKE LOWER('%" . $conn->real_escape_string($busqueda) . "%'))";
    $sql_count .= $cond;
    $sql .= $cond;
}

// Obtener total de resultados
$res_count = $conn->query($sql_count);
$total_resultados = $res_count ? (int)$res_count->fetch_assoc()['total'] : 0;
$total_paginas = ($total_resultados > 0) ? ceil($total_resultados / $por_pagina) : 1;
$offset = ($pagina - 1) * $por_pagina;

$sql .= " ORDER BY Fecha DESC LIMIT $por_pagina OFFSET $offset";

try {
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Error al obtener sugerencias: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Error en mostrar_sugerencias.php: " . $e->getMessage());
    echo "<div class='error-message'>
            <i class='ri-error-warning-line'></i>
            Ha ocurrido un error al obtener las sugerencias. Por favor, inténtelo de nuevo.
          </div>";
    $result = null;
}

// --- Cálculos para las tarjetas resumen y gráficos de sugerencias ---
$total_sugerencias = 0;
$sugerencias_por_fecha = [];
$sugerencias_por_dia_semana = [
    'Lunes' => 0,
    'Martes' => 0,
    'Miércoles' => 0,
    'Jueves' => 0,
    'Viernes' => 0,
    'Sábado' => 0,
    'Domingo' => 0
];
$primer_dia = null;
$ultimo_dia = null;
$res_stats = $conn->query("SELECT Fecha FROM sugerencia");
if ($res_stats) {
    while ($row = $res_stats->fetch_assoc()) {
        $fecha = date('Y-m-d', strtotime($row['Fecha']));
        $dia_semana = date('N', strtotime($row['Fecha']));
        $dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
        $nombre_dia = $dias[$dia_semana-1];
        if (!isset($sugerencias_por_fecha[$fecha])) $sugerencias_por_fecha[$fecha] = 0;
        $sugerencias_por_fecha[$fecha]++;
        $sugerencias_por_dia_semana[$nombre_dia]++;
        $total_sugerencias++;
        if ($primer_dia === null || $fecha < $primer_dia) $primer_dia = $fecha;
        if ($ultimo_dia === null || $fecha > $ultimo_dia) $ultimo_dia = $fecha;
    }
}
$dias_totales = $primer_dia && $ultimo_dia ? (strtotime($ultimo_dia) - strtotime($primer_dia)) / 86400 + 1 : 1;
$promedio_diario = $total_sugerencias > 0 ? round($total_sugerencias / $dias_totales, 2) : 0;

// Nuevas consultas para análisis de sugerencias
// 1. Temas más sugeridos (palabras clave)
$sql_temas = "SELECT LOWER(Sugerencia) as texto FROM sugerencia";
$result_temas = $conn->query($sql_temas);
$palabras_clave = [];
$palabras_excluidas = [
    // Originales
    'el', 'la', 'los', 'las', 'un', 'una', 'unos', 'unas', 'y', 'o', 'de', 'del', 'en', 'por', 'para', 'con', 'sin', 'sobre', 'bajo',
    'ante', 'tras', 'durante', 'mediante', 'según', 'contra', 'hacia', 'hasta', 'desde', 'entre', 'que', 'cual', 'quien', 'cuyo',
    'donde', 'cuando', 'como', 'porque', 'pues', 'ya', 'si', 'no', 'también', 'más', 'menos', 'muy', 'poco', 'mucho', 'todo', 'nada',
    'algo', 'alguien', 'nadie', 'cualquier', 'cada', 'varios', 'algunos', 'ninguno', 'ambos', 'tanto', 'tan', 'así', 'tal', 'cual',
    'este', 'ese', 'aquel', 'mi', 'tu', 'su', 'nuestro', 'vuestro', 'sus', 'mis', 'tus', 'nuestros', 'vuestros',

    // Palabras positivas
    'mejor', 'mejores', 'excelente', 'excelentes', 'bueno', 'buena', 'buenos', 'buenas', 'genial', 'geniales', 'útil', 'util',
    'útiles', 'utiles', 'ayuda', 'ayudó', 'ayudo', 'gracias', 'recomiendo', 'recomendado', 'recomendada', 'facil', 'fácil',
    'fáciles', 'faciles', 'sencillo', 'sencilla', 'sencillos', 'sencillas', 'rápido', 'rapido', 'rápida', 'rapida', 'rápidos',
    'rapidos', 'rápidas', 'rapidas', 'eficiente', 'eficientes', 'agradable', 'agradables', 'amable', 'amables', 'bonito', 'bonita',
    'bonitos', 'bonitas', 'perfecto', 'perfecta', 'perfectos', 'perfectas', 'increíble', 'increible', 'increíbles', 'increibles',
    'maravilloso', 'maravillosa', 'maravillosos', 'maravillosas', 'fantástico', 'fantastico', 'fantástica', 'fantastica',
    'fantásticos', 'fantasticos', 'fantásticas', 'fantasticas', 'magnífico', 'magnifico', 'magnífica', 'magnifica', 'magníficos',
    'magnificos', 'magníficas', 'magnificas', 'estupendo', 'estupenda', 'estupendos', 'estupendas', 'brillante', 'brillantes',
    'fabuloso', 'fabulosa', 'fabulosos', 'fabulosas', 'espectacular', 'espectaculares', 'impresionante', 'impresionantes',
    'extraordinario', 'extraordinaria', 'top', 'de lujo', 'de primera', 'premium', 'valorado', 'valioso', 'eficaz', 'eficaces',
    'fiable', 'confiable', 'estable', 'potente', 'me encanta', 'me gustó', 'me gusto', 'me gustaron', 'muy bueno', 'muy buena',
    'muy buenos', 'muy buenas', 'lo recomiendo', 'super recomendable', 'fácil de usar', 'muy sencillo de usar', 'rápido y eficaz',
    'muy eficiente', 'trato agradable', 'muy bonito todo', 'todo perfecto', 'funciona perfecto', 'funciona bien', 'una maravilla',
    'increíble experiencia', 'vale la pena', 'muy satisfecho', 'muy satisfecha', 'superó mis expectativas', 'experiencia positiva',
    'volvería a usarlo', 'lo volveré a usar', 'excelente atención', 'muy buen servicio', 'rápida respuesta', 'calidad superior',
    'muy recomendable', 'me sirvió', 'me fue útil',

    // Palabras negativas
    'malo', 'mala', 'malos', 'malas', 'mal', 'pésimo', 'pesimo', 'pésima', 'pesima', 'pésimos', 'pesimos', 'pésimas', 'pesimas',
    'terrible', 'terribles', 'horrible', 'horribles', 'pobre', 'pobres', 'deficiente', 'deficientes', 'lento', 'lenta', 'lentos',
    'lentas', 'difícil', 'dificil', 'difíciles', 'dificiles', 'complicado', 'complicada', 'complicados', 'complicadas', 'confuso',
    'confusa', 'confusos', 'confusas', 'frustrante', 'frustrantes', 'molesto', 'molesta', 'molestos', 'molestas', 'irritante',
    'irritantes', 'decepcionante', 'decepcionantes', 'inútil', 'inutil', 'inútiles', 'inutiles', 'inadecuado', 'inadecuada',
    'inadecuados', 'inadecuadas', 'insuficiente', 'insuficientes', 'fallo', 'fallos', 'error', 'errores', 'problema', 'problemas',
    'inestable', 'inestabilidad', 'crash', 'crashes', 'bug', 'bugs', 'pérdida', 'pérdida de tiempo', 'pérdida de dinero', 'engaño',
    'engañado', 'engañosa', 'No me gusta', 'no me gustó', 'no me gusto', 'no me gustaron', 'muy malo', 'muy mala', 'muy malos',
    'muy malas', 'no sirve', 'no funcionó', 'no funciona', 'falla mucho', 'muchos errores', 'fallos constantes',
    'problemas frecuentes', 'mala experiencia', 'mala atención', 'mal servicio', 'esperaba más', 'soporte lento',
    'cancelado sin razón', 'tiempo perdido', 'no vale la pena', 'me arrepiento', 'no cumple lo prometido', 'no recomendable',
    'decepcionado', 'decepcionada', 'me decepcionó', 'me decepciono', 'nada útil', 'muy lento', 'demasiado lento',
    'muy complicado', 'difícil de usar', 'confuso de entender', 'pobre calidad','estuvo','gusta', 'funciona mal'
];

if ($result_temas) {
    while ($row = $result_temas->fetch_assoc()) {
        $palabras = str_word_count(strtolower($row['texto']), 1, 'áéíóúüñ');
        foreach ($palabras as $palabra) {
            if (strlen($palabra) > 3 && !in_array($palabra, $palabras_excluidas)) {
                if (!isset($palabras_clave[$palabra])) {
                    $palabras_clave[$palabra] = 0;
                }
                $palabras_clave[$palabra]++;
            }
        }
    }
}
arsort($palabras_clave);
$top_palabras = array_slice($palabras_clave, 0, 5, true);

// 2. Análisis de sentimiento
$sql_sentimiento = "SELECT Sugerencia FROM sugerencia";
$result_sentimiento = $conn->query($sql_sentimiento);
$sentimientos = ['positivo' => 0, 'neutral' => 0, 'negativo' => 0];
$palabras_positivas = [
    // Palabras básicas
    'mejor', 'mejores', 'excelente', 'excelentes', 'bueno', 'buena', 'buenos', 'buenas',
    'genial', 'geniales', 'útil', 'util', 'útiles', 'utiles',
    'ayuda', 'ayudó', 'ayudo', 'gracias', 'recomiendo', 'recomendado', 'recomendada',
    'facil', 'fácil', 'fáciles', 'faciles', 'sencillo', 'sencilla', 'sencillos', 'sencillas',
    'rápido', 'rapido', 'rápida', 'rapida', 'rápidos', 'rapidos', 'rápidas', 'rapidas',
    'eficiente', 'eficientes', 'agradable', 'agradables', 'amable', 'amables',
    'bonito', 'bonita', 'bonitos', 'bonitas',
    'perfecto', 'perfecta', 'perfectos', 'perfectas',
    'increíble', 'increible', 'increíbles', 'increibles',
    'maravilloso', 'maravillosa', 'maravillosos', 'maravillosas',
    'fantástico', 'fantastico', 'fantástica', 'fantastica', 'fantásticos', 'fantasticos', 'fantásticas', 'fantasticas',
    'magnífico', 'magnifico', 'magnífica', 'magnifica', 'magníficos', 'magnificos', 'magníficas', 'magnificas',
    'estupendo', 'estupenda', 'estupendos', 'estupendas',
    'brillante', 'brillantes',
    'fabuloso', 'fabulosa', 'fabulosos', 'fabulosas',
    'espectacular', 'espectaculares',
    'impresionante', 'impresionantes',
    'extraordinario', 'extraordinaria', 'top', 'de lujo', 'de primera', 'premium', 'valorado', 'valioso',
    'eficaz', 'eficaces', 'fiable', 'confiable', 'estable', 'potente',

    // Frases comunes positivas
    'me encanta', 'me gustó', 'me gusto', 'me gustaron',
    'muy bueno', 'muy buena', 'muy buenos', 'muy buenas',
    'lo recomiendo', 'super recomendable', 'fácil de usar', 'muy sencillo de usar',
    'rápido y eficaz', 'muy eficiente', 'trato agradable', 'muy bonito todo',
    'todo perfecto', 'funciona perfecto', 'funciona bien', 'una maravilla',
    'increíble experiencia', 'vale la pena', 'muy satisfecho', 'muy satisfecha',
    'superó mis expectativas', 'experiencia positiva', 'volvería a usarlo',
    'lo volveré a usar', 'excelente atención', 'muy buen servicio',
    'rápida respuesta', 'calidad superior', 'muy recomendable', 'me sirvió', 'me fue útil'
];

$palabras_negativas = [
    // Palabras básicas
    'malo', 'mala', 'malos', 'malas', 'mal',
    'pésimo', 'pesimo', 'pésima', 'pesima', 'pésimos', 'pesimos', 'pésimas', 'pesimas',
    'terrible', 'terribles', 'horrible', 'horribles',
    'pobre', 'pobres',
    'deficiente', 'deficientes',
    'lento', 'lenta', 'lentos', 'lentas',
    'difícil', 'dificil', 'difíciles', 'dificiles',
    'complicado', 'complicada', 'complicados', 'complicadas',
    'confuso', 'confusa', 'confusos', 'confusas',
    'frustrante', 'frustrantes',
    'molesto', 'molesta', 'molestos', 'molestas',
    'irritante', 'irritantes',
    'decepcionante', 'decepcionantes',
    'inútil', 'inutil', 'inútiles', 'inutiles',
    'inadecuado', 'inadecuada', 'inadecuados', 'inadecuadas',
    'insuficiente', 'insuficientes',
    'fallo', 'fallos', 'error', 'errores', 'problema', 'problemas',
    'inestable', 'inestabilidad', 'crash', 'crashes', 'bug', 'bugs',
    'pérdida', 'pérdida de tiempo', 'pérdida de dinero', 'engaño', 'engañado', 'engañosa',

    // Frases comunes negativas
    'No me gusta', 'no me gustó', 'no me gusto', 'no me gustaron',
    'muy malo', 'muy mala', 'muy malos', 'muy malas',
    'no sirve', 'no funcionó', 'no funciona', 'falla mucho',
    'muchos errores', 'fallos constantes', 'problemas frecuentes',
    'mala experiencia', 'mala atención', 'mal servicio', 'esperaba más',
    'soporte lento', 'cancelado sin razón', 'tiempo perdido', 'no vale la pena',
    'me arrepiento', 'no cumple lo prometido', 'no recomendable',
    'decepcionado', 'decepcionada', 'me decepcionó', 'me decepciono',
    'nada útil', 'muy lento', 'demasiado lento', 'muy complicado',
    'difícil de usar', 'confuso de entender', 'pobre calidad', 'funciona mal'
];

if ($result_sentimiento) {
    while ($row = $result_sentimiento->fetch_assoc()) {
        $texto = strtolower($row['Sugerencia']);
        $palabras = str_word_count($texto, 1, 'áéíóúüñ');
        $puntuacion = 0;
        
        foreach ($palabras as $palabra) {
            if (in_array($palabra, $palabras_positivas)) {
                $puntuacion++;
            } elseif (in_array($palabra, $palabras_negativas)) {
                $puntuacion--;
            }
        }
        
        if ($puntuacion > 0) {
            $sentimientos['positivo']++;
        } elseif ($puntuacion < 0) {
            $sentimientos['negativo']++;
        } else {
            $sentimientos['neutral']++;
        }
    }
}

// 3. Análisis de tendencias temporales
$sql_tendencias = "SELECT Fecha, Sugerencia FROM sugerencia ORDER BY Fecha ASC";
$result_tendencias = $conn->query($sql_tendencias);
$tendencias = [];
$meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

// Inicializar tendencias para los últimos 6 meses
$fecha_actual = new DateTime();
for ($i = 5; $i >= 0; $i--) {
    $fecha = clone $fecha_actual;
    $fecha->modify("-$i months");
    $mes = $fecha->format('n') - 1; // 0-11 para el índice del array
    $tendencias[$meses[$mes]] = [
        'total' => 0,
        'positivas' => 0,
        'negativas' => 0
    ];
}

if ($result_tendencias) {
    while ($row = $result_tendencias->fetch_assoc()) {
        $fecha = new DateTime($row['Fecha']);
        $mes = $fecha->format('n') - 1; // 0-11 para el índice del array
        $mes_nombre = $meses[$mes];
        
        // Solo procesar los últimos 6 meses
        $diferencia = $fecha_actual->diff($fecha);
        if ($diferencia->m <= 5) {
            $tendencias[$mes_nombre]['total']++;
            
            // Análisis de sentimiento para esta sugerencia
            $texto = strtolower($row['Sugerencia']);
            $palabras = str_word_count($texto, 1, 'áéíóúüñ');
            $puntuacion = 0;
            
            foreach ($palabras as $palabra) {
                if (in_array($palabra, $palabras_positivas)) {
                    $puntuacion++;
                } elseif (in_array($palabra, $palabras_negativas)) {
                    $puntuacion--;
                }
            }
            
            if ($puntuacion > 0) {
                $tendencias[$mes_nombre]['positivas']++;
            } elseif ($puntuacion < 0) {
                $tendencias[$mes_nombre]['negativas']++;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/panel_admin.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.webp">
    <title>Sugerencias | Saving Secure</title>
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
            <a href="logs.php"><i class="ri-history-line"></i> Logs</a>
            <a href="mostrar_sugerencias.php" class="active"><i class="ri-feedback-line"></i> Sugerencias</a>
            <a href="mostrar_ratings.php"><i class="ri-star-line"></i> Ratings</a>
            <a href="generar_informe_admin.php"><i class="ri-download-line"></i> Descargar Informe</a>
            <a href="login.html"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-panel">
            <div class="section-header">
                <h1>Sugerencias De Los Usuarios</h1>
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="" class="search-box">
                            <input type="text" name="busqueda" placeholder="Buscar por nombre, correo o sugerencia..." value="<?php echo htmlspecialchars($busqueda_input); ?>">
                            <button type="submit"><i class="ri-search-line" style="color: black;"></i></button>
                        </form>
                    </div>
                </div>
            </div>
           

          

            <div class="resumen-tarjetas-centrado">
                <!-- Temas más sugeridos -->
                <div class="tarjeta-resumen-innovadora" style="min-height: 300px;">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-pie"></i></div>
                    <div class="info-tarjeta-innovadora" style="height: 250px;">
                        <span class="titulo-tarjeta-innovadora">Temas más sugeridos</span>
                        <canvas id="graficoPieTemas"></canvas>
                    </div>
                </div>
                <!-- Análisis de sentimiento -->
                <div class="tarjeta-resumen-innovadora" style="min-height: 300px;">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-smile"></i></div>
                    <div class="info-tarjeta-innovadora" style="height: 250px;">
                        <span class="titulo-tarjeta-innovadora">Análisis de sentimiento</span>
                        <canvas id="graficoDonaSentimiento"></canvas>
                    </div>
                </div>
                <!-- Tendencias temporales -->
                <div class="tarjeta-resumen-innovadora" style="min-height: 300px;">
                    <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-line"></i></div>
                    <div class="info-tarjeta-innovadora" style="height: 250px;">
                        <span class="titulo-tarjeta-innovadora">Tendencias de sugerencias</span>
                        <canvas id="graficoTendencias"></canvas>
                    </div>
                </div>
            </div>





           

            <div class="logs-container">
            <div style="margin-bottom: 15px; font-weight: bold; color: #ffd700; font-size: 1.1em; text-align: right;">
                Total de Sugerencias: <?php echo $total_resultados; ?>
            </div>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($sug = $result->fetch_assoc()): ?>
                        <div class="log-entry log-sugerencia">
                            <div class="log-info">
                                <div class="log-descripcion"><b style="color: #ffd700;">Sugerencia:</b> <?php echo htmlspecialchars($sug['Sugerencia']); ?></div>
                                <div class="log-usuario"><b>Nombre:</b> <?php echo htmlspecialchars($sug['Nombre']); ?></div>
                                <div class="log-usuario"><b>Correo:</b> <?php echo htmlspecialchars($sug['Correo']); ?></div>
                                <div class="log-usuario"><b>ID Sugerencia:</b> <?php echo htmlspecialchars($sug['id_sugerencia']); ?></div>
                            </div>
                            <div class="log-fecha">
                                <?php echo date('d/m/Y H:i:s', strtotime($sug['Fecha'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="log-entry log-sugerencia">
                        <div class="log-info">No hay sugerencias registradas.</div>
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
    // Gráfico de pie: Temas más sugeridos
    const ctxPie = document.getElementById('graficoPieTemas').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_keys($top_palabras)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($top_palabras)); ?>,
                backgroundColor: [
                    '#fecd02',
                    '#ffd700',
                    '#ffdf4d',
                    '#ffe680',
                    '#ffecb3'
                ],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#fff',
                        font: {
                            size: 12
                        },
                        padding: 20
                    }
                }
            }
        }
    });

    // Gráfico de dona: Análisis de sentimiento
    const ctxDona = document.getElementById('graficoDonaSentimiento').getContext('2d');
    new Chart(ctxDona, {
        type: 'doughnut',
        data: {
            labels: ['Positivo', 'Neutral', 'Negativo'],
            datasets: [{
                data: <?php echo json_encode(array_values($sentimientos)); ?>,
                backgroundColor: [
                    '#4CAF50',
                    '#FFC107',
                    '#F44336'
                ],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#fff',
                        font: {
                            size: 12
                        },
                        padding: 20
                    }
                }
            }
        }
    });

    // Gráfico de tendencias
    const ctxTendencias = document.getElementById('graficoTendencias').getContext('2d');
    new Chart(ctxTendencias, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($tendencias)); ?>,
            datasets: [
                {
                    label: 'Total Sugerencias',
                    data: <?php echo json_encode(array_column($tendencias, 'total')); ?>,
                    borderColor: '#fecd02',
                    backgroundColor: 'rgba(254,205,2,0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Positivas',
                    data: <?php echo json_encode(array_column($tendencias, 'positivas')); ?>,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76,175,80,0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Negativas',
                    data: <?php echo json_encode(array_column($tendencias, 'negativas')); ?>,
                    borderColor: '#F44336',
                    backgroundColor: 'rgba(244,67,54,0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#fff',
                        font: {
                            size: 10
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#444'
                    },
                    ticks: {
                        color: '#fff',
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#fff',
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>
