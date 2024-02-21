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

if(isset($_COOKIE['token'])) {
    try {
        $key = '41554689';
        $algorithm = 'HS256';
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));

        if ($user->role === 'ADMIN' && isset($_POST['identifiant'])) {

            $bdd = new PDO('mysql:host=localhost;dbname=projects', 'sirdasilva', 'Jesus Seul');
            $bdd->beginTransaction();

            $images = $bdd->prepare('SELECT images FROM projects WHERE identifiant = ?');
            $images->execute([$_POST['identifiant']]);
            $images = $images->fetch();
            $images = json_decode($images['images']);

            $project = $bdd->prepare("DELETE FROM projects WHERE identifiant = ?");
            $comments = $bdd->prepare("DELETE FROM comments WHERE id_projet = ?");
            $likes = $bdd->prepare("DELETE FROM likes WHERE id_projet = ?");
            $project->execute([$_POST['identifiant']]);
            $comments->execute([$_POST['identifiant']]);
            $likes->execute([$_POST['identifiant']]);

            $thumbnail = "C:\\Users\\my pc\\Documents\\Sir Da Silva - Portfolio\\portfolio\\public\\images\\thumbnails\\" . $_POST['identifiant'] . ".jpeg";
            file_exists($thumbnail) && (unlink($thumbnail));

            foreach($images as $cle => $image) {
                $image = "C:\\Users\\my pc\\Documents\\Sir Da Silva - Portfolio\\portfolio\\public\\images\\projects\\" . $image;
                file_exists($image) && (unlink($image));
            }

            echo 204;

            $bdd->commit();
        } else {
            echo 401;
        }
    } catch (Exception $e) {
        Die ("Error : " . $e->getMessage());
    }
} else {
    echo 401;
}
?>