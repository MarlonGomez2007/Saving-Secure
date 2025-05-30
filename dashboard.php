<?php
session_start();
include('db.php');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$registros_por_pagina = 5;

// Obtener la página actual desde la URL (por defecto, página 1)
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// Calcular el inicio para la consulta SQL
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Consulta para obtener los gastos con paginación
$queryGastos = "SELECT * FROM gastos WHERE id_usuario = $id_usuario ORDER BY fecha DESC LIMIT $inicio, $registros_por_pagina";
$resultGastos = mysqli_query($conn, $queryGastos);

// Obtener el número total de gastos
$queryTotalGastos = "SELECT COUNT(*) AS total FROM gastos WHERE id_usuario = $id_usuario";
$resultTotalGastos = mysqli_query($conn, $queryTotalGastos);
$totalGastos = mysqli_fetch_assoc($resultTotalGastos)['total'];

// Calcular el número total de páginas
$total_paginas = ceil($totalGastos / $registros_por_pagina);

// Consultar el correo y el nombre del usuario
$query = "SELECT nombre, correo FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $_SESSION['nombre'] = $row['nombre'];
    $_SESSION['email'] = $row['correo'];
}

$stmt->close();

if (isset($_GET['welcome']) && $_GET['welcome'] == 1) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Dashboard</title>
        <link rel='icon' href='assets/img/favicon.ico' type='image/x-icon'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
       <script>
    Swal.fire({
        title: '¡Bienvenido!',
        text: 'Usuario validado. Puedes continuar.',
        icon: 'success',
        confirmButtonText: 'Continuar',
        customClass: {
            confirmButton: 'confirm-btn' 
        }
    }).then(() => {
        window.location.href = 'dashboard.php';
    });
</script>
    </body>
    </html>";
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel='icon' href='assets/img/favicon.ico' type='image/x-icon'>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/dashboard2.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css?family=Quicksand:600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <title>Dashboard | Saving Secure</title>
    <style>
       
       .categoria-container {
          display: flex;
          gap: 10px;
          align-items: center;
      }

      .categoria-container select {
          flex: 1;
      }

      .categoria-btn {
    position: relative;
    top: -5px; /* Ajusta este valor según lo que necesites */
}   

     /* Estilos personalizados para Select2 */
.select2-container--bootstrap-5 {
  width: 100% !important;
  max-width: 100%;
  margin-bottom: 10px;
  box-sizing: border-box;
}

.select2-container--bootstrap-5 .select2-selection {
  background-color: #1a1b26;
  border: 2px solid #fecd02;
  color: white;
  border-radius: 25px;
  min-height: 45px;
  box-sizing: border-box;
  box-shadow: 0 0 10px rgba(254, 205, 2, 0.15);
  overflow: hidden;
  padding: 0 15px;
  display: flex;
  align-items: center;
}

.select2-container--bootstrap-5 .select2-selection:focus-within,
.select2-container--bootstrap-5 .select2-selection:focus,
.select2-container--bootstrap-5 .select2-selection--single:focus {
  border-color: #fecd02;
  background-color: #252636;
  outline: none;
  box-shadow: 0 0 15px rgba(254, 205, 2, 0.7);
}

.select2-container--bootstrap-5 .select2-selection--single {
  height: 45px;
  display: flex;
  align-items: center;
  border-radius: 25px;
  padding: 0 15px;
  box-sizing: border-box;
}

.select2-container--bootstrap-5 .select2-selection__rendered {
  color: white !important;
  font-size: 1em;
  font-weight: 500;
  padding-left: 0;
  letter-spacing: 0.3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.select2-container--bootstrap-5 .select2-dropdown {
  background-color: rgba(26, 27, 38, 0.95);
  border: 2px solid #fecd02;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(10px);
  overflow: auto;
  max-width: 100vw;
  min-width: 0;
}

.select2-container--bootstrap-5 .select2-results__option {
  color: white;
  padding: 12px 20px;
  font-size: 15px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border-bottom: 1px solid rgba(254, 205, 2, 0.1);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.select2-container--bootstrap-5 .select2-results__option:last-child {
  border-bottom: none;
}

.select2-container--bootstrap-5 .select2-results__option--highlighted {
  background-color: #fecd02;
  color: white;
  font-weight: 600;
}

.select2-container--bootstrap-5 .select2-selection__placeholder {
  color: #fff !important;
  font-size: 15px;
}

.select2-container--bootstrap-5 .select2-selection__clear {
  color: #fecd02;
  font-size: 18px;
  margin-right: 5px;
}

.select2-container--bootstrap-5 .select2-selection__arrow {
  color: #fecd02;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.select2-container--bootstrap-5 .select2-selection__arrow b {
  border-color: #fecd02 transparent transparent transparent;
  border-width: 6px 6px 0 6px;
}

.select2-container--bootstrap-5.select2-container--open .select2-selection__arrow b {
  border-color: transparent transparent #fecd02 transparent;
  border-width: 0 6px 6px 6px;
}

.select2-container--bootstrap-5 .select2-search__field {
  background-color: #1a1b26;
  color: white;
  border: 1px solid #fecd02;
  border-radius: 4px;
  padding: 12px 0px;
  margin: 0;
  box-sizing: border-box;
  outline: none;
  width: 100% !important;
  display: block;
}

.select2-container--bootstrap-5 .select2-search__field:focus {
  outline: none !important;
  border-color: #fecd02 !important;
  box-shadow: 0 0 8px 2px #fecd02 !important;
  background-color: rgba(26, 27, 38, 0.95);
}

/* Evitar overflow horizontal global */
html, body {
  overflow-x: hidden !important;
}

.select2-container--bootstrap-5 .select2-selection:focus,
.select2-container--bootstrap-5 .select2-selection:active,
.select2-container--bootstrap-5.select2-container--open .select2-selection {
  outline: none !important;
  border-color: #fecd02 !important;
  box-shadow: 0 0 1px 1px #fecd02 !important;
}

.select2-container--bootstrap-5 .select2-results__option--selected {
  background-color: #fecd02 !important;
  color: #1a1b26 !important;
  font-weight: 600;

  
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


        .btn-ingreso-innovador {
            position: absolute;
            top: 22px;
            right: 22px;
            background: rgba(26,27,38,0.7);
            border: 2.5px solid #fecd02;
            color: #fecd02;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px #fecd0255;
            z-index: 2;
        }
        .btn-ingreso-innovador:hover {
            background: #fecd02;
            color: #1a1b26;
            box-shadow: 0 4px 16px #fecd02cc;
        }
        /* Modal ingreso */
        #modalIngreso {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(31, 32, 41, 0.8);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
        }
        #modalIngreso .modalDialogContent {
            background: #1f2029;
            color: white;
            padding: 30px 30px 20px 30px;
            width: 90vw;
            max-width: 400px;
            border-radius: 18px;
            box-shadow: 0 0 30px rgba(254, 205, 2, 0.2);
            position: relative;
            border: 2px solid #fecd02;
        }
        #modalIngreso .closeButton {
            position: absolute;
            top: 10px;
            right: 18px;
            font-size: 2em;
            color: #fecd02;
            cursor: pointer;
        }
        #modalIngreso input[type="text"], #modalIngreso input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1.5px solid #fecd02;
            border-radius: 7px;
            background: #252636;
            color: #fff;
            font-size: 1em;
        }
        #modalIngreso button[type="submit"] {
            width: 100%;
            background: #fecd02;
            color: #1a1b26;
            border: none;
            border-radius: 7px;
            padding: 12px;
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 10px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        #modalIngreso button[type="submit"]:hover {
            background: #ff2d2d;
            color: #fff;
        }
        @media (max-width: 1200px) {
            .resumen-tarjetas-centrado { max-width: 98vw; }
        }
        @media (max-width: 900px) {
            .resumen-tarjetas-centrado { flex-direction: column; gap: 18px; align-items: center; }
            .tarjeta-resumen-innovadora { min-width: unset; width: 98vw; max-width: 400px; }
        }
    </style>
</head>
<body>
<button id="chatbot-button">
  <i class="fas fa-comments"></i>
</button>

<!-- Contenedor del chatbot -->
<div id="chatbot-container">
  <div id="chatbot-header">
    <div id="chatbot-header-title">
      <img id="chatbot-header-icon" src="assets/img/logo.png" alt="Logo" style="width: 40px; height: auto;">
      <span>Asesor Virtual</span>
    </div>
    <span id="close-chatbot">&times;</span>
  </div>
  <div id="chatbot-content"></div>
  <div id="chatbot-input-container">
    <input type="text" id="chatbot-input" placeholder="Escribe tu mensaje...">
    <button id="chatbot-send">
      <i class="fas fa-paper-plane"></i>
    </button>
  </div>
</div>

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
                <a href="javascript:void(0);" onclick="openModal()">
                    <button class="btn" style="color: #fecd02;">☰</button>
                </a>
            </li>
        </ol>
    </nav>



    
<!-- Modal de configuración que se muestra al hacer clic en el botón de menú -->
            <div id="settingsModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <ul>
                        <li><a href="#" onclick="openProfileModal()">Perfil</a></li>
                        <li><a href="#" onclick="generarInforme()">Informe</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>

        <!-- Aquiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii -->
        <?php
        // CONSULTAS PARA LAS TARJETAS RESUMEN
        $queryIngresos = "SELECT SUM(monto) AS total FROM ingresos WHERE id_usuario = $id_usuario";
        $resultIngresos = mysqli_query($conn, $queryIngresos);
        $totalIngresos = mysqli_fetch_assoc($resultIngresos)['total'] ?? 0;
        $queryEgresos = "SELECT SUM(monto) AS total FROM gastos WHERE id_usuario = $id_usuario";
        $resultEgresos = mysqli_query($conn, $queryEgresos);
        $totalEgresos = mysqli_fetch_assoc($resultEgresos)['total'] ?? 0;
        $balance = $totalIngresos - $totalEgresos;
        $queryTransacciones = "SELECT (SELECT COUNT(*) FROM gastos WHERE id_usuario = $id_usuario) + (SELECT COUNT(*) FROM ingresos WHERE id_usuario = $id_usuario) AS total";
        $resultTransacciones = mysqli_query($conn, $queryTransacciones);
        $totalTransacciones = mysqli_fetch_assoc($resultTransacciones)['total'] ?? 0;
        ?>
        <div class="resumen-tarjetas-centrado">
            <div class="tarjeta-resumen-innovadora">
                <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-line"></i></div>
                <div class="info-tarjeta-innovadora">
                    <span class="titulo-tarjeta-innovadora">Ingresos</span>
                    <span class="valor-tarjeta-innovadora">$<?= number_format($totalIngresos, 0, ',', '.') ?> COP</span>
                </div>
                <a href="ingresos.php"><button class="btn-ingreso-innovador"  title="Agregar Ingreso"><i class="fas fa-plus"></i></button></a>
            </div>
            <div class="tarjeta-resumen-innovadora">
                <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-area"></i></div>
                <div class="info-tarjeta-innovadora">
                    <span class="titulo-tarjeta-innovadora">Egresos</span>
                    <span class="valor-tarjeta-innovadora">$<?= number_format($totalEgresos, 0, ',', '.') ?> COP</span>
                </div>
            </div>
            <div class="tarjeta-resumen-innovadora">
                <div class="icono-tarjeta-amarillo"><i class="fas fa-chart-bar"></i></div>
                <div class="info-tarjeta-innovadora">
                    <span class="titulo-tarjeta-innovadora">Balance</span>
                    <span class="valor-tarjeta-innovadora" style="color: <?= $balance < 0 ? '#ff5252' : '#fecd02' ?>;">$<?= number_format($balance, 0, ',', '.') ?> COP</span>
                </div>
            </div>

        </div>
        <!-- Modal para agregar ingreso -->
        
        </div>
      





    <main class="dashboard-main">
        <div class="dashboard-content">
            <form action="guardar_gasto.php" method="POST" autocomplete="off">
                <div class="tablita">



                   <button id="btnBuscar" class="btnBuscar" type="button">
    🔍 Buscar
</button>




                        <div id="buscarModal" class="modal">
    <div class="modalDialogContent">
        <span class="closeButton" onclick="cerrarModal()"></span>
        <h2>Filtrar Gastos</h2>
        
        <label>Nombre:</label>
        <input type="text" id="filtroNombre">

        <label>Fecha Desde:</label>
        <input type="date" id="filtroFechaDesde">

        <label>Fecha Hasta:</label>
        <input type="date" id="filtroFechaHasta">

        <label>Categoría:</label>
        <select id="filtroCategoria">
            <option value="">Todas</option>
            <option value="Alimentacion">Alimentación</option>
            <option value="Transporte">Transporte</option>
            <option value="Entretenimiento">Entretenimiento</option>
            <option value="Salud">Salud</option>
            <option value="Vivienda">Vivienda</option>
            <option value="Educacion">Educación</option>
        <?php
            // Consultar las categorías personalizadas del usuario
            $queryCategorias = "SELECT DISTINCT nombre_categoria FROM categorias WHERE id_usuario = ? ORDER BY nombre_categoria";
            $stmt = $conn->prepare($queryCategorias);
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $resultCategorias = $stmt->get_result();

            // Mostrar las categorías personalizadas
            while ($row = $resultCategorias->fetch_assoc()) {
                echo '<option value="' . htmlspecialchars($row['nombre_categoria']) . '">' . 
                     htmlspecialchars($row['nombre_categoria']) . '</option>';
            }
            ?>
        </select>

       <label>Monto Mínimo:</label>
<input type="text" id="filtroMontoMin" min="0">

<label>Monto Máximo:</label>
<input type="text" id="filtroMontoMax" min="0">


        <div class="modalActionButtons">




        <button onclick="aplicarFiltros()" type="button" style="
    width: 10em;
    height: 2.0em;
    border: 3px solid #fecd02;
    background-color: #1a1b26;
    color: #fecd02;
    transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
    border-radius: 0.5em;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    position: relative;
    outline: none;
    font-family: Arial, sans-serif;
    line-height: 1.8em;
    padding-top: 2px;
">
    Aplicar Filtros
</button>

<!-- Botón Resetear -->
<button onclick="resetearFiltros()" type="button" style="
    width: 10em;
    height: 2.0em;
    border: 3px solid #fecd02;
    background-color: #1a1b26;
    color: #fecd02;
    transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
    border-radius: 0.5em;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    position: relative;
    outline: none;
    font-family: Arial, sans-serif;
    line-height: 1.8em;
    padding-top: 2px;
">
    Resetear
</button>






                </div>
            </div>
                </div>
<br>
                    <table class="tabla3" id="tabla">
    <thead>
        <tr> 
            <th>Nombre del Gasto</th> 
            <th>Descripción</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Categoría</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
            // Inicializar array para categorías (para gráficos/informes)
            $categorias = [];

            // Usar el resultado de la consulta paginada
            while ($row = mysqli_fetch_assoc($resultGastos)) {
                echo "<tr>";
                echo "<td data-label='Nombre del Gasto'>" . htmlspecialchars($row['nombre']) . "</td>";
                echo "<td data-label='Descripción'>" . htmlspecialchars($row['descripcion']) . "</td>";
                echo "<td data-label='Fecha'>" . htmlspecialchars(date("d-m-Y", strtotime($row['fecha']))) . "</td>";
                echo "<td data-label='Monto'><span class='monto-value'>" . number_format($row['monto'], 0) . " COP</span></td>";
                echo "<td data-label='Categoría'>" . htmlspecialchars($row['categoria']) . "</td>";
                echo "<td data-label='Acciones'>
                        <div class='action-buttons'>
                            <button type='button' class='btn-editar' data-id='" . $row['id'] . "'>Editar</button>
                            <button type='button' class='btn-borrar' onclick='confirmarBorrado(" . $row['id'] . ")'>Borrar</button>
            </div>
                      </td>";
                echo "</tr>";
                
                // Almacenar datos para categorías si es necesario
                if (isset($categorias[$row['categoria']])) {
                    $categorias[$row['categoria']] += $row['monto'];
                } else {
                    $categorias[$row['categoria']] = $row['monto'];
                }
            }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" data-label="Total">Total:</td>
            <td colspan="3" id="totalMonto" data-label="Monto Total">
                <?php
                    $queryTotal = "SELECT SUM(monto) AS total FROM gastos WHERE id_usuario = $id_usuario";
                    $resultTotal = mysqli_query($conn, $queryTotal);
                    $total = mysqli_fetch_assoc($resultTotal);
                    echo '<span class="numeros">' . number_format($total['total'], 0) . ' COP</span>';
                ?>
            </td>
        </tr>
    </tfoot>
</table>
<div class="paginacion">
  <div class="paginacion-container">
    <?php if ($total_paginas > 0): ?>
      <?php if ($pagina_actual > 1) : ?>
        <a href="?pagina=<?= $pagina_actual - 1 ?>" class="btn nav">« Anterior</a>
      <?php endif; ?>
      
      <?php
      // Mostrar un número limitado de páginas con elipsis
      $total_visible = 5;
      $mitad = floor($total_visible / 2);
      
      if ($total_paginas <= $total_visible) {
        // Si hay pocas páginas, mostrar todas
        $inicio = 1;
        $fin = $total_paginas;
      } else {
        // Calcular rango de páginas a mostrar
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
      
      // Primera página siempre visible
      if ($inicio > 1) {
        echo '<a href="?pagina=1" class="btn">1</a>';
        if ($inicio > 2) {
          echo '<span class="separator">...</span>';
        }
      }
      
      // Páginas visibles
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


                    
                    <div class="grafico-container">
                      
                        <div class="grafico-circular">
                            <canvas id="graficoCircular" width="300" height="300"></canvas>
            </div>

                     
                        <div class="grafico-barras">
                            <canvas id="graficoBarras" width="300" height="300"></canvas>
        </div>
            </div>
                    <br><br><br>
        </div>
                <br><br>










                <div class="caja">
                    <form class="form-container">
  <div class="input-group">
    <label for="gasto">Nombre del Gasto</label>
    <input type="text" id="gasto" name="gasto" class="form-style3" placeholder="Ingrese su Gasto" style="color: white;" required>
  </div>
  
  <div class="input-group">
    <label for="descripcion">Descripción</label>
    <input type="text" id="descripcion" name="descripcion" class="form-style3" placeholder="Ingrese la Descripción" style="color: white;">
  </div>
  
  <div class="input-group">
    <label for="monto">Monto</label>
    <input type="text" id="monto" name="montoFormatted" class="form-style3" placeholder="Ingrese el Monto" style="color: white;" required >
    <input type="hidden" id="montoReal" name="monto"> 
</div>
  
    <div class="input-group">
        <label for="categoria">Categoría</label>
        <div class="categoria-container">
            <select id="categoria" name="categoria" class="form-style3 select2" required>
                <option value="">Seleccione una Categoría</option>
                <option value="Alimentacion">Alimentacion</option>
                <option value="Transporte">Transporte</option>
                <option value="Entretenimiento">Entretenimiento</option>
                <option value="Salud">Salud</option>
                <option value="Vivienda">Vivienda</option>
                <option value="Educacion">Educacion</option>
                <?php
                // Consultar las categorías personalizadas del usuario
                $queryCategorias = "SELECT nombre_categoria FROM categorias WHERE id_usuario = ? ORDER BY nombre_categoria";
                $stmt = $conn->prepare($queryCategorias);
                $stmt->bind_param("i", $id_usuario);
                $stmt->execute();
                $resultCategorias = $stmt->get_result();

                // Mostrar las categorías personalizadas
                while ($row = $resultCategorias->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['nombre_categoria']) . '">' . 
                         htmlspecialchars($row['nombre_categoria']) . '</option>';
                }
                ?>
            </select>
            <a href="categorias.php"><button type="button" class="btn-agregar-categoria categoria-btn">Categorías</button></a>
        </div>
    </div>
  
  <button type="submit">Guardar Gasto</button>
</form>
        </div>  
        <br><br><br><br><br><br><br><br><br>











    </main>
    
<script>
function confirmarBorrado(id) {
    console.log("ID de gasto a borrar: " + id);
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Este gasto será borrado permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn-yellow',
            cancelButton: 'btn-cancel' 
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "borrar_gasto.php?id=" + id;
        }
    });
}


</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php
    $id_usuario = $_SESSION['id_usuario'];
    $queryCategorias = "SELECT categoria, SUM(monto) as total FROM gastos WHERE id_usuario = $id_usuario GROUP BY categoria";
    $resultCategorias = mysqli_query($conn, $queryCategorias);

    $categorias = [];
    $totales = [];
    
    while ($row = mysqli_fetch_assoc($resultCategorias)) {
        $categorias[] = $row['categoria'];
        $totales[] = $row['total'];
    }

    if (empty($categorias)) {
        $categorias = ['Sin datos'];
        $totales = [0];
    }

    if (array_sum($totales) === 0) {
        $totales = [1];  
    }
    ?>

    const categorias = <?php echo json_encode($categorias); ?>;
    const totales = <?php echo json_encode($totales); ?>;

    // Enhanced color palette with gradients
    const colores = [
        'rgba(254, 205, 2, 0.9)',   // Primary yellow
        'rgba(254, 205, 2, 0.8)',   // Lighter yellow
        'rgba(254, 205, 2, 0.7)',   // Even lighter yellow
        'rgba(254, 205, 2, 0.6)',   // Softer yellow
        'rgba(254, 205, 2, 0.5)',   // More transparent yellow
        'rgba(255, 213, 79, 0.9)',  // Accent yellow
        'rgba(255, 213, 79, 0.8)',  // Lighter accent
        'rgba(255, 213, 79, 0.7)'   // Softest accent
    ];

    function formatCOP(amount) {
        return new Intl.NumberFormat('es-CO', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount) + ' COP';
    }

    // Enhanced donut chart
    const ctxCircular = document.getElementById('graficoCircular').getContext('2d');
    new Chart(ctxCircular, {
        type: 'doughnut',
        data: {
            labels: categorias,
            datasets: [{
                data: totales,
                backgroundColor: colores.slice(0, categorias.length),
                borderWidth: 3,
                borderColor: '#1a1b26',
                hoverOffset: 8,
                borderRadius: 8,
                hoverBorderWidth: 4,
                hoverBorderColor: '#fecd02'
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
                        label: function (context) {
                            return `${context.label}: ${formatCOP(context.raw)}`;
                        }
                    }
                }
            },
            cutout: '75%',
            rotation: Math.PI * 0.5,
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });

    // Enhanced bar chart
    const ctxBarras = document.getElementById('graficoBarras').getContext('2d');
    new Chart(ctxBarras, {
        type: 'bar',
        data: {
            labels: categorias,
            datasets: [{
                label: 'Gastos por Categoría',
                data: totales,
                backgroundColor: colores.slice(0, categorias.length),
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
                        label: function (context) {
                            return `${context.dataset.label}: ${formatCOP(context.raw)}`;
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
                        callback: function (value) {
                            return formatCOP(value);
                        }
                    },
                    grid: {
                        color: 'rgba(254, 205, 2, 0.1)',
                        borderDash: [8, 8]
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });
});
</script>





        <script>
document.querySelectorAll('.btn-editar').forEach(button => {
    button.addEventListener('click', function() {
        let id = this.getAttribute('data-id');
        window.location.href = 'editar_gasto.php?id=' + id;
    });
});
</script>




<script>
  document.getElementById("monto").addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, ""); // Elimina todo lo que no sea número
      let formattedValue = new Intl.NumberFormat("es-ES").format(value); // Formatea con separadores
      
      e.target.value = formattedValue; // Muestra el valor formateado en el campo visible
      document.getElementById("montoReal").value = value; // Mantiene el valor sin formato para el envío
  });
</script>



<script>
        
        function openModal() {
            document.getElementById('settingsModal').classList.add('show');
        }

      
        function closeModal() {
            const modal = document.getElementById('settingsModal');
            modal.classList.add('hide');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.classList.remove('hide');
            }, 600);
        }

       
        window.onclick = function(event) {
            const modal = document.getElementById('settingsModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
    <script>
function generarInforme() {
    closeModal();

    // Mostrar alerta de carga
    Swal.fire({
        title: "Generando informe...",
        text: "Por favor, espere.",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading(); // Muestra animación de carga
        }
    });

    fetch("generar_informe.php")
        .then(response => response.text())
        .then(data => {
            Swal.fire("Informe Generado", data, "success");
        })
        .catch(error => {
            Swal.fire("Error", "No se pudo generar el informe.", "error");
        });
}

</script>













<script>
// Este código abre el modal cuando se hace clic en el botón de búsqueda
document.getElementById("btnBuscar").addEventListener("click", function() {
    document.getElementById("buscarModal").style.display = "flex"; // Cambié a "flex" para que el modal se muestre
});

// Función para cerrar el modal
function cerrarModal() {
    document.getElementById("buscarModal").style.display = "none";
}


// Aplicar los filtros
function aplicarFiltros() {
    let nombre = document.getElementById("filtroNombre").value.toLowerCase();
    let fechaDesde = document.getElementById("filtroFechaDesde").value;
    let fechaHasta = document.getElementById("filtroFechaHasta").value;
    let categoria = document.getElementById("filtroCategoria").value;
    let montoMin = parseFloat(document.getElementById("filtroMontoMin").value) || 0;
    let montoMax = parseFloat(document.getElementById("filtroMontoMax").value) || Infinity;

    let filas = document.querySelectorAll("#tabla tbody tr");

    filas.forEach(fila => {
        let nombreGasto = fila.cells[0].textContent.toLowerCase();
        let fechaGasto = fila.cells[2].textContent.split("-").reverse().join("-");
        let montoGasto = parseFloat(fila.cells[3].textContent.replace(" COP", "").replace(",", ""));
        let categoriaGasto = fila.cells[4].textContent;

        let mostrar = true;

        if (nombre && !nombreGasto.includes(nombre)) mostrar = false;
        if (fechaDesde && fechaGasto < fechaDesde) mostrar = false;
        if (fechaHasta && fechaGasto > fechaHasta) mostrar = false;
        if (categoria && categoriaGasto !== categoria) mostrar = false;
        if (montoGasto < montoMin || montoGasto > montoMax) mostrar = false;

        fila.style.display = mostrar ? "" : "none";
    });

    // Cerrar el modal después de aplicar los filtros
    cerrarModal();
}

// Resetear los filtros
function resetearFiltros() {
    document.getElementById("filtroNombre").value = "";
    document.getElementById("filtroFechaDesde").value = "";
    document.getElementById("filtroFechaHasta").value = "";
    document.getElementById("filtroCategoria").value = "";
    document.getElementById("filtroMontoMin").value = "";
    document.getElementById("filtroMontoMax").value = "";

    let filas = document.querySelectorAll("#tabla tbody tr");
    filas.forEach(fila => fila.style.display = "");

    // Cerrar el modal después de resetear los filtros
    cerrarModal();
}

// Cerrar el modal cuando el usuario haga clic fuera del contenido del modal
window.addEventListener("click", function(event) {
    let modal = document.getElementById("buscarModal");  // Cambié "modalBuscar" por "buscarModal"
    if (event.target === modal) {
        cerrarModal();
    }
});


</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const montoInput = document.getElementById("monto");
    const montoReal = document.getElementById("montoReal");
    const categoriaInput = document.getElementById("categoria");
    const categoriaReal = document.getElementById("categoriaReal");
    const form = document.querySelector(".form-container");

    // Función para convertir el monto a número limpio
    montoInput.addEventListener("input", function() {
        let valor = montoInput.value.replace(/[^\d]/g, ''); // Elimina caracteres no numéricos
        montoInput.value = new Intl.NumberFormat("es-CO").format(valor); // Formatea con separadores
        montoReal.value = valor; // Guarda el valor limpio en el campo oculto
    });

    // Asignar categoría al campo oculto antes de enviar
    categoriaInput.addEventListener("change", function() {
        categoriaReal.value = categoriaInput.value;
    });

    // Evento antes de enviar el formulario
    form.addEventListener("submit", function(event) {
        if (!montoReal.value) {
            alert("Por favor, ingrese un monto válido.");
            event.preventDefault(); // Evita el envío del formulario si el monto no es válido
        }
    });
});
</script>
<script>
  // Formatear el Monto Mínimo
  document.getElementById("filtroMontoMin").addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, ""); // Elimina todo lo que no sea número
      let formattedValue = new Intl.NumberFormat("es-ES").format(value); // Formatea con separadores
      e.target.value = formattedValue; // Muestra el valor formateado en el campo visible
  });

  // Formatear el Monto Máximo
  document.getElementById("filtroMontoMax").addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, ""); // Elimina todo lo que no sea número
      let formattedValue = new Intl.NumberFormat("es-ES").format(value); // Formatea con separadores
      e.target.value = formattedValue; // Muestra el valor formateado en el campo visible
  });
</script>
 <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inicializar Select2
        $(document).ready(function() {
            $('#categoria').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Seleccione una Categoría',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    }
                }
            });
        });
    </script>

 <script>
document.addEventListener("DOMContentLoaded", function () {
    // Elementos del DOM
    const chatbotButton = document.getElementById("chatbot-button");
    const chatbotContainer = document.getElementById("chatbot-container");
    const closeChatbot = document.getElementById("close-chatbot");
    const chatbotContent = document.getElementById("chatbot-content");
    const chatbotInput = document.getElementById("chatbot-input");
    const chatbotSend = document.getElementById("chatbot-send");
    const chatbotForm = document.getElementById("chatbot-form");
    
    // Log para depuración - verifica que los elementos se encuentren correctamente
    console.log("Elementos críticos del DOM:", {
        chatbotButton: !!chatbotButton,
        chatbotContainer: !!chatbotContainer, 
        chatbotContent: !!chatbotContent,
        chatbotInput: !!chatbotInput,
        chatbotSend: !!chatbotSend
    });
    
    // Variables para el historial de mensajes y estado de la conversación
    let conversationHistory = [];
    let userInfo = {
        hasUsedApp: false,
        financialGoals: [],
        mentionedTopics: new Set(),
        lastInteraction: Date.now()
    };
    
    // Actualiza esta API Key con una válida
    const API_KEY = "Bearer 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";
    
    // Funciones para abrir/cerrar el chatbot
    function toggleChatbot() {
        chatbotContainer.classList.toggle("active");
        if (chatbotContainer.classList.contains("active")) {
            chatbotInput.focus(); // Auto-focus en el input
            
            // Si han pasado más de 30 minutos desde la última interacción, reiniciar conversación
            if (Date.now() - userInfo.lastInteraction > 30 * 60 * 1000) {
                if (conversationHistory.length > 0) {
                    addMessage("Bienvenido de nuevo. ¿En qué puedo ayudarle con sus finanzas hoy?", "bot-message");
                }
            }
        }
        userInfo.lastInteraction = Date.now();
    }
    
    // Usamos optional chaining para evitar errores si el elemento no existe
    chatbotButton?.addEventListener("click", (e) => {
        e.stopPropagation(); // Evita que el evento se propague al documento
        toggleChatbot();
    });
    
    closeChatbot?.addEventListener("click", () => {
        chatbotContainer.classList.remove("active");
    });
    
    // Cerrar el chatbot al hacer clic fuera de él
    document.addEventListener("click", (e) => {
        if (chatbotContainer && chatbotButton && !chatbotContainer.contains(e.target) && !chatbotButton.contains(e.target)) {
            chatbotContainer.classList.remove("active");
        }
    });
    
    // Expresiones colombianas y saludos locales
    const colombianExpressions = [
        "¡Hola!", "¡Buen día!", "¡Saludos!", "¡Bienvenido!", "¡Hola!", 
        "¿Cómo está?", "¡Buenos días!", "¡Buenas tardes!"
    ];
    
    // Lista de saludos comunes
    const commonGreetings = [
        "hola", "buenos días", "buenas tardes", "buenas noches", 
        "saludos", "hey", "qué tal", "cómo estás", "hi", "hello",
        "buenas"
    ];
    
    // Respuestas a saludos formales
    const greetingResponses = [
        "¡Bienvenido! Soy Saving Secure, su asesor financiero personal. ¿En qué puedo ayudarle hoy con sus finanzas?", 
        "¡Saludos! Soy Saving Secure, estoy aquí para ayudarle a gestionar mejor su dinero.",
        "¡Hola! Soy Saving Secure, listo para asistirle con sus finanzas personales.",
        "¡Buen día! Soy Saving Secure, su asesor financiero virtual. ¿Qué consultas tiene sobre cómo administrar su dinero?",
        "¡Saludos! Soy Saving Secure, es un placer atenderle. ¿Necesita ayuda con ahorros, inversiones o presupuestos?"
    ];
    
    // Frases para incentivar el uso de la aplicación
    const appPromotionPhrases = [
        "¿Ya probó nuestra calculadora de gastos? Le ayuda a visualizar sus gastos mensuales.",
        "Nuestra aplicación le permite hacer seguimiento de sus gastos diarios. Es como tener un contador personal.",
        "¿Sabía que puede establecer metas de ahorro en nuestra plataforma? Muchos usuarios ya han ahorrado para su vivienda o vehículo.",
        "Con nuestra herramienta de presupuesto puede organizar sus finanzas en cuestión de minutos. Le invitamos a probarla.",
        "Los usuarios que utilizan nuestra calculadora de gastos logran ahorrar hasta un 20% más cada mes. ¿Le gustaría intentarlo?",
        "¿Le cuesta llevar el control de sus gastos? Nuestra aplicación le muestra gráficos claros para entender sus patrones de consumo.",
        "Muchos usuarios ya están utilizando nuestra plataforma para reducir sus deudas más rápido. Usted también puede beneficiarse."
    ];
    
    // Consejos financieros con contexto colombiano
    const financialTips = [
        "En Colombia, una buena estrategia es tener un fondo de emergencia que cubra 3-6 meses de gastos. ¡Nunca se sabe cuándo lo necesitarás!",
        "¿Conoces el sistema de ahorro 50/30/20? 50% para necesidades básicas, 30% para gustos y 20% para ahorro. Funciona muy bien para organizar el sueldo.",
        "Antes de pedir un crédito, compara las tasas de interés entre diferentes bancos. En Colombia pueden variar bastante.",
        "Aprovecha las promociones sin cuota de manejo en tarjetas de crédito, pero úsalas con responsabilidad.",
        "Para inversiones seguras en Colombia, considera los CDTs o fondos de inversión de bajo riesgo si estás comenzando.",
        "¿Sabías que puedes reducir tu declaración de renta guardando facturas de gastos médicos y educación?",
        "Las billeteras digitales como Nequi o Daviplata te ayudan a controlar mejor tus gastos diarios sin comisiones."
    ];
    
    // Palabras clave financieras en contexto colombiano
    const colombianFinancialKeywords = [
        "plata", "lucas", "billete", "luca", "pesos", "ahorrar", "cuotas", 
        "crédito", "préstamo", "gota a gota", "banco", "nómina", "prima", 
        "cesantías", "pensión", "subsidio", "impuestos", "DIAN", "factura", 
        "arriendo", "hipoteca", "fiado", "mercado", "recibo", "servicios"
    ];
    
    // Información detallada sobre la aplicación
    const appInfo = {
        features: [
            "Seguimiento detallado de gastos diarios, semanales y mensuales",
            "Categorización automática de gastos (mercado, servicios, transporte, etc.)",
            "Presupuestos personalizados con alertas cuando estás por exceder límites",
            "Metas de ahorro con seguimiento visual de progreso",
            "Recordatorios de pagos para evitar intereses por mora",
            "Gráficos interactivos para visualizar en qué gastas tu dinero",
            "Consejos personalizados basados en tus hábitos de gasto",
            "Compatibilidad con Nequi, Daviplata y principales bancos colombianos",
            "Exportación de informes para declaración de renta",
            "Modo familiar para gestionar finanzas compartidas"
        ],
        benefits: [
            "Ahorra hasta un 25% más identificando gastos hormiga",
            "Reduce estrés financiero al tener control total de tu dinero",
            "Logra tus metas financieras más rápido con planes estructurados",
            "Evita sobregiros bancarios y pagos de intereses innecesarios",
            "Mejora tus hábitos financieros con retroalimentación personalizada",
            "Toma decisiones informadas sobre inversiones y grandes compras"
        ],
        howToUse: [
            "Regístrate gratis en nuestra página web",
            "Conecta tus cuentas bancarias o ingresa tus gastos manualmente",
            "Establece categorías personalizadas para tus gastos",
            "Crea presupuestos realistas basados en tus ingresos",
            "Revisa periódicamente tus informes para identificar áreas de mejora",
            "Utiliza las alertas y recordatorios para mantener el control"
        ],
        commonQuestions: {
            "cómo usar": "Para usar nuestra calculadora de gastos, simplemente regístrate, ingresa tus ingresos y gastos, y la aplicación automáticamente te mostrará gráficos y análisis. Puedes categorizar tus gastos y establecer presupuestos personalizados.",
            "costo": "Ofrecemos un plan básico totalmente gratuito y planes premium desde $9,900 COP mensuales con funciones avanzadas como sincronización bancaria, asesoría personalizada y herramientas de inversión.",
            "seguridad": "Tu información financiera está protegida con encriptación de nivel bancario. No almacenamos contraseñas de tus cuentas bancarias y cumplimos con todas las normativas de protección de datos de Colombia.",
            "diferencia": "A diferencia de otras apps, nos especializamos en el contexto financiero colombiano, incluyendo conceptos como prima, cesantías y declaración de renta. Además, ofrecemos recomendaciones personalizadas basadas en tu comportamiento financiero.",
            "recuperar": "Si olvidaste tu contraseña, puedes restablecerla fácilmente desde la página de inicio de sesión utilizando el correo electrónico con el que te registraste.",
            "exportar": "Puedes exportar todos tus informes en formatos PDF o Excel, ideales para compartir con tu contador o para tu declaración de renta."
        },
        successStories: [
            "María de Medellín logró ahorrar para la cuota inicial de su apartamento en solo 18 meses usando nuestra aplicación para controlar sus gastos hormiga.",
            "Carlos de Bogotá redujo sus deudas de tarjetas de crédito en un 70% en 8 meses siguiendo nuestro plan de pagos optimizado.",
            "La familia Rodríguez de Cali ahorró suficiente para sus vacaciones soñadas estableciendo presupuestos claros para cada categoría de gasto.",
            "Camila, emprendedora de Barranquilla, aumentó el flujo de caja de su negocio en un 30% al identificar gastos innecesarios mediante nuestros informes detallados."
        ],
        tips: [
            "Registra hasta los gastos más pequeños, como el café diario. Estos 'gastos hormiga' pueden sumar hasta un 20% de tus ingresos mensuales.",
            "Revisa tus suscripciones periódicamente. Muchos colombianos pagan por servicios que ya no usan.",
            "Usa la regla 72 horas: espera 72 horas antes de hacer una compra no esencial para evitar compras impulsivas.",
            "Configura transferencias automáticas a tu cuenta de ahorros el día que recibes tu nómina.",
            "Utiliza nuestra función de 'retos de ahorro' para motivarte, como el reto del 52 semanas o el ahorro del 10%."
        ]
    };
    
    // Función para enviar mensajes con prevención de errores
    function handleSubmit(e) {
        if (e) e.preventDefault();
        
        const userMessage = chatbotInput.value.trim();
        if (!userMessage) return;
        
        // Desactivar input mientras se procesa
        chatbotInput.value = "";
        chatbotInput.disabled = true;
        chatbotSend.disabled = true;
        
        // Mostrar mensaje del usuario
        addMessage(userMessage, "user-message");
        
        // Actualizar tiempo de última interacción
        userInfo.lastInteraction = Date.now();
        
        // Verificar si es solo un saludo
        if (isJustGreeting(userMessage)) {
            // Responder con un saludo cordial solo si es la primera interacción
            if (conversationHistory.length === 0) {
                const randomGreeting = greetingResponses[Math.floor(Math.random() * greetingResponses.length)];
                setTimeout(() => {
                    addMessage(randomGreeting, "bot-message");
                    conversationHistory.push({ role: "assistant", content: randomGreeting });
                    chatbotInput.disabled = false;
                    chatbotSend.disabled = false;
                    chatbotInput.focus();
                }, 500);
            } else {
                // Si no es la primera interacción, no saludar de nuevo
                setTimeout(() => {
                    addMessage("¿En qué más te puedo colaborar con tus finanzas?", "bot-message");
                    chatbotInput.disabled = false;
                    chatbotSend.disabled = false;
                    chatbotInput.focus();
                }, 500);
            }
            return;
        }
        
        // Verificar si la consulta es de ámbito no financiero (solo si no es un saludo)
        if (!isFinancialQuery(userMessage) && !isJustGreeting(userMessage)) {
            addMessage("Entiendo su consulta, pero como asesor especializado exclusivamente en finanzas personales, mi enfoque se limita a temas financieros y nuestra calculadora de gastos. No puedo ofrecer información sobre programación, desarrollo, tecnología u otros temas no relacionados con finanzas. ¿Podría reformular su pregunta hacia algún aspecto financiero? Por ejemplo: ahorros, inversiones, presupuestos, deudas, metas financieras o cómo usar nuestra calculadora financiera.", "bot-message");
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
            return;
        }
        
        // Actualizar historial de conversación
        conversationHistory.push({ role: "user", content: userMessage });
        
        // Analizar mensaje para detectar temas financieros mencionados
        detectFinancialTopics(userMessage);
        
        // Mostrar indicador de escritura
        const typingIndicator = showTypingIndicator();
        
        // Llamada a la API con manejo de errores mejorado
        callChatAPI(userMessage, typingIndicator);
    }
    
    // Función para detectar temas financieros mencionados
    function detectFinancialTopics(message) {
        const topics = {
            ahorro: ["ahorro", "ahorrar", "guardar plata", "guardar dinero", "alcancia"],
            inversion: ["inversion", "invertir", "acciones", "bolsa", "CDT", "fondo"],
            deuda: ["deuda", "credito", "prestamo", "hipoteca", "gota a gota", "tarjeta"],
            presupuesto: ["presupuesto", "gastos", "ingresos", "sueldo", "nomina", "quincena"],
            impuestos: ["impuestos", "DIAN", "declaración", "renta", "IVA", "retención"],
            app: ["app", "aplicación", "calculadora", "herramienta", "como usar", "como funciona", 
                  "registrarme", "cuenta", "iniciar sesión", "características", "funciones", 
                  "gráficos", "reportes", "categorías", "exportar", "dispositivo", "premium", 
                  "versión", "Saving Secure"]
        };
        
        const messageLower = message.toLowerCase();
        
        for (const [topic, keywords] of Object.entries(topics)) {
            if (keywords.some(keyword => messageLower.includes(keyword))) {
                userInfo.mentionedTopics.add(topic);
            }
        }
    }
    
    // Función para verificar si el mensaje es solo un saludo
    function isJustGreeting(message) {
        message = message.toLowerCase().trim();
        // Verificar si el mensaje es muy corto y contiene un saludo común
        return message.length < 20 && commonGreetings.some(greeting => 
            message === greeting || message.startsWith(greeting + " ") || message.endsWith(" " + greeting)
        );
    }
    
    // Función para verificar si la consulta es sobre temas financieros
    function isFinancialQuery(query) {
        const financialKeywords = [
            "dinero", "plata", "lucas", "finanzas", "inversión", "invertir", "ahorro", "ahorrar",
            "presupuesto", "credito", "prestamo", "hipoteca", "interés", "intereses",
            "deuda", "deudas", "banco", "cuenta", "tarjeta", "impuestos", "impuesto",
            "seguro", "seguros", "jubilación", "pensión", "bolsa", "acciones", "bonos",
            "fondos", "dividendos", "capital", "activos", "pasivos", "gastos", "ingresos",
            "nómina", "sueldo", "prima", "cesantías", "arriendo", "servicios", "facturas",
            "mercado", "compras", "cuotas", "fiado", "gota a gota", "DIAN", "declaración",
            "pesos", "dólares", "euros", "moneda", "economía", "finanza", "financiero",
            "financiera", "precio", "descuento", "oferta", "compra", "venta", "balance",
            "contabilidad", "contable", "contador", "criptomoneda", "bitcoin", "ethereum",
            "billetera", "presupuestar", "meta", "objetivo financiero", "plazo fijo"
        ];

        // Palabras clave relacionadas con la aplicación
        const appKeywords = [
            "app", "aplicacion", "calculadora", "herramienta", "plataforma", "registrar", 
            "categoria", "funciona", "usar", "utilizar", "cómo", "registrarme", "cuenta", 
            "login", "iniciar sesión", "contraseña", "usuario", "premium", "gratis", "costo", 
            "precio", "plan", "versión", "actualizar", "feature", "función", "característica", 
            "sincronizar", "exportar", "datos", "gráfico", "reporte", "analisis", "tutorial", 
            "guia", "ayuda", "soporte", "error", "problema", "telefono", "celular", "computador", 
            "tablet", "dispositivo", "Saving Secure", "saving", "secure", "calculadora de gastos",
            "chat", "asistente", "asesor", "virtual", "financiero", "consejos", "recomendaciones"
        ];

        // Lista de temas específicamente prohibidos
        const prohibitedTopics = [
            // Programación
            "programación", "programar", "código", "desarrollar", "software", "aplicaciones", 
            "desarrollo web", "desarrollo móvil", "desarrollador", "framework", "javascript", 
            "python", "java", "html", "css", "php", "mysql", "sql", "base de datos", "backend", 
            "frontend", "fullstack", "api", "crud", "servidor", "cliente", "interfaz", "web", 
            "app móvil", "android", "ios", "react", "angular", "vue", "node", "express", "django",
            "laravel", "spring", "bootstrap", "jquery", "api rest", "json", "xml", "http", 
            "hosting", "dominio", "github", "git", "repositorio", "codificar", "programador",
            
            // Otros temas claramente no financieros
            "política", "guerra", "fútbol", "medicina", "salud", "enfermedad", "juego", 
            "receta", "cocinar", "entretenimiento", "película", "serie", "música", "deporte", 
            "videojuego", "religión", "viaje", "turismo", "vacaciones", "horóscopo", "astrología",
            "arte", "literatura", "ciencia", "biología", "química", "física", "historia",
            "geografía", "idiomas", "lenguajes", "educación", "universidad", "escuela", "colegio",
            "moda", "belleza", "decoración", "hogar", "mascotas", "animales", "plantas", "jardinería",
            "bricolaje", "reparaciones", "mecánica", "vehículos", "autos", "motos", "bicicletas",
            "matrimonio", "divorcio", "relaciones", "amor", "amistad", "psicología", "emociones",
            "juicio", "abogado", "legal", "ley", "demanda", "juicios", "proceso", "criminología"
        ];

        query = query.toLowerCase();

        // Verificar primero si la consulta contiene temas prohibidos
        if (prohibitedTopics.some(term => query.includes(term))) {
            return false;
        }

        // Si ya hay una conversación activa, ser más estricto con lo que se considera válido
        if (conversationHistory.length > 0) {
            // Si la consulta es muy corta, verificar que tenga al menos un término financiero
            if (query.length < 50) {
                return financialKeywords.some(keyword => query.includes(keyword)) || 
                       appKeywords.some(keyword => query.includes(keyword));
            }
            
            // Para consultas más largas, verificar que tenga una mayor presencia de términos financieros
            const hasFinancialTerms = financialKeywords.some(keyword => query.includes(keyword));
            const hasAppTerms = appKeywords.some(keyword => query.includes(keyword));
            
            return hasFinancialTerms || hasAppTerms;
        }
        
        // Para la primera interacción, verificamos si contiene palabras clave financieras o de la aplicación
        return financialKeywords.some(keyword => query.includes(keyword)) || 
               appKeywords.some(keyword => query.includes(keyword));
    }
    
    // Llamada a la API separada para mejor mantenimiento
    function callChatAPI(userMessage, typingIndicator) {
        // Determinar si debemos promover la app basado en la conversación
        const shouldPromoteApp = conversationHistory.length >= 2 && !userInfo.hasUsedApp && Math.random() > 0.5;
        
        // Imprimir información de diagnóstico
        console.log("Enviando consulta a OpenRouter API...");
        console.log("Modelo: deepseek/deepseek-chat");
        console.log("URL origen:", window.location.origin);
        console.log("Longitud del historial:", conversationHistory.length);
        
        // Construir un prompt personalizado basado en la conversación
        const systemPrompt = `Eres Saving Secure, un asesor financiero virtual profesional, experto EXCLUSIVAMENTE en finanzas personales, formal y respetuoso. Tu objetivo principal es ayudar a los usuarios a gestionar sus finanzas personales, ahorrar dinero y tomar decisiones financieras informadas. Además, debes motivar a los usuarios a utilizar nuestra calculadora de gastos para maximizar su experiencia.

IMPORTANTE: NO PUEDES responder absolutamente ninguna consulta relacionada con programación, desarrollo de software, tecnología, salud, política, deportes, entretenimiento u otros temas no financieros. Si recibes preguntas sobre estos temas, debes rechazarlas cortésmente y explicar que solo puedes hablar de finanzas personales.

SOBRE NUESTRA APLICACIÓN DE CALCULADORA DE GASTOS:
- Es una plataforma web completa para gestión financiera personal
- Permite categorizar gastos, establecer presupuestos, crear metas de ahorro y visualizar el progreso
- Ofrece gráficos detallados de sus patrones de gasto para identificar áreas de mejora
- Compatible con smartphones, tablets y computadores
- Integración con principales bancos y billeteras digitales
- Incluye funciones especiales para gestionar prima, cesantías y preparar información para la declaración de renta
- Envía alertas personalizadas cuando está por exceder su presupuesto en alguna categoría
- Disponible versión gratuita y versiones premium desde $9,900 COP/mes

PUNTOS CLAVE SOBRE FINANZAS PERSONALES:
- La mayoría de personas gasta entre 10-15% de su ingreso en "gastos hormiga" sin darse cuenta
- La regla 50/30/20 funciona muy bien (50% necesidades, 30% deseos, 20% ahorro)
- Los préstamos con tasas de interés elevadas deben evitarse
- El fondo de emergencia ideal debe cubrir entre 3-6 meses de gastos básicos
- Las inversiones en CDTs, fondos de inversión colectiva y TES son opciones conservadoras para principiantes
- El mercado accionario ofrece oportunidades pero requiere mayor educación financiera

ESTRATEGIAS DE AHORRO (USA ESTAS COMO INSPIRACIÓN PERO NUNCA REPITAS EXACTAMENTE LA MISMA):
- Método de ahorro 52 semanas: empiece guardando $5.000 la primera semana, aumente $5.000 cada semana
- Ahorro automático: configure transferencias automáticas a cuenta de ahorros apenas reciba su sueldo
- Desafío "Sin compras": evite gastos no esenciales durante un mes y destine ese dinero al ahorro
- Regla 24 horas: espere un día completo antes de realizar cualquier compra no planificada
- Método japonés Kakebo: registre y clasifique todos sus gastos de forma manual para mayor consciencia
- Técnica del frasco: coloque diariamente una cantidad fija en un frasco para gastos inesperados
- Ahorro por metas específicas: divida sus ahorros en "sobres virtuales" según objetivos concretos
- Método 60-20-20: 60% gastos fijos, 20% gastos variables, 20% ahorro e inversión

COMPORTAMIENTO CONVERSACIONAL:
- RECHAZA COMPLETAMENTE cualquier pregunta no relacionada con finanzas o economía personal.
- Si te preguntan sobre programación, tecnología, desarrollo de software, u otros temas no financieros, explica amablemente que no puedes responder por ser un asesor exclusivamente financiero.
- Mantén el hilo de la conversación, respondiendo directamente a las preguntas del usuario sobre finanzas.
- Interpreta las preguntas cortas o ambiguas en el contexto de la conversación previa, siempre que sean de ámbito financiero.
- NO RESPONDAS JAMÁS a consultas de programación, políticas, deportes, salud u otros temas no financieros.

IMPORTANTE:
1. **USA UN TONO FORMAL**: Utiliza "usted" en lugar de "tú", evita expresiones coloquiales o regionalismos.
2. **SÉ CORDIAL Y PROFESIONAL**: Responde siempre de manera educada, respetuosa y profesional.
3. **INCITA A USAR LA CALCULADORA DE GASTOS**: Menciona frecuentemente nuestra herramienta.
4. **MANTÉN LA FLUIDEZ CONVERSACIONAL**: Responde a preguntas de seguimiento sin romper el flujo.
5. **NUNCA REPITAS RESPUESTAS**: Cada vez que respondas, hazlo de forma única y diferente, aunque sea sobre el mismo tema.
6. **PERSONALIZA TUS RESPUESTAS**: Adapta tus consejos al perfil financiero que detectes del usuario.
7. **VARÍA TUS RESPUESTAS**: No uses siempre las mismas frases o estructuras.
8. **SÉ ESPECÍFICO**: Ofrece ejemplos concretos y números realistas cuando hables de finanzas.
9. **COMPARTE HISTORIAS DE ÉXITO**: Menciona casos de usuarios que han mejorado sus finanzas.
10. **VARÍA EJEMPLOS Y DATOS**: No utilices siempre los mismos porcentajes o ejemplos.

${shouldPromoteApp ? `IMPORTANTE: Inclúyelo sutilmente, pero NO repitas exactamente esta frase: "Nuestra aplicación le permite visualizar sus gastos"` : ''}

${userInfo.mentionedTopics.size > 0 ? `El usuario ha mostrado interés en: ${Array.from(userInfo.mentionedTopics).join(', ')}. Enfócate en estos temas.` : ''}

HISTORIAS DE ÉXITO PARA INSPIRARTE (NUNCA REPITAS LA MISMA):
- María logró ahorrar para la cuota inicial de su apartamento identificando sus gastos hormiga.
- Carlos redujo sus deudas de tarjetas siguiendo un plan organizado de pagos.
- La familia Rodríguez ahorró para sus vacaciones estableciendo presupuestos claros.
- Camila aumentó el flujo de caja de su negocio al identificar gastos innecesarios.
- Juan y Ana pudieron comprar su primer carro ahorrando el 15% de sus ingresos mensuales.
- Pedro pagó su maestría evitando gastos impulsivos por dos años.
- Sofía ahorró para su boda utilizando cuentas específicas para cada categoría de gastos.
- Miguel pudo jubilarse anticipadamente invirtiendo consistentemente durante 20 años.

TUS RESPUESTAS DEBEN SER:
- Formales y respetuosas, usando "usted" en lugar de "tú"
- Precisas y basadas en hechos financieros relevantes
- Claras y fáciles de entender
- Educativas y motivadoras
- Breves y concretas (máximo 4-5 líneas)
- Conversacionales y naturales
- SIEMPRE DIFERENTES Y ÚNICAS
- Variadas en estructura y contenido para mantener el interés

Responde siempre en español usando un tono formal y profesional. Si te preguntan sobre cómo usar nuestra aplicación, explica las funciones principales de manera sencilla e incentiva su uso. NUNCA REPITAS RESPUESTAS ANTERIORES.`;
        
        // Preparar mensajes para API incluyendo historial
        const apiMessages = [
            { role: "system", content: systemPrompt },
            ...conversationHistory
                .filter(message => message.role !== "assistant" || !isJustGreeting(message.content)) // Excluir saludos del bot
                .slice(-5) // Limitamos a los últimos 5 mensajes para contexto
        ];
        
        try {
            fetch("https://openrouter.ai/api/v1/chat/completions", {
                method: "POST",
                headers: {
                    "Authorization": API_KEY,
                    "Content-Type": "application/json",
                    "HTTP-Referer": window.location.origin,
                    "X-Title": "Saving Secure"
                },
                body: JSON.stringify({
                    model: "deepseek/deepseek-chat",
                    messages: apiMessages,
                    max_tokens: 500,
                    temperature: 0.7
                })
            })
            .then(response => {
                if (!response.ok) {
                    console.error(`Error de API: ${response.status} - ${response.statusText}`);
                    console.error("Detalles de la solicitud:", {
                        url: "https://openrouter.ai/api/v1/chat/completions",
                        model: "deepseek/deepseek-chat",
                        origin: window.location.origin
                    });
                    throw new Error(`Error ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                // Imprimir respuesta para diagnóstico
                console.log("Respuesta de API recibida:", data);
                
                // Eliminar indicador de escritura
                removeTypingIndicator(typingIndicator);
                
                // Verificar si la respuesta tiene el formato esperado
                if (!data.choices || !data.choices[0] || !data.choices[0].message) {
                    console.error("Formato de respuesta inesperado:", data);
                    throw new Error("Formato de respuesta inesperado");
                }
                
                // Procesar respuesta
                let botResponse = data.choices[0].message.content;
                
                // Si la respuesta es muy larga, acortarla
                if (botResponse.length > 800) {
                    botResponse = botResponse.substring(0, 750) + "...";
                }
                
                // Agregar respuesta al chat y al historial
                addMessage(botResponse, "bot-message");
                conversationHistory.push({ role: "assistant", content: botResponse });
            })
            .catch(error => {
                console.error("Error detallado en la llamada a la API:", error);
                removeTypingIndicator(typingIndicator);
                
                // Usar API de respaldo en caso de error con la primera
                fallbackAPICall(userMessage);
            })
            .finally(() => {
                // Reactivar controles
                chatbotInput.disabled = false;
                chatbotSend.disabled = false;
                chatbotInput.focus();
            });
        } catch (error) {
            console.error("Error crítico al llamar a la API:", error);
            removeTypingIndicator(typingIndicator);
            
            // Usar API de respaldo en caso de error
            fallbackAPICall(userMessage);
            
            // Reactivar controles
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
        }
    }
    
    // Función para llamar a una API alternativa en caso de fallo
    function fallbackAPICall(userMessage) {
        console.log("Utilizando API de respaldo...");
        
        // Si el userMessage contiene palabras clave específicas, dar respuestas directas sin API
        const lowerMessage = userMessage.toLowerCase();
        
        // Lista de temas prohibidos (programación y no financieros)
        const prohibitedKeywords = [
            "programación", "código", "software", "desarrollo web", "php", "java", 
            "python", "javascript", "html", "crud", "base de datos", "sql", "mysql",
            "api", "programar", "app", "desarrollo", "frontend", "backend", "fullstack",
            "algoritmo"
        ];
        
        // Verificar si el mensaje contiene temas prohibidos
        if (prohibitedKeywords.some(keyword => lowerMessage.includes(keyword))) {
            const respuesta = "Como asesor financiero especializado, no puedo responder consultas sobre programación o desarrollo de software. Mi enfoque está exclusivamente en temas financieros como ahorros, inversiones, presupuestos, deudas y cómo utilizar nuestra calculadora financiera. ¿En qué aspecto de sus finanzas personales puedo ayudarle?";
            addMessage(respuesta, "bot-message");
            conversationHistory.push({ role: "assistant", content: respuesta });
            
            // Reactivar controles
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
            return;
        }
        
        // Palabras clave comunes y sus respuestas
        if (lowerMessage.includes("problemas técnicos") || 
            lowerMessage.includes("no funciona") || 
            lowerMessage.includes("error")) {
            const respuesta = "Disculpe las molestias. Estamos experimentando algunos problemas técnicos temporales. Nuestro equipo está trabajando para resolverlos lo antes posible. Mientras tanto, puedo responder a consultas generales sobre finanzas personales o sobre cómo utilizar nuestra calculadora cuando el servicio se restablezca completamente.";
            addMessage(respuesta, "bot-message");
            conversationHistory.push({ role: "assistant", content: respuesta });
            
            // Reactivar controles
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
            return;
        }
        
        // Si se menciona la calculadora o la aplicación
        if (lowerMessage.includes("calculadora") || 
            lowerMessage.includes("aplicación") || 
            lowerMessage.includes("app")) {
            const respuesta = "Nuestra calculadora de gastos le permite visualizar y gestionar sus finanzas personales de manera efectiva. Puede categorizar sus gastos, establecer presupuestos personalizados y recibir recomendaciones para mejorar sus hábitos financieros. Le invito a probarla cuando nuestro servicio se restablezca completamente.";
            addMessage(respuesta, "bot-message");
            conversationHistory.push({ role: "assistant", content: respuesta });
            
            // Reactivar controles
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
            return;
        }
        
        // Si contiene palabras sobre ahorro
        if (lowerMessage.includes("ahorro") || 
            lowerMessage.includes("ahorrar") || 
            lowerMessage.includes("guardar dinero")) {
            const respuesta = "Para mejorar sus ahorros, le recomiendo seguir la regla 50/30/20: destine 50% de sus ingresos a necesidades básicas, 30% a deseos personales y 20% a ahorro e inversión. Nuestra calculadora de gastos puede ayudarle a implementar este sistema de manera efectiva.";
            addMessage(respuesta, "bot-message");
            conversationHistory.push({ role: "assistant", content: respuesta });
            
            // Reactivar controles
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
            return;
        }
        
        // API de respaldo - usando una configuración alternativa o un servicio diferente
        const fallbackPrompt = `Eres un asesor financiero virtual. El usuario te ha enviado este mensaje: "${userMessage}". 
        Proporciona una respuesta breve, útil y única sobre finanzas personales o sobre cómo nuestra calculadora de gastos 
        podría ayudarle. Usa un tono formal y profesional. Mantén la respuesta breve (máximo 3-4 líneas).`;
        
        // Intenta con configuración alternativa de OpenRouter (modelo diferente)
        fetch("https://openrouter.ai/api/v1/chat/completions", {
            method: "POST",
            headers: {
                "Authorization": API_KEY,
                "Content-Type": "application/json",
                "HTTP-Referer": window.location.origin,
                "X-Title": "Saving Secure"
            },
            body: JSON.stringify({
                model: "deepseek/deepseek-chat",
                messages: [{ role: "user", content: fallbackPrompt }],
                max_tokens: 250,
                temperature: 0.8
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.choices && data.choices[0] && data.choices[0].message) {
                const fallbackResponse = data.choices[0].message.content;
                addMessage(fallbackResponse, "bot-message");
                conversationHistory.push({ role: "assistant", content: fallbackResponse });
            } else {
                // Si falla también la API de respaldo, usar una respuesta genérica pero aleatoria
                useGenericResponse();
            }
        })
        .catch(error => {
            console.error("Error también en API de respaldo:", error);
            useGenericResponse();
        });
    }
    
    // Función para generar una respuesta genérica aleatoria en caso de fallo de todas las APIs
    function useGenericResponse() {
        const genericResponses = [
            "Gracias por su consulta financiera. En este momento estamos experimentando dificultades técnicas. ¿Le parece si reformula su pregunta o intenta más tarde?",
            "Disculpe, parece que hay un problema temporal con nuestro sistema. ¿Podría intentar enviar su pregunta nuevamente?",
            "Lamento la interrupción en nuestro servicio. Nuestro sistema de asesoría financiera está temporalmente saturado. ¿Podría intentarlo de nuevo en unos momentos?",
            "Estamos experimentando problemas de conectividad. Por favor, intente nuevamente su consulta sobre finanzas o uso de nuestra calculadora.",
            "Parece que hay una sobrecarga en nuestro sistema. ¿Le importaría reformular su pregunta sobre finanzas personales?",
            "En este momento no puedo procesar su consulta financiera. Estamos trabajando para resolver los problemas técnicos. ¿Podría intentarlo nuevamente en unos minutos?",
            "Disculpe las molestias. Nuestro servicio de asesoría financiera está experimentando dificultades técnicas temporales. Por favor, intente su consulta más tarde."
        ];
        
        const randomResponse = genericResponses[Math.floor(Math.random() * genericResponses.length)];
        addMessage(randomResponse, "bot-message");
        conversationHistory.push({ role: "assistant", content: randomResponse });
        
        // Asegurarse de que los controles de entrada estén habilitados
        chatbotInput.disabled = false;
        chatbotSend.disabled = false;
        chatbotInput.focus();
    }
    
    // Función para mostrar indicador de escritura
    function showTypingIndicator() {
        const typingIndicator = document.createElement("div");
        typingIndicator.classList.add("chatbot-message", "bot-message", "typing-indicator");
        typingIndicator.innerHTML = "<span></span><span></span><span></span>";
        
        if (chatbotContent) {
            chatbotContent.appendChild(typingIndicator);
            scrollToBottom();
            return typingIndicator;
        } else {
            console.error("El elemento chatbotContent no existe, no se puede mostrar el indicador de escritura");
            return null;
        }
    }
    
    // Función para eliminar indicador de escritura
    function removeTypingIndicator(indicator) {
        if (indicator && indicator.parentNode) {
            indicator.parentNode.removeChild(indicator);
        }
    }
    
    // Función mejorada para agregar mensajes al chat
    function addMessage(text, className, specialType = "") {
        if (!chatbotContent) {
            console.error("El elemento chatbotContent no existe, no se puede añadir mensaje");
            return;
        }
        
        // Para depuración
        console.log("Añadiendo mensaje:", text.substring(0, 30) + "...", "con clase:", className);
        
        const messageDiv = document.createElement("div");
        messageDiv.classList.add("chatbot-message", className);
        
        if (specialType) {
            messageDiv.classList.add(specialType);
        }

        // Formatear el texto para mejorar la legibilidad
        const formattedText = formatMessage(text);
        messageDiv.innerHTML = formattedText;
        
        // Asegurarnos de que el mensaje sea visible
        messageDiv.style.display = "block";
        messageDiv.style.opacity = "1";

        chatbotContent.appendChild(messageDiv);
        
        // Log de verificación
        console.log("Mensaje añadido. Altura del contenido:", chatbotContent.scrollHeight);
        
        scrollToBottom();
    }

    // Función para formatear el texto con mejoras visuales
    function formatMessage(text) {
        if (!text) return "Lo siento, ocurrió un error inesperado. Por favor, intente de nuevo.";
        
        // Dividir el texto en párrafos más cortos
        const paragraphs = text.split("\n").filter(p => p.trim() !== "");
        let formattedText = "";

        paragraphs.forEach(paragraph => {
            // Usar viñetas para listas
            if (paragraph.startsWith("- ")) {
                formattedText += `<p>• ${paragraph.substring(2)}</p>`;
            } else {
                formattedText += `<p>${paragraph}</p>`;
            }
        });

        // Destacar palabras clave con negritas
        const financialKeywords = [
            "ahorro", "inversión", "presupuesto", "deuda", "crédito", "interés", 
            "impuestos", "jubilación", "dinero", "nómina", "prima", 
            "cesantías", "arriendo", "fondo", "CDT"
        ];
        
        financialKeywords.forEach(keyword => {
            const regex = new RegExp(`\\b${keyword}\\b`, "gi");
            formattedText = formattedText.replace(regex, `<strong>${keyword}</strong>`);
        });

        // Añadir emojis relevantes (evitando duplicados)
        if (!formattedText.includes("💰")) formattedText = formattedText.replace(/\b(ahorro|ahorrar|dinero)\b/gi, match => `💰 ${match}`);
        if (!formattedText.includes("📈")) formattedText = formattedText.replace(/\b(inversión|invertir|CDT)\b/gi, match => `📈 ${match}`);
        if (!formattedText.includes("📊")) formattedText = formattedText.replace(/\b(presupuesto|gastos|ingresos)\b/gi, match => `📊 ${match}`);
        if (!formattedText.includes("💳")) formattedText = formattedText.replace(/\b(deuda|crédito|préstamo|tarjeta)\b/gi, match => `💳 ${match}`);
        if (!formattedText.includes("🏠")) formattedText = formattedText.replace(/\b(vivienda|casa|apartamento|arriendo)\b/gi, match => `🏠 ${match}`);
        if (!formattedText.includes("📝")) formattedText = formattedText.replace(/\b(impuestos|declaración)\b/gi, match => `📝 ${match}`);
        if (!formattedText.includes("💼")) formattedText = formattedText.replace(/\b(trabajo|empleo|nómina|sueldo|prima)\b/gi, match => `💼 ${match}`);
        if (!formattedText.includes("🛒")) formattedText = formattedText.replace(/\b(compras|mercado|gastos diarios)\b/gi, match => `🛒 ${match}`);
        if (!formattedText.includes("📱")) formattedText = formattedText.replace(/\b(aplicación|calculadora|herramienta)\b/gi, match => `📱 ${match}`);
        if (!formattedText.includes("📋")) formattedText = formattedText.replace(/\b(estrategia|plan|método|sistema)\b/gi, match => `📋 ${match}`);
        if (!formattedText.includes("👨‍💼")) formattedText = formattedText.replace(/\b(asesor|ayuda|consulta|consejo)\b/gi, match => `👨‍💼 ${match}`);
        if (!formattedText.includes("⏱️")) formattedText = formattedText.replace(/\b(tiempo|plazo|meta|objetivo)\b/gi, match => `⏱️ ${match}`);

        // Destacar la calculadora de gastos
        formattedText = formattedText.replace(/\b(calculadora de gastos)\b/gi, '<strong style="color:#4CAF50;">calculadora de gastos</strong>');

        return formattedText;
    }
    
    // Desplazamiento automático al final del chat - mejorado
    function scrollToBottom() {
        if (chatbotContent) {
            // Usar setTimeout para garantizar que el DOM se ha actualizado
            setTimeout(() => {
                chatbotContent.scrollTop = chatbotContent.scrollHeight;
            }, 10);
        } else {
            console.error("El elemento chatbotContent no existe, no se puede hacer scroll");
        }
    }
    
    // Event listeners con verificación de existencia
    if (chatbotSend) chatbotSend.addEventListener("click", handleSubmit);
    if (chatbotForm) chatbotForm.addEventListener("submit", handleSubmit);
    if (chatbotInput) {
        chatbotInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                handleSubmit();
            }
        });
    }
    
    // Mensaje de bienvenida mejorado con verificación de existencia
    setTimeout(() => {
        if (chatbotContent) {
            console.log("Enviando mensaje de bienvenida...");
            // Verificar si el elemento está visible en la página
            console.log("Estado del contenedor:", {
                width: chatbotContent.offsetWidth,
                height: chatbotContent.offsetHeight,
                visible: chatbotContent.offsetParent !== null
            });
            
            // Mensaje de bienvenida estático predefinido
            const welcomeMessage = `¡Hola! 👋 Soy su 👨‍💼 Asesor Financiero Virtual de **Saving Secure**. 💼 Es un placer acompañarle en su camino hacia la estabilidad económica.

Quiero recordarle que mi expertise está **exclusivamente enfocado en temas financieros**, por lo que estaré encantado de responder sus consultas relacionadas con este ámbito. 📊

Además, le invito a explorar nuestra **📱 calculadora de gastos**, una 📱 herramienta poderosa que le permitirá:

✅ **Controlar su 💰 dinero** de manera eficiente.

✅ Crear **presupuestos personalizados** adaptados a sus necesidades.

✅ Establecer y alcanzar **metas de 💰 ahorro** de forma clara y realista.

✅ Organizar y gestionar sus **deudas** de manera estratégica.

¿En qué aspecto financiero puedo asistirle hoy? 🤔 Estoy aquí para guiarle y ofrecerle soluciones que impulsen su bienestar económico. ¡Cuénteme! 💬`;
            
            addMessage(welcomeMessage, "bot-message", "welcome");
            conversationHistory.push({ role: "assistant", content: welcomeMessage });
        } else {
            console.error("Error: chatbotContent no está disponible para mostrar el mensaje de bienvenida");
        }
    }, 1000); // Aumentado a 1000ms para dar más tiempo al DOM a cargar completamente
});
</script>
<script>
        // Modal ingreso
        document.getElementById('abrirModalIngreso').onclick = function() {
            document.getElementById('modalIngreso').style.display = 'flex';
        };
        function cerrarModalIngreso() {
            document.getElementById('modalIngreso').style.display = 'none';
        }
        // Formatear monto ingreso
        document.getElementById('montoIngreso').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, "");
            let formattedValue = new Intl.NumberFormat("es-ES").format(value);
            e.target.value = formattedValue;
        });
        // Cerrar modal al hacer click fuera
        window.addEventListener('click', function(event) {
            let modal = document.getElementById('modalIngreso');
            if (event.target === modal) {
                cerrarModalIngreso();
            }
        });
        </script>
</body>
</html>


