<?php
// process_profile.php
session_start();
// Sambung terus ke connect.php guna path yang betul
require_once '../includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id     = intval($_POST['user_id']);
    $name        = trim($_POST['name']);
    $contact     = trim($_POST['contact']);
    $address     = trim($_POST['address']);
    $family_size = intval($_POST['family_size']);
    $priority    = $_POST['priority'];

    // Check sama ada profile beneficiary ini sudah wujud atau belum guna $conn
    $check_stmt = $conn->prepare("SELECT beneficiary_id FROM beneficiaries WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Jika wujud, lakukan UPDATE
        $stmt = $conn->prepare("UPDATE beneficiaries SET name = ?, contact = ?, address = ?, family_size = ?, priority = ? WHERE user_id = ?");
        $stmt->bind_param("sssisi", $name, $contact, $address, $family_size, $priority, $user_id);
    } else {
        // Jika belum wujud, lakukan INSERT baru
        $stmt = $conn->prepare("INSERT INTO beneficiaries (user_id, name, contact, address, family_size, priority) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssis", $user_id, $name, $contact, $address, $family_size, $priority);
    }

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Profile successfully saved!";
    } else {
        $_SESSION['error_msg'] = "Failed to save profile. Please try again.";
    }

    $stmt->close();
    $check_stmt->close();
    $conn->close();

    // Balik semula ke page profile
    header("Location: profile_page_bene.php");
    exit();
}