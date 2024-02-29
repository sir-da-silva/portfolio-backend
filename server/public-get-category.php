<?php
require 'headers1.php';
require 'credentials.php';
if (isset($_POST['category'])) {
    try {
        $bdd = new PDO("mysql:host=$DBhost;dbname=$projectsDB", $projectsDBusername, $projectsDBpassword);

        $query = $bdd->prepare("SELECT identifiant, titre FROM projects WHERE JSON_CONTAINS(categories, JSON_OBJECT(?, true))");
        $query->execute([$_POST['category']]);
            
        while($row = $query->fetch()) {$data[] = $row;}
            
        echo isset($data) ? json_encode($data) : 404;
    }
    catch (Exception $e) {
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur : " . $e->getMessage() . " dans le fichier " . $e->getFile() . " à la ligne " . $e->getLine() . " - Adresse IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($errorMessage, 3, "debug/error.log");
        
        Die("500");
    }
}
?>