<?php
require 'headers1.php';
require 'credentials.php';
require "./account-isUser.php";

if (isset($_POST['email']) && trim($_POST['email']) && preg_match('/^[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,6}$/', $_POST['email'])) {
    echo isUser($_POST['email']);
} else {
    return 400;
}
?>