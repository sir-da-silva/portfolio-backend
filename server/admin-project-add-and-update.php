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

        if (
            $user->role === 'ADMIN' &&
            isset($_POST['project']) &&
            isset($_POST['action']) &&
            (
                $_POST['action'] === 'ADD' || $_POST['action'] === 'UPDATE'
            )
        ) {

            $bdd = new PDO("mysql:host=$DBhost;dbname=$DBprojectsDB", $DBusername, $DBpassword);
            $bdd->beginTransaction();
            
            // Convertion du texte JSON en objet
            $project = json_decode($_POST['project']);
            
            // Verifier si les valeur obligatoire sont present
            if (
                count($project->images) &&
                $project->titre &&
                $project->description &&
                $project->competences &&
                $project->client_ou_contexte &&
                preg_match('/true/', json_encode($project->categories))) {

                   

                // creer un image de previsualisation
                function thumbnail($tmp_name, $extension, $width, $identifiant) {
                    switch ($extension) {
                        case 'jpeg': $image = imagecreatefromjpeg($tmp_name); break;
                        case 'jpg': $image = imagecreatefromjpeg($tmp_name); break;
                        case 'png' : $image = imagecreatefrompng($tmp_name); break;
                        case 'gif' : $image = imagecreatefromgif($tmp_name); break;
                        default: exit('415');
                    }
                    
                    // debut de traitement
                    $imageW = imagesx($image);
                    $imageH = imagesy($image);
                    
                    $miniW = $width;
                    $coefficient = $imageW / $miniW;
                    $miniH = $imageH / $coefficient;
                    
                    $miniImage = imagecreatetruecolor($miniW, $miniH);
                    imagecopyresampled($miniImage, $image, 0, 0, 0, 0, $miniW, $miniH, $imageW, $imageH);
                    imagepng($miniImage, "C:\\Users\\my pc\\Documents\\Sir Da Silva - Portfolio\\portfolio\\public\\images\\thumbnails\\" . $identifiant . ".png", 9);

                    imagedestroy($image);
                    imagedestroy($miniImage);
                }

                if ($_POST['action'] === 'UPDATE' && $project->identifiant) {
                    // verifier si le projet a mettre a jour existe
                    $check = $bdd->prepare("SELECT id FROM projects WHERE identifiant = ?");
                    $check->execute([$project->identifiant]);
                    $check = $check->fetch(PDO::FETCH_ASSOC);

                    if ($check) {
                        $query = $bdd->prepare("UPDATE projects SET images = :images, titre = :titre, description = :description, competences = :competences, client_ou_contexte = :client_ou_contexte, date_de_realisation = :date_de_realisation, objectifs_atteints = :objectifs_atteints, liens = :liens, tags = :tags, credits = :credits, categories = :categories WHERE identifiant = :identifiant");
                        $identifiant = $project->identifiant;
                    }
                    else {
                        echo 404;
                    } 

                } elseif ($_POST['action'] === 'ADD') {
                    // Créez la requête SQL INSERT avec des paramètres de liaison
                    $query = $bdd->prepare("INSERT INTO projects (images, titre, description, competences, client_ou_contexte, date_de_realisation, objectifs_atteints, liens, tags, credits, categories, identifiant) VALUES (:images, :titre, :description, :competences, :client_ou_contexte, :date_de_realisation, :objectifs_atteints, :liens, :tags, :credits, :categories, :identifiant)");
                    $identifiant = 'project_' . bin2hex(random_bytes(16));
                } else {
                    exit('400');
                }

                if (isset($query)) {
                    //enregistrer les images
                    for($i = 0, $l = count($project->images); $i < $l; $i++) {
                        if(isset($_FILES['images']['name'][$i])) {
                            if ($_FILES['images']['error'][$i] === 0 && getimagesize($_FILES['images']['tmp_name'][$i])) {

                                $chemin = "../../../../Users/my pc/Documents/Sir Da Silva - Portfolio/portfolio/public/images/projects/";
                                $tmp_name = $_FILES['images']['tmp_name'][$i];
                                $extension = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                                $new_name = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

                                $i === 0 && thumbnail($tmp_name, $extension, 400, $identifiant);
                                move_uploaded_file($tmp_name, $chemin . $new_name);

                                $images[] = $new_name;
                            } else {
                                exit('206');
                            }
                        } else {
                            $images[] = $_POST['images'][$i];
                        }
                    }

                    // parser les valeurs
                    $images = json_encode($images);
                    $titre = trim($project->titre);
                    $description = trim($project->description);
                    $competences = trim($project->competences);
                    $client_ou_contexte = trim($project->client_ou_contexte);
                    $date_de_realisation = $project->date_de_realisation;
                    $objectifs_atteints = trim($project->objectifs_atteints);
                    $liens = json_encode($project->liens);
                    $tags = trim($project->tags);
                    $credits = trim($project->credits);
                    $categories = json_encode($project->categories);

                    // Associez les valeurs aux paramètres de la requête
                    $query->bindParam(':images', $images);
                    $query->bindParam(':titre', $titre);
                    $query->bindParam(':description', $description);
                    $query->bindParam(':competences', $competences);
                    $query->bindParam(':client_ou_contexte', $client_ou_contexte);
                    $query->bindParam(':date_de_realisation', $date_de_realisation);
                    $query->bindParam(':objectifs_atteints', $objectifs_atteints);
                    $query->bindParam(':liens', $liens);
                    $query->bindParam(':tags', $tags);
                    $query->bindParam(':credits', $credits);
                    $query->bindParam(':categories', $categories);
                    $query->bindParam(':identifiant', $identifiant);

                    // Valider l'ajout
                    $query->execute();
                    echo 201;
                }
            }
            else {
                echo 400;
            }
        } else {
            echo 400;
        }
    } catch (Exception $e) {
        echo 401;
    }
} else {
    echo 401;
}
?>