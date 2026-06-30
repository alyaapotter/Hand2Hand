<?php
// includes/db.php
// Database connection for Hand2Hand system

$host = "localhost";
$port = "3301"; // Default MySQL port — change to 3307 if connection still fails
$dbname = "hand2hand";
$username = "root";
$password = ""; // Change this to your MySQL password if needed

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>