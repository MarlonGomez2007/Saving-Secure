<?php
// Iniciar sesión y conectar a la base de datos
session_start();
include 'db.php';

// Verificar si el usuario está verificado y tiene correo en sesión
if (!isset($_SESSION['verificado']) || !isset($_SESSION['correo'])) {
    header("Location: solicitar_correo.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nueva_contra = $_POST['nueva_contra'];
    $confirmar_contra = $_POST['confirmar_contra'];
    $correo = $_SESSION['correo'];

    // Verificar que las contraseñas coincidan
    if ($nueva_contra === $confirmar_contra) {
        // Encriptar la contraseña con MD5
        $contra_md5 = md5($nueva_contra);

        // Actualizar la contraseña en la base de datos
        $sql = "UPDATE usuarios SET contra='$contra_md5' WHERE correo='$correo'";

        if ($conn->query($sql) === TRUE) {
            // Mostrar mensaje de éxito y redirigir al login
            echo "<script>
                alert('Contraseña actualizada correctamente.');
                window.location.href = 'login.html';
            </script>";
            session_destroy();
        } else {
            // Mostrar mensaje de error
            echo "<script>alert('Error al actualizar la contraseña.');</script>";
        }
    } else {
        // Mostrar mensaje si las contraseñas no coinciden
        echo "<script>alert('Las contraseñas no coinciden.');</script>";
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <title>Cambiar Contraseña - Saving Secure</title>
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.9/css/unicons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/olvidar_contra.css">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/logo.webp">
    <style>
	.password-container {
		position: relative;
		width: 100%;
	}
	
	.password-input {
		width: 100%;
		padding: 12px;
		padding-right: 40px;
		background-color: #2a2a2a;
		border: none;
		border-radius: 4px;
		color: white;
		font-size: 14px;
	}
	
	.toggle-password {
		position: absolute;
		right: 10px;
		top: 50%;
		transform: translateY(-50%);
		cursor: pointer;
		color: #888;
		padding: 5px;
	}
	
	.toggle-password:hover {
		color: #ffd700;
        
	}
    .anim-up, .anim-down, .anim-left, .anim-right, .anim-fade-in {
	animation-duration: 0.2s; 
	animation-delay: 0.2s; 
	animation-fill-mode: both; 
}
</style>

<style>
.mensaje-validacion {
    margin-top: 5px;
    font-size: 14px;
    min-height: 20px;
}

.coincide {
    color: #2ecc71;
}

.no-coincide {
    color: #e74c3c;
}
</style>
</head>
<body>
    <header id="masthead" class="site-header">
        <nav id="primary-navigation" class="site-navigation anim-right">
            <div class="container anim-right">
                <div class="navbar-header">
                    <img src="assets/img/logo.webp" alt="Logo de Saving Secure" style="width: 85px; height: 70px; vertical-align: middle; margin-right: 5px;">
                    <a href="verificar_codigo.php"><button>Volver</button></a>
                </div>
            </div>
        </nav>
    </header>

    <div class="section anim-fade-in anim-pause-2">
        <div class="container">
            <div class="row full-height justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 text-center align-self-center py-5">
                    <div class="section pb-5 pt-5 pt-sm-2 text-center">
                        <h4 class="pb-3">Cambiar Contraseña</h4>
                        <form method="post">
                        <div class="form-group">
                                    <div class="password-container">
                                        <input type="password" class="form-style" id="nueva_contra" placeholder="Nueva Contraseña" name="nueva_contra" style="color: white;" required>
                                        <i class="toggle-password fas fa-eye" onclick="togglePassword(this)"></i>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="password-container">
                                        <input type="password" class="form-style" id="confirmar_contra" placeholder="Confirmar Contraseña" name="confirmar_contra" style="color: white;" required>
                                        <i class="toggle-password fas fa-eye" onclick="togglePassword(this)"></i>
                                    </div>
                                    <div id="mensaje_validacion" class="mensaje-validacion"></div>
                                </div>
                            <input type="submit" value="Actualizar Contraseña" class="btn mt-4">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Función para mostrar/ocultar la contraseña
    function togglePassword(icon) {
        const input = icon.previousElementSibling;
        if (input.type === "password") {
            // Cambiar a texto visible
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            // Cambiar a contraseña oculta
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
    </script>

    
<script>
// Ejecutar cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencias a los elementos del formulario
    const nuevaContra = document.getElementById('nueva_contra');
    const confirmarContra = document.getElementById('confirmar_contra');
    const mensajeValidacion = document.getElementById('mensaje_validacion');

    // Función para validar si las contraseñas coinciden
    function validarContraseñas() {
        const nueva = nuevaContra.value;
        const confirmacion = confirmarContra.value;

        if (confirmacion.length === 0) {
            // Campo de confirmación vacío
            mensajeValidacion.textContent = '';
            mensajeValidacion.className = 'mensaje-validacion';
        } else if (nueva === confirmacion) {
            // Las contraseñas coinciden
            mensajeValidacion.textContent = 'Las contraseñas coinciden';
            mensajeValidacion.className = 'mensaje-validacion coincide';
        } else {
            // Las contraseñas no coinciden
            mensajeValidacion.textContent = 'Las contraseñas no coinciden';
            mensajeValidacion.className = 'mensaje-validacion no-coincide';
        }
    }

    // Agregar eventos para validar en tiempo real
    nuevaContra.addEventListener('input', validarContraseñas);
    confirmarContra.addEventListener('input', validarContraseñas);
});


// Función duplicada para mostrar/ocultar contraseña
function togglePassword(element) {
    const input = element.previousElementSibling;
    if (input.type === "password") {
        // Cambiar a texto visible
        input.type = "text";
        element.classList.remove("fa-eye");
        element.classList.add("fa-eye-slash");
    } else {
        // Cambiar a contraseña oculta
        input.type = "password";
        element.classList.remove("fa-eye-slash");
        element.classList.add("fa-eye");
    }
}
</script>
</body>
</html>