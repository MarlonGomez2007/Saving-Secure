<?php
// Datos de conexión a la base de datos
$servername = "127.0.0.1";
$username = "u903298125_Saving_Secure";
$password = "Marlon.20071965";
$dbname = "u903298125_Saving_Secure";

// Crear conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hay error en la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
