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

if(isset($_POST['email']) && isset($_POST['password'])) {
    sleep(1);

    try {
        $bdd = new PDO('mysql:host=localhost;dbname=users', 'sirdasilva', 'Jesus Seul');

        // verification
        $check = $bdd->prepare("SELECT p.mot_de_passe, p.sel
        FROM passwords p
        INNER JOIN users u
        ON u.id_utilisateur = p.id_utilisateur
        WHERE u.email = ?");
        $check->execute([$_POST['email']]);
        $check = $check->fetch();

        if ($check) {
            if(hash('sha256', $_POST['password'] . $check['sel']) === $check['mot_de_passe']) {
                // requette
                $infos = $bdd->prepare("SELECT * FROM users WHERE email = ?");
                $infos->execute([$_POST['email']]);
                $infos = $infos->fetch();

                $key = '41554689';
                $algorithm = 'HS256';

                foreach($infos as $name => $info) {
                    $payload[$name] = $info;
                }

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

