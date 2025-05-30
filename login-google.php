<?php
require_once 'config/google.php';

$client = getGoogleClient();
$auth_url = $client->createAuthUrl();
header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
exit; 