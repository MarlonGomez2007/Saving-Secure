<?php
session_start();
include('db.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// Verificar si se recibieron los parámetros necesarios
if (isset($_GET['id']) && isset($_GET['nombre'])) {
    $id_categoria = $_GET['id'];
    $nuevo_nombre = $_GET['nombre'];
    $id_usuario = $_SESSION['id_usuario'];
    
    // Verificar que la categoría pertenece al usuario
    $sql_verificar = "SELECT * FROM categorias WHERE id_categoria = ? AND id_usuario = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("ii", $id_categoria, $id_usuario);
    $stmt_verificar->execute();
    $resultado = $stmt_verificar->get_result();
    
    if ($resultado->num_rows > 0) {
        // Actualizar la categoría
        $sql_actualizar = "UPDATE categorias SET nombre_categoria = ? WHERE id_categoria = ? AND id_usuario = ?";
        $stmt_actualizar = $conn->prepare($sql_actualizar);
        $stmt_actualizar->bind_param("sii", $nuevo_nombre, $id_categoria, $id_usuario);
        
        if ($stmt_actualizar->execute()) {
            $_SESSION['mensaje'] = "Categoría actualizada exitosamente";
            $_SESSION['tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar la categoría";
            $_SESSION['tipo'] = "error";
        }
    } else {
        $_SESSION['mensaje'] = "No tienes permiso para editar esta categoría";
        $_SESSION['tipo'] = "error";
    }
} else {
    $_SESSION['mensaje'] = "Parámetros inválidos";
    $_SESSION['tipo'] = "error";
}

header("Location: categorias.php");
exit();
?> 