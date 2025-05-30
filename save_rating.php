<?php
// Incluir archivo de conexión a la base de datos
require_once 'db.php';

// Obtener la calificación enviada por el usuario (1-5 estrellas)
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

// Verificar que la calificación sea válida (entre 1 y 5)
if ($rating >= 1 && $rating <= 5) {
    // Preparar y ejecutar la consulta para guardar la calificación
    $stmt = $conn->prepare("INSERT INTO ratings (rating, created_at) VALUES (?, NOW())");
    $stmt->bind_param("i", $rating);

    // Verificar si la inserción fue exitosa
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    // Cerrar la sentencia preparada
    $stmt->close();
} else {
    // Responder si la calificación no es válida
    echo 'invalid';
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
