<?php
require 'headers2.php';
require 'credentials.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (isset($_COOKIE['token'])) {
    try {
        $key = '41554689';
        $algorithm = 'HS256';
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
        
        if ($user->role === 'CLIENT' || ($user->role === 'ADMIN' && isset($_POST['CLIENT_ID']))) {

            $bdd = new PDO("mysql:host=$DBhost;dbname=$DBusersDB", $DBusername, $DBpassword);
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