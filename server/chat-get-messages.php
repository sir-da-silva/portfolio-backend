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
        
        if ($user->role === 'CLIENT' || ($user->role === 'ADMIN' && isset($_POST['CLIENT_ID']))) {

            $bdd = new PDO('mysql:host=localhost;dbname=users', 'sirdasilva', 'Jesus Seul');
            $bdd->beginTransaction();
            
            // recuperer l'en tete
            $HEAD = $user->role === 'ADMIN' ?
                $bdd->prepare("SELECT id_utilisateur, email, nom, prenom, photo, role FROM users WHERE id_utilisateur = ?"):
                $bdd->prepare("SELECT id_utilisateur, email, nom, prenom, photo, role FROM users WHERE role = ?");
            
            $user->role === 'ADMIN' ?
                $HEAD->execute([$_POST['CLIENT_ID']]):
                $HEAD->execute(['ADMIN']);

            $HEAD = $HEAD->fetch();

            // creer l'en tete
            $messages['head']['id_utilisateur'] = $HEAD['id_utilisateur'];
            $messages['head']['nom'] = $HEAD['nom'];
            $messages['head']['prenom'] = $HEAD['prenom'];
            $messages['head']['email'] = $HEAD['email'];
            $messages['head']['photo'] = $HEAD['photo'];
            $messages['head']['role'] = $HEAD['role'];
            $messages['body'] = [];

            // selectionner les messages
            $query = $bdd->prepare(
                "SELECT id, id_sender, id_receiver, content, time, state
                FROM messages
                WHERE id_sender IN (:id_user_1, :id_user_2)
                AND id_receiver IN (:id_user_1, :id_user_2)"
            );
            $query->execute([':id_user_1' => $user->id_utilisateur, ':id_user_2' => $HEAD['id_utilisateur']]);
        
            while($message = $query->fetch()) {
                $messages['body'][] = $message;
            }
            
            echo json_encode($messages);
            
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