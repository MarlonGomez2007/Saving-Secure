<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.html');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT * FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

$error = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $numero = $_POST['numero'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $biografia = $_POST['biografia'];
    $foto_perfil = $usuario['foto_perfil'];

    // Validar y procesar subida de nueva foto
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto_perfil']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $check = getimagesize($tmp_name);
        if (!$check) {
            $error = 'El archivo no es una imagen válida.';
        } elseif (!in_array($ext, $permitidas)) {
            $error = 'Solo se permiten imágenes JPG, JPEG, PNG, GIF o WEBP.';
        } elseif ($_FILES['foto_perfil']['size'] > $max_size) {
            $error = 'La imagen no debe superar los 2MB.';
        } else {
            // Eliminar la foto anterior si no es la predeterminada
            if ($foto_perfil && $foto_perfil !== 'assets/default_profile.png' && file_exists($foto_perfil)) {
                unlink($foto_perfil);
            }
            $foto_perfil = 'assets/perfiles/' . uniqid() . '.' . $ext;
            move_uploaded_file($tmp_name, $foto_perfil);
        }
    }

    if (!$error) {
        $stmt_update = $conn->prepare('UPDATE usuarios SET nombre = ?, numero = ?, fecha_nacimiento = ?, direccion = ?, biografia = ?, foto_perfil = ? WHERE id_usuario = ?');
        $stmt_update->bind_param('ssssssi', $nombre, $numero, $fecha_nacimiento, $direccion, $biografia, $foto_perfil, $id_usuario);
        $stmt_update->execute();
        header('Location: perfil.php?actualizado=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>
    <style>
        .perfil-container { max-width: 500px; margin: 30px auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; }
        .perfil-foto { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; }
        .perfil-label { font-weight: bold; }
        .perfil-form input, .perfil-form textarea { width: 100%; margin-bottom: 10px; padding: 8px; }
        .perfil-form button { padding: 10px 20px; }
        .error-msg { color: red; font-weight: bold; }
        .success-msg { color: green; font-weight: bold; }
    </style>
</head>
<body>
<div class="perfil-container">
    <h2>Mi Perfil</h2>
    <?php if ($error): ?>
        <p class="error-msg"><?php echo $error; ?></p>
    <?php endif; ?>
    <form class="perfil-form" method="POST" enctype="multipart/form-data">
        <img src="<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'assets/default_profile.png'); ?>" class="perfil-foto" alt="Foto de perfil"><br>
        <label class="perfil-label">Cambiar foto de perfil:</label>
        <input type="file" name="foto_perfil" accept="image/*"><br>

        <label class="perfil-label">Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required><br>

        <label class="perfil-label">Correo:</label>
        <input type="email" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled><br>

        <label class="perfil-label">Número:</label>
        <input type="text" name="numero" value="<?php echo htmlspecialchars($usuario['numero']); ?>"><br>

        <label class="perfil-label">Fecha de nacimiento:</label>
        <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>"><br>

        <label class="perfil-label">Dirección:</label>
        <input type="text" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion']); ?>"><br>

        <label class="perfil-label">Biografía:</label>
        <textarea name="biografia"><?php echo htmlspecialchars($usuario['biografia']); ?></textarea><br>

        <button type="submit">Guardar cambios</button>
    </form>
    <?php if (isset($_GET['actualizado'])): ?>
        <p class="success-msg">¡Perfil actualizado correctamente!</p>
    <?php endif; ?>
</div>
</body>
</html> 