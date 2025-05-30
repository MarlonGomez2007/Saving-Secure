<?php
// Iniciar la sesión actual
session_start();

// Destruir todos los datos de la sesión
session_destroy();

// Redirigir al usuario a la página de login
header("Location: login.html");

// Finalizar la ejecución del script
exit();
?>
