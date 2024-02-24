<?php
require 'headers2.php';
setcookie('token', '', time() - 3600, null, null, false, true);
echo 201;
?>