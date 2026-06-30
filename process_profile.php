<?php
session_start();
require_once 'includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $family_size = intval($_POST['family_size']);
    $priority = $_POST['priority'];

    $stmt = $conn->prepare("
        UPDATE user
        SET
            username = ?,
            contact_number = ?,
            address = ?,
            family_size = ?,
            priority_level = ?
        WHERE user_id = ?
    ");

    if (!$stmt) {
        $_SESSION['error_msg'] = $conn->error;
        header("Location: profile_page_bene.php");
        exit();
    }

    $stmt->bind_param(
        "sssisi",
        $username,
        $contact,
        $address,
        $family_size,
        $priority,
        $user_id
    );

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Profile successfully saved!";
    } else {
        $_SESSION['error_msg'] = "Failed to save profile.";
    }

    $stmt->close();

    header("Location: profile_page_bene.php");
    exit();
}
?>