<?php
// Iniciar sesión y conectar a la base de datos
session_start();
include('db.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_categoria = $_POST['gasto'];
    $id_usuario = $_SESSION['id_usuario'];
    
    // Preparar la consulta SQL
    $sql = "INSERT INTO categorias (nombre_categoria, id_usuario) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nombre_categoria, $id_usuario);
    
    if ($stmt->execute()) {
        $mensaje = "Categoría creada exitosamente";
        $tipo = "success";
    } else {
        $mensaje = "Error al crear la categoría";
        $tipo = "error";
    }
}

// Obtener las categorías del usuario
$id_usuario = $_SESSION['id_usuario'];

// Configuración de paginación
$registros_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;

// Calcular el inicio para la consulta SQL
$inicio = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener el número total de categorías
$sql_total = "SELECT COUNT(*) AS total FROM categorias WHERE id_usuario = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $id_usuario);
$stmt_total->execute();
$resultado_total = $stmt_total->get_result();
$total_registros = $resultado_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener las categorías del usuario con paginación
$sql_categorias = "SELECT * FROM categorias WHERE id_usuario = ? ORDER BY nombre_categoria ASC LIMIT ?, ?";
$stmt_categorias = $conn->prepare($sql_categorias);
$stmt_categorias->bind_param("iii", $id_usuario, $inicio, $registros_por_pagina);
$stmt_categorias->execute();
$resultado_categorias = $stmt_categorias->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="icon" type="image/ico" href="assets/img/logo.webp">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Quicksand:600&display=swap" rel="stylesheet">
    <title>Gestionar Categorías | Saving Secure</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/categoria.css">
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
                <a href="dashboard.php"><button class="" style="color: #fecd02;">Volver</button></a>
            </li>
        </ol>
    </nav>

    <div>
<table class="tabla3" id="tabla">
    <thead>
        <tr> 
            <th>Categoría</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    
                            <?php while ($categoria = $resultado_categorias->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></td>
                                <td>
                                    <button class="btn-editar" onclick="editarCategoria(<?php echo $categoria['id_categoria']; ?>, '<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>')">Editar</button>
                                    <button class="btn-eliminar" onclick="eliminarCategoria(<?php echo $categoria['id_categoria']; ?>)">Eliminar</button>
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





    <main class="dashboard-main">
        <div class="dashboard-content">
            <div class="caja">
                <h2 style="color: #fecd02; text-align: center; margin-bottom: 20px;">Gestionar Categorías</h2>
                
                <form method="POST" action="">
                    <label for="gasto">Nueva Categoría:</label>
                    <input type="text" id="gasto" name="gasto" class="form-style3" required>
                    <button type="submit" class="btn-editar" style="width: 100%;">Agregar Categoría</button>
                </form>

                
            </div>
        </div>
    </main>



<br><br><br>


    
















    <script>
        // Mostrar SweetAlert si hay un mensaje
        <?php if (isset($mensaje)): ?>
        Swal.fire({
            title: '<?php echo $mensaje; ?>',
            icon: '<?php echo $tipo; ?>',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#fecd02',
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            window.location.href = 'dashboard.php';
        });
        <?php endif; ?>

        // Manejar el envío del formulario
        document.getElementById('formCategoria').addEventListener('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Deseas crear esta categoría?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#fecd02',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        function editarCategoria(id, nombre) {
            
            Swal.fire({
                title: 'Editar Categoría',
                input: 'text',
                inputValue: nombre,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return 'El nombre de la categoría no puede estar vacío';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `editar_categoria.php?id=${id}&nombre=${encodeURIComponent(result.value)}`;
                }
            });
        }

        function eliminarCategoria(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#fecd02',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `borrar_categoria.php?id=${id}`;
                }
            });
        }
    </script>
</body>
</html>