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
        
        if (isset($_POST['limite']) && is_numeric($_POST['limite']) && ($user->role === 'CLIENT' || $user->role === 'ADMIN')) {

            $bdd = new PDO("mysql:host=$DBhost;dbname=$usersDB", $usersDBusername, $usersDBpassword);
            $bdd->beginTransaction();

            if ($user->role === 'CLIENT') {
                $HEAD = $bdd->query("SELECT id_utilisateur FROM users WHERE role = 'ADMIN'");
                $HEAD = $HEAD->fetch();

                $query = $bdd->prepare(
                    "SELECT id, id_sender, id_receiver, content, time, state
                    FROM messages
                    WHERE id_sender IN (:id_user_1, :id_user_2)
                    AND id_receiver IN (:id_user_1, :id_user_2)
                    AND id > :limite"
                );
                $query->execute([
                    ':id_user_1' => $user->id_utilisateur,
                    ':id_user_2' => $HEAD['id_utilisateur'],
                    ':limite' => intval($_POST['limite'])
                ]);
            }

            if ($user->role === 'ADMIN') {
                $query = $bdd->prepare(
                    "SELECT id, id_sender, id_receiver, content, time, state
                    FROM messages
                    WHERE id > :limite"
                );
                $query->execute([
                    ':limite' => intval($_POST['limite'])
                ]);
            }
        
            while($body = $query->fetch()) {
                $messages[] = $body;
            }
            
            echo isset($messages) ? json_encode($messages) : 404;
            
            $bdd->commit();
        }
    }
    catch (Exception $e) {
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur : " . $e->getMessage() . " dans le fichier " . $e->getFile() . " à la ligne " . $e->getLine() . " - Adresse IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($errorMessage, 3, "debug/error.log");
        
        Die("Erreur : " . $e->getMessage());
    }
} else {
    echo 401;
}
?>