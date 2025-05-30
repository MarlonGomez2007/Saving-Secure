<?php
require_once 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';


$mensaje = '';
$tipo = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y limpiar los datos
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $sugerencia = filter_var($_POST['sugerencia'], FILTER_SANITIZE_STRING);
    
    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($email) || empty($sugerencia)) {
        $mensaje = 'Todos los campos son obligatorios';
        $tipo = 'error';
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El formato del correo electrónico no es válido';
        $tipo = 'error';
    }
    
    try {
        // Guardar en la base de datos
        $sql = "INSERT INTO sugerencia (Nombre, Correo, Sugerencia, Fecha) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $nombre, $email, $sugerencia);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }
        
        // Enviar correo electrónico
        $mail = new PHPMailer(true);
        
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'savingsecure3@gmail.com';
        $mail->Password = 'vvkz zzba zftl sgik';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Configuración del correo
        $mail->setFrom('savingsecure3@gmail.com', 'Saving Secure');
        $mail->addAddress($email, $nombre);
        
        $mail->isHTML(true);
        // Asunto del correo codificado en UTF-8
        $mail->Subject = '=?UTF-8?B?' . base64_encode('¡Gracias por tu sugerencia!') . '?=';
        
        // Cuerpo del correo con diseño HTML y emoji de check
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa; border-radius: 10px;">
            <div style="font-size: 48px; text-align: center; margin-bottom: 20px;">✅</div>
            <h2 style="color: #2c3e50; text-align: center;">¡Gracias por tu sugerencia!</h2>
            <p style="color: #34495e; font-size: 16px; line-height: 1.6;">
                Hola ' . htmlspecialchars($nombre) . ',
            </p>
            <p style="color: #34495e; font-size: 16px; line-height: 1.6;">
                Hemos recibido tu sugerencia y la estamos revisando. Tu opinión es muy importante para nosotros y nos ayuda a mejorar nuestros servicios.
            </p>
            <p style="color: #34495e; font-size: 16px; line-height: 1.6;">
                Detalles de tu sugerencia:
            </p>
            <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <p style="color: #34495e; font-size: 16px; line-height: 1.6;">
                    <strong>Correo:</strong> ' . htmlspecialchars($email) . '<br>
                    <strong>Sugerencia:</strong> ' . htmlspecialchars($sugerencia) . '
                </p>
            </div>
            <p style="color: #34495e; font-size: 16px; line-height: 1.6;">
                ¡Gracias por ayudarnos a mejorar!
            </p>
            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #7f8c8d; font-size: 14px;">
                    Saving Secure - Tu seguridad es nuestra prioridad
                </p>
            </div>
        </div>';
        
        $mail->send();
        $mensaje = '¡Gracias por tu sugerencia!';
        $tipo = 'success';
        
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo = 'error';
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guardar Sugerencia</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php if ($mensaje): ?>
<script>
    Swal.fire({
        icon: '<?php echo $tipo; ?>',
        title: '<?php echo ($tipo == "success") ? "¡Enviado!" : "Error"; ?>',
        html: '<?php echo $mensaje; ?>',
        confirmButtonColor: '#ffc107',
        background: 'white',
        color: 'black'
    }).then(() => {
        window.location.href = "index.html";
    });
</script>
<?php endif; ?>
</body>
</html>
