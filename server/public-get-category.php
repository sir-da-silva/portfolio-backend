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
        Die("500");
    }
}
?>