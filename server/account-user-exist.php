<?php
// Autoriser l'accès depuis n'importe quelle origine
header("Access-Control-Allow-Origin: http://localhost:5173");

// Autoriser les méthodes HTTP spécifiées
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Autoriser les en-têtes spécifiés
header("Access-Control-Allow-Headers: Content-Type");

// Indiquer que les cookies peuvent être inclus dans la demande
header('Access-Control-Allow-Credentials: true');

// Indiquer si les en-têtes, méthodes et crédentiels spécifiés peuvent être exposés lors de la réponse aux requêtes clients
header("Access-Control-Expose-Headers: Content-Length, X-JSON");

// Définir le type de contenu pour la réponse
header("Content-Type: application/json");

require "./account-user-exist-function.php";

if (isset($_POST['email']) && trim($_POST['email']) && preg_match('/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,6}$/', $_POST['email'])) {
    echo isUser($_POST['email']);
} else {
    return 400;
}

?>