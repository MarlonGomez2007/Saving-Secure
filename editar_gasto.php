<?php
// Iniciar sesión y conectar a la base de datos
session_start();
include('db.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// Verificar si se recibió un ID de gasto
if (isset($_GET['id'])) {
    $id_gasto = $_GET['id'];
} else {
    header("Location: dashboard.php");
    exit();
}

// Obtener información del gasto desde la base de datos
$query = "SELECT * FROM gastos WHERE id = $id_gasto AND id_usuario = {$_SESSION['id_usuario']}";
$result = mysqli_query($conn, $query);
$gasto = mysqli_fetch_assoc($result);

// Verificar si el gasto existe y pertenece al usuario
if (!$gasto) {
    header("Location: dashboard.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y limpiar datos del formulario
    $nombre = mysqli_real_escape_string($conn, $_POST['gasto']);
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
    
    // Eliminar las comas del monto antes de actualizar
    $monto = str_replace('.', '', $_POST['monto']);
    
    // Validar que el monto sea numérico
    if (!is_numeric($monto)) {
        echo "El monto no es válido.";
        exit();
    }

    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);

    // Actualizar el gasto en la base de datos
    $updateQuery = "UPDATE gastos SET nombre = '$nombre', descripcion = '$descripcion', monto = '$monto', categoria = '$categoria' WHERE id = $id_gasto";
    if (mysqli_query($conn, $updateQuery)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error al actualizar el gasto: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="icon" type="image/ico" href="assets/img/logo.webp">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Quicksand:600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <title>Editar Gasto | Saving Secure</title>
    <link rel="stylesheet" href="assets/css/editar_gastos.css">
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

    <main class="dashboard-main">
        <div class="dashboard-content">
            <div class="caja">
                <h2 style="color: #fecd02; text-align: center; margin-bottom: 30px;">Editar Gasto</h2>
                <form action="editar_gasto.php?id=<?php echo $gasto['id']; ?>" method="POST" autocomplete="off">
                    <label for="gasto">Nombre del Gasto</label>
                    <input type="text" id="gasto" name="gasto" class="form-style3" value="<?php echo htmlspecialchars($gasto['nombre']); ?>" required>

                    <label for="descripcion">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" class="form-style3" value="<?php echo htmlspecialchars($gasto['descripcion']); ?>">

                    <label for="monto">Monto</label>
                    <input type="text" id="monto" name="monto" class="form-style3" value="<?php echo number_format($gasto['monto'], 0, ',', '.'); ?>" required>
                    <input type="hidden" id="montoReal" name="montoReal" value="<?php echo $gasto['monto']; ?>"> <!-- Campo oculto para el valor real -->

                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria" class="form-style3 select2" required>
                        <option value="Alimentación" <?php if ($gasto['categoria'] == 'Alimentación') echo 'selected'; ?>>Alimentacion</option>
                        <option value="Transporte" <?php if ($gasto['categoria'] == 'Transporte') echo 'selected'; ?>>Transporte</option>
                        <option value="Entretenimiento" <?php if ($gasto['categoria'] == 'Entretenimiento') echo 'selected'; ?>>Entretenimiento</option>
                        <option value="Salud" <?php if ($gasto['categoria'] == 'Salud') echo 'selected'; ?>>Salud</option>
                        <option value="Vivienda" <?php if ($gasto['categoria'] == 'Vivienda') echo 'selected'; ?>>Vivienda</option>
                        <option value="Educación" <?php if ($gasto['categoria'] == 'Educación') echo 'selected'; ?>>Educacion</option>
                        <?php
                            // Consultar las categorías personalizadas del usuario
                            $queryCategorias = "SELECT nombre_categoria FROM categorias WHERE id_usuario = ? ORDER BY nombre_categoria";
                            $stmt = $conn->prepare($queryCategorias);
                            $stmt->bind_param("i", $_SESSION['id_usuario']);
                            $stmt->execute();
                            $resultCategorias = $stmt->get_result();

                            // Mostrar las categorías personalizadas
                            while ($row = $resultCategorias->fetch_assoc()) {
                                $selected = ($gasto['categoria'] === $row['nombre_categoria']) ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($row['nombre_categoria']) . '"' . $selected . ' style="color:white">' . 
                                    htmlspecialchars($row['nombre_categoria']) . '</option>';
                            }
                        ?>
                    </select>
                    
                    <button type="submit">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </main>
    <script>
 document.getElementById("monto").addEventListener("input", function (e) {
    let value = e.target.value.replace(/\D/g, ""); // Elimina todo lo que no sea número
    let formattedValue = new Intl.NumberFormat("es-ES").format(value); // Formatea con separadores

    e.target.value = formattedValue; // Muestra el valor formateado en el campo visible
    document.getElementById("montoReal").value = value; // Mantiene el valor sin formato para el envío
});

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
</body>
</html>