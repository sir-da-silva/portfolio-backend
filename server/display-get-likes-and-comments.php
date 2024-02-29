<?php
require 'headers1.php';
require 'credentials.php';
if (isset($_POST['identifiant'])) {
    try {
        $bdd = new PDO("mysql:host=$DBhost;dbname=$projectsDB", $projectsDBusername, $projectsDBpassword);
        $bdd->beginTransaction();

        $infos['likes'] = [];
        $infos["comments"] = [];

        $likes = $bdd->prepare( "SELECT id_utilisateur FROM likes WHERE id_projet = ?");
        $comments = $bdd->prepare(
            "SELECT c.id, c.contenue, c.time, u.nom, u.prenom, u.photo, u.id_utilisateur
            FROM projects.comments c
            INNER JOIN users.users u
            ON u.id_utilisateur = c.id_utilisateur
            WHERE c.id_projet = ?
            ORDER BY c.id DESC"
        );
        $likes->execute([$_POST['identifiant']]);
        $comments->execute([$_POST['identifiant']]);

        while($like = $likes->fetch()) {
            $infos['likes'][] = $like['id_utilisateur'];
        }
        while ($comment = $comments->fetch()) {
            $infos['comments'][] = $comment;
        }

        echo json_encode($infos);

        $bdd->commit();
    }
    catch (Exception $e) {
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur : " . $e->getMessage() . " dans le fichier " . $e->getFile() . " à la ligne " . $e->getLine() . " - Adresse IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($errorMessage, 3, "debug/error.log");
        
        Die("Error : " . $e->getMessage());
    }
}
?>