<?php
session_start();
require_once 'config/google.php';
require_once 'db.php';

if (!isset($_GET['code'])) {
    header('Location: login.html');
    exit();
}

$client = getGoogleClient();
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    echo 'Error al autenticar con Google.';
    exit();
}

$client->setAccessToken($token['access_token']);

// Obtener datos básicos
$oauth2 = new Google_Service_Oauth2($client);
$userInfo = $oauth2->userinfo->get();

// Obtener datos adicionales (fecha de nacimiento y número)
$peopleService = new Google_Service_PeopleService($client);
$profile = $peopleService->people->get('people/me', [
    'personFields' => 'birthdays,phoneNumbers'
]);

$birthday = null;
if (!empty($profile->getBirthdays())) {
    $b = $profile->getBirthdays()[0]->getDate();
    $birthday = $b['year'] . '-' . str_pad($b['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($b['day'], 2, '0', STR_PAD_LEFT);
}

$phone = null;
if (!empty($profile->getPhoneNumbers())) {
    $phone = $profile->getPhoneNumbers()[0]->getValue();
}

$email = $userInfo->email;
$nombre = $userInfo->name;
$foto = $userInfo->picture;
$google_id = $userInfo->id;

// Buscar usuario por correo
$stmt = $conn->prepare('SELECT * FROM usuarios WHERE correo = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    
    // Actualiza google_id si está vacío
    if (empty($usuario['google_id'])) {
        $stmt_update = $conn->prepare('UPDATE usuarios SET nombre = ?, foto_perfil = ?, google_id = ?, fecha_nacimiento = ?, numero = ? WHERE id_usuario = ?');
        $stmt_update->bind_param('sssssi', $nombre, $foto, $google_id, $birthday, $phone, $usuario['id_usuario']);
        $stmt_update->execute();
    }
    
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['rol'] = $usuario['rol'];
    $rol = (int)$usuario['rol'];
} else {
    // Insertar usuario nuevo
    $rol = 1; // Default role for new users
    $stmt_insert = $conn->prepare('INSERT INTO usuarios (nombre, correo, foto_perfil, google_id, fecha_nacimiento, numero, rol, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
    $stmt_insert->bind_param('ssssssi', $nombre, $email, $foto, $google_id, $birthday, $phone, $rol);
    $stmt_insert->execute();
    
    $_SESSION['id_usuario'] = $stmt_insert->insert_id;
    $_SESSION['rol'] = $rol;
}

// Redirigir según el rol - FIXED: Now allows role 2 (admin) to log in
if ($rol == 2) { // Changed from === to == for more flexible comparison and fixed the logic
    header('Location: panel_admin.php');
} else {
    header('Location: dashboard.php');
}
exit();
?>