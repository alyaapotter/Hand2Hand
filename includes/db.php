<?php
// includes/connect.php atau includes/db.php
// Pastikan maklumat database melaka kau betul
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hand2hand"; // Ganti dengan nama DB kau yang betul

// 1. Guna cara global variable (Selesai error login/register/aid_request)
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Guna cara function (Selesai error beneficiary_page_admin/profile_page_bene)
if (!function_exists('getConnection')) {
    function getConnection() {
        global $conn;
        return $conn;
    }
}
?>