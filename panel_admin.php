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

// Función para mostrar mensajes de error
function mostrarError($mensaje) {
    echo "<script>alert('$mensaje');</script>";
}

// Función para generar token CSRF
function generarTokenCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para validar token CSRF
function validarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Verificar token CSRF en POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
        mostrarError('Token CSRF inválido');
        exit();
    }
}

// Procesar solicitud para cambiar rol de usuario
if (isset($_POST['cambiar_rol'])) {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Token CSRF inválido']));
    }

    $id_usuario = intval($_POST['id_usuario']);
    $nuevo_rol = intval($_POST['nuevo_rol']);

    // Validar que el rol sea 1 o 2
    if ($nuevo_rol !== 1 && $nuevo_rol !== 2) {
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Rol inválido']));
    }

    try {
        global $conn;

        // Verificar que el usuario existe
        $stmt_verificar = $conn->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
        $stmt_verificar->bind_param("i", $id_usuario);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->get_result();
        
        if ($resultado->num_rows === 0) {
            header('Content-Type: application/json');
            die(json_encode(['error' => 'Usuario no encontrado']));
        }

        // Actualizar el rol usando consulta preparada
        $stmt = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("ii", $nuevo_rol, $id_usuario);
        
        if ($stmt->execute()) {
            header('Content-Type: application/json');
            die(json_encode(['success' => true]));
        } else {
            throw new Exception("Error al actualizar el rol: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error al cambiar rol: " . $e->getMessage());
        header('Content-Type: application/json');
        die(json_encode(['error' => $e->getMessage()]));
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($stmt_verificar)) $stmt_verificar->close();
    }
}

// Procesar formulario para agregar nuevo usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_usuario'])) {
    // Obtener datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contra = $_POST['contra'] ?? '';
    $rol = $_POST['rol'] ?? '';

    // Validar que todos los campos estén completos
    if (empty($nombre) || empty($numero) || empty($correo) || empty($contra) || empty($rol)) {
        mostrarError('Todos los campos son obligatorios');
        exit();
    }

    // Limpiar y validar datos
    $nombre = filter_var(trim($nombre), FILTER_SANITIZE_STRING);
    $numero = filter_var(trim($numero), FILTER_SANITIZE_STRING);
    $correo = filter_var(trim($correo), FILTER_SANITIZE_EMAIL);
    $contra = md5($contra);

    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        mostrarError('Formato de correo electrónico inválido');
        exit();
    }

    // Validar formato de número telefónico
    if (!preg_match('/^[0-9]{10}$/', $numero)) {
        mostrarError('El número telefónico debe tener 10 dígitos');
        exit();
    }

    // Insertar usuario en la base de datos
    try {
        // Usar la conexión existente desde db.php
        global $conn;
        
        // Preparar consulta
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, numero, correo, contra, rol, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la inserción");
        }

        // Ejecutar consulta
        $fecha_registro = date('Y-m-d H:i:s');
        $stmt->bind_param("ssssss", $nombre, $numero, $correo, $contra, $rol, $fecha_registro);

        if (!$stmt->execute()) {
            throw new Exception("Error al insertar el usuario");
        }

        echo "<script>alert('Usuario registrado exitosamente'); window.location.href = 'panel_admin.php';</script>";
    } catch (Exception $e) {
        error_log("Error en registro: " . $e->getMessage());
        mostrarError('Error en el sistema. Por favor, intente más tarde.');
    } finally {
        if (isset($stmt)) $stmt->close();
    }
}

// Procesar solicitud para eliminar usuario
if (isset($_GET['eliminar'])) {
    $id_usuario = $_GET['eliminar'];

    try {
        // Usar la conexión existente desde db.php
        global $conn;

        // Verificar si el usuario existe
        $stmt_verificar = $conn->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ?");
        $stmt_verificar->bind_param("i", $id_usuario);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->get_result();
        
        if ($resultado->num_rows === 0) {
            throw new Exception("El usuario no existe");
        }

        // Desactivar temporalmente las restricciones de clave foránea
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Eliminar registros relacionados primero
            // Ya no eliminamos los logs para mantener el historial
            $stmt_eliminar_gastos = $conn->prepare("DELETE FROM gastos WHERE id_usuario = ?");
            $stmt_eliminar_gastos->bind_param("i", $id_usuario);
            $stmt_eliminar_gastos->execute();

            $stmt_eliminar_categorias = $conn->prepare("DELETE FROM categorias WHERE id_usuario = ?");
            $stmt_eliminar_categorias->bind_param("i", $id_usuario);
            $stmt_eliminar_categorias->execute();

            // Finalmente eliminar el usuario
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            if (!$stmt) {
                throw new Exception("Error en la preparación de la eliminación");
            }

            $stmt->bind_param("i", $id_usuario);

            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar el usuario");
            }

            // Confirmar la transacción
            $conn->commit();

            // Reactivar las restricciones de clave foránea
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");

            echo "<script>
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Usuario y sus datos relacionados eliminados correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'panel_admin.php';
                    }
                });
            </script>";
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $conn->rollback();
            // Reactivar las restricciones de clave foránea
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Error al eliminar: " . $e->getMessage());
        echo "<script>
            Swal.fire({
                title: 'Error',
                text: '" . addslashes($e->getMessage()) . "',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'panel_admin.php';
                }
            });
        </script>";
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($stmt_verificar)) $stmt_verificar->close();
        if (isset($stmt_eliminar_gastos)) $stmt_eliminar_gastos->close();
        if (isset($stmt_eliminar_categorias)) $stmt_eliminar_categorias->close();
    }
}

// Obtener parámetros de paginación y búsqueda
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 6;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Obtener lista de usuarios con paginación y búsqueda
$usuarios = [];
$total_usuarios = 0;

try {
    global $conn;
    
    // Construir la consulta base
    $sql_base = "SELECT id_usuario, nombre, numero, correo, rol, fecha_registro FROM usuarios";
    $sql_contador = "SELECT COUNT(*) as total FROM usuarios";
    $where = [];
    $params = [];
    $types = "";

    if ($busqueda) {
        $where[] = "(nombre LIKE ? OR correo LIKE ? OR numero LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params = array_merge($params, [$busqueda_param, $busqueda_param, $busqueda_param]);
        $types .= "sss";
    }

    if (!empty($where)) {
        $sql_base .= " WHERE " . implode(" AND ", $where);
        $sql_contador .= " WHERE " . implode(" AND ", $where);
    }

    // Obtener total de usuarios
    $stmt = $conn->prepare($sql_contador);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $resultado = $stmt->get_result();
    $total_usuarios = $resultado->fetch_assoc()['total'];
    $stmt->close();

    // Calcular paginación
    $total_paginas = ceil($total_usuarios / $por_pagina);
    $offset = ($pagina - 1) * $por_pagina;

    // Obtener usuarios paginados
    $sql_base .= " LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql_base);
    
    if (!empty($params)) {
        $types .= "ii";
        $params = array_merge($params, [$por_pagina, $offset]);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param("ii", $por_pagina, $offset);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $usuarios[] = $fila;
    }
} catch (Exception $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
    mostrarError('Error al cargar usuarios. Por favor, intente más tarde.');
} finally {
    if (isset($stmt)) $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/panel_admin.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.webp">
    <title>Panel de Administración | Saving Secure</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    
    <div class="sidebar">
        <div class="logo">
            <i class="ri-database-2-line"></i>
            Panel Admin
        </div>
        <nav class="nav-links">
            <a href="panel_admin.php" class="active"><i class="ri-user-line"></i> Usuarios</a>
            <a href="logs.php"><i class="ri-history-line"></i> Logs</a>
            <a href="mostrar_sugerencias.php"><i class="ri-feedback-line"></i> Sugerencias</a>
            <a href="mostrar_ratings.php"><i class="ri-star-line"></i> Ratings</a>
            <a href="generar_informe_admin.php"><i class="ri-download-line"></i> Descargar Informe</a>
            <a href="login.html"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
        </nav>
    </div>
    
    <div class="main-content">
        <div class="admin-panel">
            <div class="section-header">
                <h1>Gestión de Usuarios</h1>
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="">
                            <input type="text" name="busqueda" placeholder="Buscar usuarios..." value="<?php echo htmlspecialchars($busqueda); ?>">
                            <button type="submit"><i class="ri-search-line" style="color: black;"></i>
                            </i></button>
                        </form>
                    </div>
                    <button class="add-user-btn" onclick="openAddUserModal()">
                        <i class="ri-add-line"></i> Agregar Usuario
                    </button>
                </div>
            </div>

            <div class="user-grid">
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="user-card">
                        <div class="user-card-header">
                            <span><?php echo $usuario['nombre']; ?></span>
                            <span class="user-badge">
                                <?php echo $usuario['rol'] == 2 ? 'Administrador' : 'Usuario'; ?>
                            </span>
                        </div>
                        <div>
                            <strong>Correo:</strong> <?php echo $usuario['correo']; ?><br>
                        </div>
                        <div class="user-actions">
                        <a href="javascript:void(0);" onclick="confirmarEliminar(<?php echo $usuario['id_usuario']; ?>)" style="text-decoration: none; color: inherit; color: black; text-align: center;" class="btn btn-delete">Eliminar</a>
                            <button class="btn btn-edit" onclick="editRole(<?php echo $usuario['id_usuario']; ?>)">Editar Rol</button>
                        </div>
                    </div>
                <?php endforeach; ?>
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

    <div id="addUserModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Agregar Nuevo Usuario</h2>
            <form class="modal-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                <input type="text" name="nombre" placeholder="Nombre" required>
                <input type="text" name="numero" placeholder="Número" required>
                <input type="email" name="correo" placeholder="Correo" required>
                <input type="password" name="contra" placeholder="Contraseña" required>
                <select name="rol" required>
                    <option value="2">Administrador</option>
                    <option value="1">Usuario</option>
                </select>
                <button type="submit" name="agregar_usuario">Registrar</button>
            </form>
            <button type="button" class="close-btn" onclick="closeAddUserModal()">Cerrar</button>
        </div>
    </div>


   
    <div id="changeRoleModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Cambiar Rol de Usuario</h2>
            <form id="changeRoleForm" method="POST" class="modal-form">
                <input type="hidden" name="csrf_token" value="<?php echo generarTokenCSRF(); ?>">
                <input type="hidden" name="id_usuario" id="id_usuario">
                <select name="nuevo_rol" required>
                    <option value="1">Usuario</option>
                    <option value="2">Administrador</option>
                </select>
                <button type="submit" name="cambiar_rol">Actualizar Rol</button>
            </form>
            <button class="close-btn" onclick="closeChangeRoleModal()">Cerrar</button>
        </div>
    </div>


<script>
   // Función para abrir el modal de edición de rol y establecer el ID del usuario
    function editRole(id_usuario) {
        // Asignar el ID del usuario al campo oculto del formulario
        document.getElementById('id_usuario').value = id_usuario;
        
        // Mostrar el modal de cambio de rol
        document.getElementById('changeRoleModal').style.display = 'block';
    }

    // Función para cerrar el modal de cambio de rol
    function closeChangeRoleModal() {
        document.getElementById('changeRoleModal').style.display = 'none';
    }

    // Manejar el envío del formulario de cambio de rol
    document.getElementById('changeRoleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('cambiar_rol.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Rol actualizado correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.error || 'Error al actualizar el rol',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error al procesar la solicitud: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
</script>


    <script>
        // Función para abrir el modal de agregar usuario
        function openAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }

        // Función para cerrar el modal de agregar usuario
        function closeAddUserModal() {
            document.getElementById('addUserModal').style.display = 'none';
        }
    </script>
    <script>
    // Función para mostrar confirmación antes de eliminar un usuario
    function confirmarEliminar(id_usuario) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esto.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirigir a la URL de eliminación
                window.location.href = '?eliminar=' + id_usuario;
            }
        });
    }

    // Verificar si hay un mensaje de éxito en la URL
    if (window.location.search.includes('eliminar')) {
        Swal.fire({
            title: '¡Éxito!',
            text: 'Usuario eliminado correctamente',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'panel_admin.php';
            }
        });
    }
</script>

</body>
</html>
