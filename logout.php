<?php
session_start();
session_destroy();
header("Location: /hand2hand/login.php");
exit();
?>
