<?php
// Incluir archivo de conexión a la base de datos
require_once 'db.php';
// Iniciar sesión para manejar datos del usuario
session_start();

// Obtener datos del formulario de login
$correo = $_POST['correo'];
$contra = $_POST['contra'];

// Configuración de reCAPTCHA
$recaptcha_secret = '6Lei-IEqAAAAANJF0jrz46P3q5ZQxa5gfR7o64Tr';  
$recaptcha_response = $_POST['g-recaptcha-response'];

// Verificar si se completó el CAPTCHA
if (empty($recaptcha_response)) {
    // Mostrar error si no se completó el CAPTCHA
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Verificación de CAPTCHA</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Error de CAPTCHA',
                text: 'Por favor, completa el CAPTCHA.',
                icon: 'error',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = 'login.html';
            });
        </script>
    </body>
    </html>";
    exit();
}

// Verificar la respuesta del CAPTCHA con Google
$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
$response = file_get_contents($verify_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
$response_keys = json_decode($response, true);

// Si la verificación del CAPTCHA falla
if (intval($response_keys['success']) !== 1) {
    // Mostrar error de verificación de CAPTCHA
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Verificación de CAPTCHA</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Error de CAPTCHA',
                text: 'Verificación del CAPTCHA fallida. Por favor, inténtalo de nuevo.',
                icon: 'error',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = 'login.html';
            });
        </script>
    </body>
    </html>";
    exit();
}

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Buscar usuario por correo electrónico
$stmt = $conn->prepare("SELECT id_usuario, nombre, contra, rol FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

// Verificar si el usuario existe
if ($resultado->num_rows > 0) {
    $user = $resultado->fetch_assoc();

    // Verificar si la contraseña está en formato MD5
    if (strlen($user['contra']) == 32) { 
        // Verificar si la contraseña es correcta
        if (md5($contra) === $user['contra']) {
            // Guardar datos del usuario en la sesión
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];

            // Redirigir según el rol del usuario
            if ($user['rol'] == 2) { 
                echo "<script>window.location.href = 'panel_admin.php';</script>";
            } else {
                echo "<script>window.location.href = 'dashboard.php?welcome=1';</script>";
            }

            exit();
        } else {
            // Mostrar error si la contraseña es incorrecta
            echo "";
            echo "<!DOCTYPE html>
            <html lang='es'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Error de Inicio de Sesión</title>
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: 'Error',
                        text: 'El Correo o la Contraseña son Incorrectas.',
                        icon: 'error',
                        confirmButtonText: 'Intentar de nuevo'
                    }).then(() => {
                        window.location.href = 'login.html';
                    });
                </script>
            </body>
            </html>";
            exit();
        }
    } else {
        // Error si la contraseña no está en formato MD5
        echo "<p>Contraseña no está en formato MD5</p>";
    }

} else {
    // Mostrar error si el usuario no existe
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error de Inicio de Sesión</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Error',
                text: 'El Correo o la Contraseña son Incorrectas.',
                icon: 'error',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = 'login.html';
            });
        </script>
    </body>
    </html>";
    exit();
}

// Cerrar conexiones
$stmt->close();
$conn->close();
?>
