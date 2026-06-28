<?php
// includes/db.php
// Database connection for Hand2Hand system

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hand2hand";
$port = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character encoding
$conn->set_charset("utf8");
