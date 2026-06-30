<?php
session_start();
require_once 'includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: donor/profile_page_donor.php');
    exit();
}

$user_id = $_POST['user_id'];
$current_password = trim($_POST['current_password']);
$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

if ($new_password !== $confirm_password) {
    $_SESSION['error_msg'] = "New password and confirm password do not match.";
    header('Location: donor/profile_page_donor.php');
    exit();
}

if (strlen($new_password) < 6) {
    $_SESSION['error_msg'] = "New password must be at least 6 characters.";
    header('Location: donor/profile_page_donor.php');
    exit();
}

/* Check current password */
$stmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_msg'] = "User not found.";
    header('Location: donor/profile_page_donor.php');
    exit();
}

$user = $result->fetch_assoc();

if ($current_password !== $user['password']) {
    $_SESSION['error_msg'] = "Current password is incorrect.";
    header('Location: donor/profile_page_donor.php');
    exit();
}

$stmt->close();

/* Update password */
$update = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
$update->bind_param("si", $new_password, $user_id);

if ($update->execute()) {
    $_SESSION['success_msg'] = "Password updated successfully.";
} else {
    $_SESSION['error_msg'] = "Failed to update password.";
}

$update->close();
$conn->close();

header('Location: donor/profile_page_donor.php');
exit();
?>
