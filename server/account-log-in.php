<?php
require 'headers2.php';
require 'credentials.php';
require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;

if(isset($_POST['email']) && isset($_POST['password'])) {
    sleep(1);

    try {
        $bdd = new PDO("mysql:host=$DBhost;dbname=$DBusersDB", $DBusername, $DBpassword);

        // verification
        $check = $bdd->prepare("SELECT u.id_utilisateur, p.mot_de_passe, p.sel
        FROM passwords p
        INNER JOIN users u
        ON u.id_utilisateur = p.id_utilisateur
        WHERE u.email = ?");
        $check->execute([$_POST['email']]);
        $check = $check->fetch();

        if ($check) {
            if(hash('sha256', $_POST['password'] . $check['sel']) === $check['mot_de_passe']) {
                // requette
                $infos = $bdd->prepare("SELECT * FROM users WHERE id_utilisateur = ?");
                $infos->execute([$check['id_utilisateur']]);
                $infos = $infos->fetch();

                foreach($infos as $name => $info) {
                    $payload[$name] = $info;
                }

                $key = '41554689';
                $algorithm = 'HS256';

                $jwt = JWT::encode($payload, $key, $algorithm);

                setcookie('token', $jwt, time() + 3600 * 24 * 30, null, null, false, true);

                echo 200;
                
            } else {
                echo 401;
            }
        } else {
            echo 404;
        }
    }
    catch (Exception $e) {
        Die ("Erreur : " . $e->getMessage());
    }
} else {
    echo 400;
}
?>

