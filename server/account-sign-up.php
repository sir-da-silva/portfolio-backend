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

require "./account-user-exist-function.php";
require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;

if(isset($_POST['infos'])) {
    $infos = json_decode($_POST['infos']);

    if (!isset($infos->email) || !trim($infos->email))
    {
        echo 400;
        //echo 'Veuillez entrez votre email';
    }
    else if (!preg_match('/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,6}$/', $infos->email))
    {
        echo 400;
        //echo 'Email incorecte';
    }
    else if (!isset($infos->nom) || !trim($infos->nom))
    {
        echo 400;
        //echo 'Veuillez entrer votre nom';
    }
    else if (!preg_match('/^[\w\s-]+$/', $infos->nom))
    {
        echo 400;
        //echo 'Le nom contient des caractere interdit';
    }
    else if (!isset($infos->prenom) || !trim($infos->prenom))
    {
        echo 400;
        //echo 'Veuillez entrer votre prenom';
    }
    else if (!preg_match('/^[\w\s-]+$/', $infos->prenom))
    {
        echo 400;
        //echo 'Le prenom contient des caractere interdit';
    }
    else if (!isset($infos->password) || !$infos->password)
    {
        echo 400;
        //echo 'Veuillez entrer un mot de passe';
    }
    else if (strlen($infos->password) < 8)
    {
        echo 400;
        //echo 'Ce mot de passe est trop court';
    }
    else if (!isset($infos->password2) || !$infos->password2)
    {
        echo 400;
        //echo 'Veuillez ressaisir le mot de passe';
    }
    else if ($infos->password != $infos->password2)
    {
        echo 400;
        //echo 'Ce mot de passe est different du premier';
    }
    else
    {
        try {
            $bdd = new PDO('mysql:host=localhost;dbname=users', 'sirdasilva', 'Jesus Seul');
            
            //verification
            if (isUser($infos->email) === 204) {
                echo isUser($infos->email);
            } else {
                $bdd->beginTransaction();

                // prepare
                $user = $bdd->prepare('INSERT INTO users (id_utilisateur, nom, prenom, email, role) VALUE(:id_utilisateur, :nom, :prenom, :email, :role)');
                $password = $bdd->prepare('INSERT INTO passwords (id_utilisateur, mot_de_passe, sel) VALUE(:id_utilisateur, :mot_de_passe, :sel)');

                // generate data
                $id_user = 'user_' . bin2hex(random_bytes(16));
                $sel = bin2hex(random_bytes(8));
                $password_hashed = hash('sha256', $infos->password . $sel);
                $role = 'CLIENT';

                // bindparams
                $user->bindParam(':id_utilisateur', $id_user);
                $user->bindParam(':nom', $infos->nom);
                $user->bindParam(':prenom', $infos->prenom);
                $user->bindParam(':email', $infos->email);
                $user->bindParam(':role', $role);

                $password->bindParam(':id_utilisateur', $id_user);
                $password->bindParam(':mot_de_passe', $password_hashed);
                $password->bindParam(':sel', $sel);

                // execute
                $user->execute();
                $password->execute();

                // automatic connection
                $infos = $bdd->prepare("SELECT * FROM users WHERE id_utilisateur = ?");
                $infos->execute([$id_user]);
                $infos = $infos->fetch();

                $key = '41554689';
                $algorithm = 'HS256';

                foreach($infos as $name => $info) {
                    $payload[$name] = $info;
                }

                $jwt = JWT::encode($payload, $key, $algorithm);

                setcookie('token', $jwt, time() + 3600, null, null, false, true);
                
                echo 200;
                $bdd->commit();
            }
            
        } catch (Exception $e) {
            Die('Error : ' . $e->getMessage());
        }
    }

} else {
    echo 400;
}
?>