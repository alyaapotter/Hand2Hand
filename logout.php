<?php
session_start();

// buang semua session
session_unset();
session_destroy();

// redirect balik login page
header("Location: login.php");
exit;
