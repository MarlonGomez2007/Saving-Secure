<?php
session_start();
include 'db.php'; // Conexión a la base de datos

require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $conn->real_escape_string($_POST['correo']);

    // Evitar SQL Injection con prepared statements
    $stmt = $conn->prepare("SELECT id_usuario, nombre FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'];

        // Guardar correo en sesión
        $_SESSION['correo'] = $correo;
        $_SESSION['nombre'] = $nombre;

        // Redirigir a la página de selección de método
        header("Location: seleccionar_metodo.php");
        exit();
    } else {
        echo "<script>alert('El correo no está registrado.');</script>";
    }
}

?>
<!doctype html>
<html lang="es">
<head>
    <title>Recuperar Contraseña - Saving Secure</title>
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/olvidar_contra.css">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.webp">
</head>
<body>
    <header id="masthead" class="site-header">
        <nav id="primary-navigation" class="site-navigation anim-right">
            <div class="container anim-right">
                <div class="navbar-header">
                    <img src="assets/img/logo.webp" alt="Logo de Saving Secure" style="width: 85px; height: 70px; vertical-align: middle; margin-right: 5px;">
                    <a href="login.html"><button>Volver</button></a>
                </div>
            </div>
        </nav>
    </header>

    <div class="section anim-fade-in anim-pause-2">
        <div class="container">
            <div class="row full-height justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 text-center align-self-center py-5">
                    <div class="section pb-5 pt-5 pt-sm-2 text-center">
                        <h4 class="pb-3">Recuperar Contraseña</h4>
                        <form method="post">
                            <div class="form-group">
                                <input type="email" class="form-style" placeholder="Correo Electrónico" name="correo" required>
                            </div>
                            <input type="submit" value="Continuar" class="btn mt-4">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>