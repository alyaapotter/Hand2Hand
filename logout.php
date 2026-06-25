<?php
session_start();
session_destroy();
header("Location: /h2h_final/login.php");
exit();
?>
