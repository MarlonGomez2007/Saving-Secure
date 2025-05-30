<?php
// Conexión a la base de datos
include('db.php');

// Verificar si se recibió un ID por GET
if (isset($_GET['id'])) {
    $id_gasto = $_GET['id'];

    // Escapar el ID para prevenir inyección SQL
    $id_gasto = mysqli_real_escape_string($conn, $id_gasto);

    // Consulta para eliminar el gasto
    $delete_query = "DELETE FROM gastos WHERE id = $id_gasto";

    // Ejecutar la consulta y verificar resultado
    if (mysqli_query($conn, $delete_query)) {
        // Redirigir con mensaje de éxito
        header("Location: dashboard.php?mensaje=eliminado");
        exit();
    } else {
        // Redirigir con mensaje de error
        header("Location: dashboard.php?mensaje=error");
        exit();
    }
} else {
    // Redirigir si no se proporcionó un ID
    header("Location: dashboard.php?mensaje=no_id");
    exit();
}
?>
