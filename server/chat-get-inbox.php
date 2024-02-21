<?php
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

require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (isset($_COOKIE['token'])) {
    try {
        $key = '41554689';
        $algorithm = 'HS256';
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
    
        $bdd = new PDO('mysql:host=localhost;dbname=users', 'sirdasilva', 'Jesus Seul');
        $bdd->beginTransaction();
        
        if($user->role === 'ADMIN') {
    
            $query = $bdd->query(
                "SELECT DISTINCT COALESCE(m1.id_sender, m1.id_receiver) AS user,
                m1.content AS last_message,
                m1.time AS last_time,
                m1.state AS state,
                u.id_utilisateur AS id_utilisateur,
                u.nom AS nom,
                u.prenom AS prenom,
                u.email AS email,
                u.photo AS photo
                FROM messages m1
                INNER JOIN users u
                ON COALESCE(m1.id_sender, m1.id_receiver) = u.id_utilisateur
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM messages m2
                    WHERE COALESCE(m1.id_sender, m1.id_receiver) = COALESCE(m2.id_sender, m2.id_receiver)
                    AND m2.id < m1.id
                )
                AND u.role != 'ADMIN'
                ORDER BY m1.id DESC"
            );
        
            while($row = $query->fetch()) {
                $users[] = $row;
            }
        
            echo isset($users) ? json_encode($users) : 404;
        }
    
        $bdd->commit();
    }
    catch (Exception $e) {
        Die("Erreur : " . $e->getMessage());
    }
} else {
    echo 401;
}
?>