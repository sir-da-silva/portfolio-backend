<?php
require 'headers2.php';
require 'credentials.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if(isset($_COOKIE['token'])) {
    try {
        $key = '41554689';
        $algorithm = 'HS256';
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));

        if ($user->role === 'ADMIN' && isset($_POST['identifiant'])) {

            $bdd = new PDO("mysql:host=$DBhost;dbname=$projectsDB", $projectsDBusername, $projectsDBpassword);
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