<?php
$servername = "localhost:3307";
$username = "root";
$password = "root123";
$dbname = "hand2hand";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname );
 
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//echo "Connected successfully";

?>