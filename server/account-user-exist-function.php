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

function isUser ($user) {
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=users', 'sirdasilva', 'Jesus Seul');
    
        $check = $bdd->prepare('SELECT id_utilisateur FROM users WHERE email = ?');
        $check->execute([$user]);
        $check = $check->fetch();

        return $check ? 204 : 404;

    } catch (Exception $e) {
        Die('Error : ' . $e->getMessage());
    }
}
?>