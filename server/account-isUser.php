<?php
function isUser ($user) {
    try {
        $bdd = new PDO("mysql:host=$DBhost;dbname=$DBusersDB", $DBusername, $DBpassword);
    
        $check = $bdd->prepare('SELECT id_utilisateur FROM users WHERE email = ?');
        $check->execute([$user]);
    
        return $check ? 204 : 404;

    } catch (Exception $e) {
        Die('Error : ' . $e->getMessage());
    }
}
?>