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
        
        if ($_POST['id_comment'] && $_POST['id_projet']) {
    
            $bdd = new PDO("mysql:host=$DBhost;dbname=$projectsDB", $projectsDBusername, $projectsDBpassword);
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
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur : " . $e->getMessage() . " dans le fichier " . $e->getFile() . " à la ligne " . $e->getLine() . " - Adresse IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($errorMessage, 3, "debug/error.log");
        
        Die("Erreur : " . $e->getMessage());
    }
} else {
    echo 401;
}
?>