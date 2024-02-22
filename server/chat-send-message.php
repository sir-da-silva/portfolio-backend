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
        
        if (($user->role === 'CLIENT' && isset($_POST['new_message'])) || ($user->role === 'ADMIN' && isset($_POST['new_message']) && isset($_POST['id_receiver']))) {

            $bdd = new PDO('mysql:host=localhost;dbname=users', 'sirdasilva', 'Jesus Seul');
            $bdd->beginTransaction();

            if($user->role === 'CLIENT') {
                $ADMIN = $bdd->query("SELECT id_utilisateur FROM users WHERE role = 'ADMIN'");
                $ADMIN = $ADMIN->fetch();
            }

            $query = $bdd->prepare(
                "INSERT INTO messages (id_sender, id_receiver, content, time, state)
                VALUES (:id_sender, :id_receiver, :content, :time, :state)"
            );
            $query->execute(array(
                ':id_sender' => $user->id_utilisateur,
                ':id_receiver' => $user->role === 'CLIENT' ? $ADMIN['id_utilisateur'] : $_POST['id_receiver'],
                ':time' => time(),
                'content' => $_POST['new_message'],
                ':state' => 1
            ));

            echo 201;

            $bdd->commit();
        }
    }
    catch (Exception $e) {
        Die("Erreur : " . $e->getMessage());
    }
} else {
    echo 401;
}
?>