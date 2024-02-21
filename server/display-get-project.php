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

if (isset($_POST['identifiant'])) {
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=projects', 'sirdasilva', 'Jesus Seul');

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
        Die("500");
    }
}
?>