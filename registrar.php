<?php
// Incluir archivo de conexión a la base de datos
require_once 'db.php';

// Incluir librerías de PHPMailer para envío de correos
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

// Importar clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Recoger datos del formulario
$campos = [
    'nombre' => $_POST['nombre'] ?? '',
    'numero' => $_POST['numero'] ?? '',
    'correo' => $_POST['correo'] ?? '',
    'contra' => $_POST['contra'] ?? ''
];

// Verificar que todos los campos estén completos
foreach ($campos as $campo => $valor) {
    if (empty(trim($valor))) {
        mostrarError('Todos los campos son obligatorios');
        exit();
    }
}

// Limpiar y validar datos
$nombre = filter_var(trim($campos['nombre']), FILTER_SANITIZE_STRING);
$numero = filter_var(trim($campos['numero']), FILTER_SANITIZE_STRING);
$correo = filter_var(trim($campos['correo']), FILTER_SANITIZE_EMAIL);
$contra = $campos['contra'];

// Validar formato de correo electrónico
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    mostrarError('Formato de correo electrónico inválido');
    exit();
}

// Validar longitud de contraseña
if (strlen($contra) < 8) {
    mostrarError('La contraseña debe tener al menos 8 caracteres');
    exit();
}

// Validar formato de número telefónico
if (!preg_match('/^[0-9]{10}$/', $numero)) {
    mostrarError('El número telefónico debe tener 10 dígitos');
    exit();
}

try {
    // Usar la conexión existente desde db.php
    global $conn;
    
    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión a la base de datos");
    }

    // Verificar si el correo ya está registrado
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta");
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        mostrarError('El correo ya está registrado');
        exit();
    }
    $stmt->close();

    // Preparar datos para inserción
    $contra_hasheada = md5($contra);
    $rol = 1; // Rol de usuario normal
    $fecha_registro = date('Y-m-d H:i:s');

    // Insertar nuevo usuario en la base de datos
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, numero, correo, contra, rol, fecha_registro) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la inserción");
    }

    $stmt->bind_param("ssssss", $nombre, $numero, $correo, $contra_hasheada, $rol, $fecha_registro);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al insertar el usuario");
    }

    $stmt->close();
    $conn->close();

    // Configurar y enviar correo de bienvenida
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'savingsecure3@gmail.com'; 
        $mail->Password = 'vvkz zzba zftl sgik'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configurar remitente y destinatario
        $mail->setFrom('no-reply@savingsecure.com', 'Saving Secure');
        $mail->addAddress($correo, $nombre);
        $mail->Subject = 'Bienvenido a Saving Secure - Registro Exitoso';
        $mail->isHTML(true);

        // Contenido del correo
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { padding: 20px; }
                .header { background-color: #fecd02; color: black; padding: 10px; }
                .content { margin: 20px 0; }
                .footer { font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>¡Bienvenido a Saving Secure!</h2>
                </div>
                <div class='content'>
                    <p>Hola <strong>$nombre</strong>,</p>
                    <p>Tu cuenta ha sido creada exitosamente en Saving Secure.</p>
                    <p>Datos de tu registro:</p>
                    <ul>
                        <li>Nombre: $nombre</li>
                        <li>Correo: $correo</li>
                        <li>Fecha de registro: $fecha_registro</li>
                    </ul>
                    <p>Ya puedes iniciar sesión en nuestra plataforma.</p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no responder.</p>
                </div>
            </div>
        </body>
        </html>";

        // Mostrar mensaje de éxito según si se envió el correo o no
        if ($mail->send()) {
            // Mensaje si el correo se envió correctamente
            echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Registro Exitoso</title>
            <script src='assets/cdn/sweetalert2@11.js'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: '¡Registro exitoso!',
                    text: 'Tu cuenta ha sido creada correctamente y se ha enviado un correo de confirmación',
                    icon: 'success',
                    confirmButtonText: 'Iniciar sesión'
                }).then(() => {
                    window.location.href = 'login.html';
                });
            </script>
        </body>
        </html>";
    } else {
        // Mensaje si el registro fue exitoso pero no se envió el correo
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Registro Exitoso</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: '¡Registro exitoso!',
                    text: 'Tu cuenta ha sido creada correctamente',
                    icon: 'success',
                    confirmButtonText: 'Iniciar sesión'
                }).then(() => {
                    window.location.href = 'login.html';
                });
            </script>
        </body>
        </html>";
    }

    } catch (Exception $e) {
        mostrarError('Error al enviar el correo: ' . $mail->ErrorInfo);
    }
} catch (Exception $e) {
    error_log("Error en registro: " . $e->getMessage());
    mostrarError('Error en el sistema. Por favor, intente más tarde.');
}

// Función para mostrar mensajes de error con SweetAlert
function mostrarError($mensaje) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: 'Error',
                text: '" . htmlspecialchars($mensaje) . "',
                icon: 'error',
                confirmButtonText: 'Intentar de nuevo'
            }).then(() => {
                window.location.href = 'login.html';
            });
        </script>
    </body>
    </html>";
}
?>
