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

require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if(isset($_COOKIE['token'])) {
    $key = '41554689';
    $algorithm = 'HS256';

    try {
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
        
        echo json_encode($user);

    } catch (Exception $e) {
        Die('Erreur : ' . $e->getMessage());
    }
    
} else {
    echo json_encode(null);
}
?>