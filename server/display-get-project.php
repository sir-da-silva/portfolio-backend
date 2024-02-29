<?php
require 'headers1.php';
require 'credentials.php';
if (isset($_POST['identifiant'])) {
    try {
        $bdd = new PDO("mysql:host=$DBhost;dbname=$projectsDB", $projectsDBusername, $projectsDBpassword);

        $query = $bdd->prepare("SELECT * FROM projects WHERE identifiant = ?");
        $query->execute([$_POST['identifiant']]);

        $project = $query->fetch();

        if ($project) {
            $project['images'] = json_decode($project['images']);
            $project['liens'] = json_decode($project['liens']);
            $project['categories'] = json_decode($project['categories']);

            echo json_encode($project);
        } else {
            echo 404;
        }
    }
    catch (Exception $e) {
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur : " . $e->getMessage() . " dans le fichier " . $e->getFile() . " à la ligne " . $e->getLine() . " - Adresse IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($errorMessage, 3, "debug/error.log");
        
        Die("500");
    }
}
?>