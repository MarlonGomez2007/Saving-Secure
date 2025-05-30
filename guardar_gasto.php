<?php
// Iniciar sesión para acceder a las variables de sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit();
}

// Procesar el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Obtener datos del formulario
    $gasto = $_POST['gasto'];
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $categoria = $_POST['categoria'];
    
    // Convertir el monto a entero
    $monto = intval($monto); 
    
    // Obtener fecha y hora actuales
    $fecha = date('Y-m-d H:i:s');
    $hora = date('H:i:s');  
    
    require_once 'db.php';

    // Crear conexión a la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar si hay error en la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Preparar y ejecutar la consulta SQL para insertar el gasto
    $sql = "INSERT INTO gastos (id_usuario, nombre, descripcion, monto, fecha, hora, categoria) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsss", $_SESSION['id_usuario'], $gasto, $descripcion, $monto, $fecha, $hora, $categoria);

    // Verificar si la inserción fue exitosa
    if ($stmt->execute()) {
      // Mostrar mensaje de éxito con SweetAlert2
        echo "<!DOCTYPE html>
              <html lang='es'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>Gasto Guardado</title>
                  <link rel='icon' href='assets/img/favicon.ico' type='image/x-icon'>
                  <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                  <style>
                    .btn-aceptar {
                        background-color: #f0b700;
                        border: none;
                        color: white;
                        padding: 10px 20px;
                        font-size: 16px;
                        border-radius: 5px;
                    }

                    .btn-aceptar:hover {
                        background-color: #e0a500;
                    }

                    
                    </style>
              </head>
              <body>
                  <script>
                    Swal.fire({
                        title: '¡Gasto Guardado!',
                        text: 'El gasto se guardó correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                        confirmButton: 'btn-aceptar' 
                        }
                    }).then(() => {
                        window.location.href = 'dashboard.php'; // Redirigir al dashboard
                    });
                    </script>
              </body>
              </html>";
    } else {
        // Mostrar mensaje de error
        echo "Error al guardar el gasto: " . $stmt->error;
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
}
?>
