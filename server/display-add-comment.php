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
        
        if ((isset($_POST['identifiant']) && isset($_POST['comment']))) {
    
            $bdd = new PDO("mysql:host=$DBhost;dbname=$projectsDB", $projectsDBusername, $projectsDBpassword);
            $bdd->beginTransaction();

            $comment = $bdd->prepare(
                "INSERT INTO comments (id_utilisateur, id_projet, contenue, time )
                VALUES (:id_utilisateur, :id_projet, :contenue, :time)"
            );
            $comment->execute(array(
                ':id_utilisateur' => $user->id_utilisateur,
                ':id_projet' => $_POST['identifiant'],
                ':contenue' => $_POST['comment'],
                ':time' => time()
            ));
            echo 201;
    
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