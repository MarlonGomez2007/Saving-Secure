<?php
// Incluir archivo de conexión a la base de datos
require_once 'db.php';

// Iniciar sesión
session_start();

// Verificar si el usuario es administrador (rol 2)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {  
    header('Content-Type: application/json');
    die(json_encode(['error' => 'No autorizado']));
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

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Método no permitido']));
}

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Token CSRF inválido']));
}

// Obtener y validar datos
$id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
$nuevo_rol = isset($_POST['nuevo_rol']) ? intval($_POST['nuevo_rol']) : 0;

if ($id_usuario <= 0 || ($nuevo_rol !== 1 && $nuevo_rol !== 2)) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Datos inválidos']));
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