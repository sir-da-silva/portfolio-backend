<?php
session_start();

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

$password = "979d9bddb3ac445febfc438c6f861c1c0caa6833dab54e51072da38d88babc03";
$sessionKey = "t47kscCQ5XZijtWXbXd5OQ0g281nqYRC";

$ifaccess = isset($_SESSION['sessionKey']) && $_SESSION['sessionKey'] === $sessionKey;
$iftryaccess = !isset($_SESSION['sessionKey']) && isset($_POST['password']);
$ifaction = isset($_POST['action']);
$ifadd = $ifaction && $_POST['action'] === 'add' && isset($_POST['project']) ;
$ifupdate = $ifaction && $_POST['action'] === 'update' && isset($_POST['project']);
$ifdelete = $ifaction && $_POST['action'] === 'delete' && isset($_POST['identifiant']);

# 200 Authetifié avec succès
# 201 Crée ou modifié avec succès
# 204 Supprimé avec succès
# 206 Image incomplètement chargé
# 400 Données ou paramètre manquants
# 401 Non authentifé
# 404 Non trouvé
# 415 Format de l'image primaire non accepté

if ($ifaccess) {
    if ($ifaction) {
        try {
            $bdd = new PDO('mysql:host=localhost;dbname=projects', 'sirdasilva', 'Jesus Seul');

            $bdd->beginTransaction();

            if ($ifadd || $ifupdate) {
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
                            case 'jpeg' :
                                $image = imagecreatefromjpeg($tmp_name);
                                break;
                            case 'png' :
                                $image = imagecreatefrompng($tmp_name);
                                break;
                            case 'gif' :
                                $image = imagecreatefromgif($tmp_name);
                                break;
                            default:
                                exit(415);
                        }
                        
                        // debut de traitement
                        $imageW = imagesx($image);
                        $imageH = imagesy($image);
                        
                        $miniW = $width;
                        $coefficient = $imageW / $miniW;
                        $miniH = $imageH / $coefficient;
                        
                        $miniImage = imagecreatetruecolor($miniW, $miniH);
                        
                        imagecopyresampled($miniImage, $image, 0, 0, 0, 0, $miniW, $miniH, $imageW, $imageH);
                    
                        imagepng($miniImage, "C:\\Users\\my pc\\Documents\\Sir Da Silva - Portfolio\\portfolio\\public\\images\\thumbnails\\" . $identifiant . ".png", 100);

                        imagedestroy($image);
                        imagedestroy($miniImage);
                    }

                    if ($ifupdate && $project->identifiant) {
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

                    } else if ($ifadd) {
                        // Créez la requête SQL INSERT avec des paramètres de liaison
                        $query = $bdd->prepare("INSERT INTO projects (images, titre, description, competences, client_ou_contexte, date_de_realisation, objectifs_atteints, liens, tags, credits, categories, identifiant) VALUES (:images, :titre, :description, :competences, :client_ou_contexte, :date_de_realisation, :objectifs_atteints, :liens, :tags, :credits, :categories, :identifiant)");
    
                        $identifiant = uniqid() . bin2hex(random_bytes(16));
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
    
                                    $images[] = 'images/projects/' . $new_name;
                                } else {
                                    exit(206);
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
                        try {
                            $query->execute();

                            echo 201;
                        } catch (Exception $e) {Die ("Erreur : " . $e->getMessage());}
                    }
                }
                else {
                    echo 400;
                }

            } elseif ($ifdelete) {
                $query = $bdd->prepare("DELETE FROM project WHERE identifiant = ?");
                $query->execute([$_POST['identifiant']]);

                $thumbnail = "C:\\Users\\my pc\\Documents\\Sir Da Silva - Portfolio\\portfolio\\public\\images\\thumbnails\\" . $_POST['identifiant'] . ".jpeg";
                file_exists($thumbnail) && (unlink($thumbnail));

                echo 204;
            }
            else {
                echo 400;
            }

            $bdd->commit();
        } 
        catch (Exception $e) {Die ("Erreur : " . $e->getMessage());}
    }
    else {
        echo 200;
    }

} elseif ($iftryaccess) {
    if (hash('sha256', $_POST['password']) === $password) {

        $_SESSION['sessionKey'] = $sessionKey;
        echo 200;
    }
    else {
        echo 401;
    }
}
else {
    echo 401;
}
?>
