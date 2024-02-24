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
        
        if (($user->role === 'CLIENT' && isset($_POST['new_message'])) || ($user->role === 'ADMIN' && isset($_POST['new_message']) && isset($_POST['id_receiver']))) {

            $bdd = new PDO("mysql:host=$DBhost;dbname=$DBusersDB", $DBusername, $DBpassword);
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
                ':content' => $_POST['new_message'],
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