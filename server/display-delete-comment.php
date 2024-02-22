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

if (isset($_COOKIE['token'])) {
    try {
        $key = '41554689';
        $algorithm = 'HS256';
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));        
        
        if ($_POST['id_comment'] && $_POST['id_projet']) {
    
            $bdd = new PDO('mysql:host=localhost;dbname=projects', 'sirdasilva', 'Jesus Seul');
            $bdd->beginTransaction();
            
            if ($user->role === 'ADMIN') {
                $delete = $bdd->prepare( "DELETE FROM comments WHERE id = ? AND id_projet = ?");
                $delete->execute([$_POST['id_comment'], $_POST['id_projet']]);
                echo 201;
            } else if ($user->role === 'CLIENT') {
                $delete = $bdd->prepare("DELETE FROM comments WHERE id = ? AND id_projet = ? AND id_utilisateur = ? ");
                $delete->execute([$_POST['id_comment'], $_POST['id_projet'], $user->id_utilisateur]);
                echo 201;
            }
    
            $bdd->commit();
        } else {
            echo 400;
        }
    }
    catch (Exception $e) {
        Die("Erreur : " . $e->getMessage());
    }
} else {
    echo 401;
}
?>