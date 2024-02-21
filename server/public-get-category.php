<?php
// Autoriser l'accès depuis n'importe quelle origine
header("Access-Control-Allow-Origin: *");

// Autoriser les méthodes HTTP spécifiées
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Autoriser les en-têtes spécifiés
header("Access-Control-Allow-Headers: Content-Type");

// Indiquer si les en-têtes, méthodes et crédentiels spécifiés peuvent être exposés lors de la réponse aux requêtes clients
header("Access-Control-Expose-Headers: Content-Length, X-JSON");

// Définir le type de contenu pour la réponse
header("Content-Type: application/json");

if (isset($_POST['category'])) {
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=projects', 'sirdasilva', 'Jesus Seul');

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