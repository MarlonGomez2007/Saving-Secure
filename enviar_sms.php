<?php
session_start();
include 'db.php';


if (!isset($_SESSION['correo']) || !isset($_SESSION['codigo'])) {
    header("Location: solicitar_correo.php");
    exit();
}


$correo = $_SESSION['correo'];
$stmt = $conn->prepare("SELECT numero, nombre FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$telefono = $row['numero'];
$nombre = $row['nombre'];
?>