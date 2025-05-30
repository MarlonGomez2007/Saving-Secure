<?php
// Iniciar sesión para acceder a las variables de sesión
session_start();

// Verificar si existe la variable de sesión 'correo'
// Si no existe, redirigir al usuario a la página de solicitud de correo
if (!isset($_SESSION['correo'])) {
    header("Location: solicitar_correo.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el código ingresado por el usuario
    $codigo_ingresado = $_POST['codigo'];

    // Verificar si el código ingresado coincide con el código almacenado en la sesión
    if ($codigo_ingresado == $_SESSION['codigo']) {
        // Si el código es correcto, marcar como verificado y redirigir a la página de cambio de contraseña
        $_SESSION['verificado'] = true;
        header("Location: cambiar_contra.php");
        exit();
    } else {
        // Si el código es incorrecto, mostrar mensaje de error
        echo "<script>alert('Código incorrecto.');</script>";
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <title>Verificar Código - Saving Secure</title>
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
                        <h4 class="pb-3" >Verificar Código</h4>
                        <div class="subtitle pb-3" style="color: white;">
                            Se ha enviado un código de verificación a tu correo electrónico
                        </div>
                        <form method="post">
                            <div class="form-group">
                                <input type="text" class="form-style" placeholder="Código de verificación" name="codigo" required style="color: white;">
                            </div>
                            <input type="submit" value="Verificar Código" class="btn mt-4" >
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>