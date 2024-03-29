<?php
require 'headers2.php';
require 'credentials.php';
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (isset($_COOKIE['token'])) {
    try {
        $key = '41554689';
        $algorithm = 'HS256';
        $user = JWT::decode($_COOKIE['token'], new Key($key, 'HS256'));
    
        if($user->role === 'ADMIN') {
            $bdd = new PDO("mysql:host=$DBhost;dbname=$usersDB", $usersDBusername, $usersDBpassword);
            $bdd->beginTransaction();

            $req1 = "SELECT DISTINCT COALESCE(m1.id_sender, m1.id_receiver) AS user,
            m1.id,
            m1.content AS last_message,
            m1.id_sender AS last_message_sender,
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
                AND m2.id > m1.id
                ORDER BY m2.id DESC
            )
            AND u.role != 'ADMIN'
            ORDER BY m1.id DESC";

            $req2 = "SELECT COALESCE(m1.id_sender, m1.id_receiver) AS user,
            m1.id,
            m1.content AS last_message,
            m1.id_sender AS last_message_sender,
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
            INNER JOIN (
                SELECT COALESCE(id_sender, id_receiver) AS user_id,
                MAX(id) AS max_id
                FROM messages
                GROUP BY COALESCE(id_sender, id_receiver)
            ) m2
            ON COALESCE(m1.id_sender, m1.id_receiver) = m2.user_id
            AND m1.id = m2.max_id
            WHERE  u.role != 'ADMIN'
            ORDER BY m1.id DESC";        

            $query = $bdd->query($req2);
        
            while($row = $query->fetch()) {
                $users[] = $row;
            }
        
            echo isset($users) ? json_encode($users) : 404;

            $bdd->commit();
        }
    }
    catch (Exception $e) {
        $errorMessage = "[" . date('Y-m-d H:i:s') . "] Erreur : " . $e->getMessage() . " dans le fichier " . $e->getFile() . " à la ligne " . $e->getLine() . " - Adresse IP : " . $_SERVER['REMOTE_ADDR'] . "\n";
        error_log($errorMessage, 3, "debug/error.log");
        
        Die("Erreur : " . $e->getMessage());
    }
} else {
    echo 401;
}
?>