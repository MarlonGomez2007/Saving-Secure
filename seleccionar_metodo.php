<?php
session_start();
include 'db.php';
require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['correo'])) {
    header("Location: solicitar_correo.php");
    exit();
}

// Obtener el correo y número de teléfono del usuario
$correo = $_SESSION['correo'];
$stmt = $conn->prepare("SELECT numero FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$telefono = $row['numero'];

// Censurar el correo y teléfono
$correo_censurado = preg_replace('/(?<=.).(?=.*@)/', '*', $correo);
$telefono_censurado = substr($telefono, 0, 3) . '****' . substr($telefono, -3);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $metodo = $_POST['metodo'];
    
    if ($metodo == 'correo') {
        // Generar código aleatorio de 6 dígitos
        $codigo = rand(100000, 999999);
        $_SESSION['codigo'] = $codigo;
        
        // Configurar PHPMailer
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

            // Configurar destinatario y remitente
            $mail->setFrom('no-reply@savingsecure.com', 'Saving Secure');
            $mail->addAddress($correo, $_SESSION['nombre']);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '=?UTF-8?B?' . base64_encode('Código de recuperación de contraseña') . '?=';
            $mail->isHTML(true);

            // Mensaje HTML del correo
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { padding: 20px; text-align: center; }
                    .header { background-color: #fecd02; color: black; padding: 10px; font-size: 20px; font-weight: bold; }
                    .code { font-size: 24px; font-weight: bold; color: #333; padding: 10px; border: 1px solid #ddd; display: inline-block; }
                    .footer { font-size: 12px; color: #666; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>Recuperación de Contraseña - Saving Secure</div>
                    <p>Hola <strong>{$_SESSION['nombre']}</strong>,</p>
                    <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                    <p>Tu código de verificación es:</p>
                    <div class='code'>$codigo</div>
                    <p>Si no realizaste esta solicitud, puedes ignorar este correo.</p>
                    <div class='footer'>Este es un correo automático, por favor no responder.</div>
                </div>
            </body>
            </html>";

            // Enviar correo
            if ($mail->send()) {
                header("Location: verificar_codigo.php");
                exit();
            } else {
                echo "<script>alert('Error al enviar el correo.');</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('Error al enviar el correo: {$mail->ErrorInfo}');</script>";
        }
    } elseif ($metodo == 'sms') {
        // Generar código aleatorio de 6 dígitos
        $codigo = rand(100000, 999999);
        $_SESSION['codigo'] = $codigo;
        
        // Redirigir a la página de envío de SMS
        header("Location: enviar_sms.php");
        exit();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>Seleccionar Método - Saving Secure</title>
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.webp">
    <link rel="stylesheet" href="assets/css/olvidar_contra.css">
</head>
<body>
    <header id="masthead" class="site-header">
        <nav id="primary-navigation" class="site-navigation anim-right">
            <div class="container anim-right">
                <div class="navbar-header">
                    <img src="assets/img/logo.webp" alt="Logo de Saving Secure" style="width: 85px; height: 70px; vertical-align: middle; margin-right: 5px;">
                    <a href="solicitar_correo.php"><button>Volver</button></a>
                </div>
            </div>
        </nav>
    </header>

    <div class="section anim-fade-in anim-pause-2">
        <div class="container">
            <div class="row full-height justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 text-center align-self-center py-5">
                    <div class="section pb-5 pt-5 pt-sm-2 text-center">
                        <h4 class="pb-3">Seleccionar Método de Verificación</h4>
                        <p class="text-muted">Selecciona cómo deseas recibir tu código de verificación</p>
                        
                        <form method="post" class="mt-4">
                            <div class="form-group">
                                <div class="custom-control custom-radio mb-3">
                                    <input type="radio" id="correo" name="metodo" value="correo" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="correo" style="color: white;">
                                        Correo Electrónico: <?php echo $correo_censurado; ?>
                                    </label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="sms" name="metodo" value="sms" class="custom-control-input">
                                    <label class="custom-control-label" for="sms" style="color: white;">
                                        SMS: <?php echo $telefono_censurado; ?>
                                    </label>
                                </div>
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