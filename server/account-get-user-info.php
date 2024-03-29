<?php
require 'headers2.php';
require 'credentials.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if(isset($_COOKIE['token'])) {
    $key = '41554689';
    $algorithm = 'HS256';

    try {
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
        $bdd = new PDO("mysql:host=$DBhost;dbname=$usersDB", $usersDBusername, $usersDBpassword);

        $infos = $bdd->prepare("SELECT * FROM users WHERE id_utilisateur = ?");
        $infos->execute([$user->id_utilisateur]);
        $infos = $infos->fetch();

        echo json_encode($infos);

    } catch (Exception $e) {
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur : " . $e->getMessage() . " dans le fichier " . $e->getFile() . " à la ligne " . $e->getLine() . " - Adresse IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($errorMessage, 3, "debug/error.log");
        
        Die('Erreur : ' . $e->getMessage());
    }
    
} else {
    echo json_encode(null);
}
?>