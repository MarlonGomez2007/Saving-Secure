<?php
require_once __DIR__ . '/../vendor/autoload.php';

function getGoogleClient() {
    $client = new Google_Client();
    $client->setClientId('245615849358-mdjj1m2nfjh8jl6tfc5pde4347mt3o0m.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('https://savingsecure.site/google-callback.php');
    $client->addScope('email');
    $client->addScope('profile');
    $client->addScope('https://www.googleapis.com/auth/user.birthday.read');
    $client->addScope('https://www.googleapis.com/auth/user.phonenumbers.read');
    return $client;
}
?> 