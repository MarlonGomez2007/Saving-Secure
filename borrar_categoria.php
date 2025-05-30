<?php
// Iniciar sesión y conectar a la base de datos
session_start();
include('db.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// Verificar si se recibió un ID de categoría
if (!isset($_GET['id'])) {
    header("Location: categorias.php");
    exit();
}

$id_categoria = $_GET['id'];
$id_usuario = $_SESSION['id_usuario'];

// Verificar que la categoría pertenezca al usuario antes de borrarla
$query = "SELECT * FROM categorias WHERE id_categoria = ? AND id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_categoria, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: categorias.php");
    exit();
}

// Borrar la categoría
$query = "DELETE FROM categorias WHERE id_categoria = ? AND id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_categoria, $id_usuario);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Categoría eliminada exitosamente";
    $_SESSION['tipo'] = "success";
} else {
    $_SESSION['mensaje'] = "Error al eliminar la categoría";
    $_SESSION['tipo'] = "error";
}

header("Location: categorias.php");
exit();
?> 