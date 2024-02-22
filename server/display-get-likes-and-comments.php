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
        Die("Error : " . $e->getMessage());
    }
}
?>