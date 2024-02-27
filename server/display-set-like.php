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
        
        if ((isset($_POST['identifiant']))) {
    
            $bdd = new PDO("mysql:host=$DBhost;dbname=$projectsDB", $projectsDBusername, $projectsDBpassword);
            $bdd->beginTransaction();
            
            $like = $bdd->prepare("SELECT id_utilisateur FROM likes WHERE id_projet = ? AND id_utilisateur = ? ");
            $like->execute([$_POST['identifiant'], $user->id_utilisateur]);
            $like = $like->fetch();

            if ($like) {
                $remove = $bdd->prepare("DELETE FROM likes WHERE id_projet = ? AND id_utilisateur = ?");
                $remove->execute([$_POST['identifiant'], $user->id_utilisateur]);
                echo 201;
            } else {
                $set = $bdd->prepare("INSERT INTO likes (id_projet, id_utilisateur) VALUE (?, ?)");
                $set->execute([$_POST['identifiant'], $user->id_utilisateur]);
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