<?php
// donor/home_page_donor.php
session_start();
// Sambung dengan fail connect.php kau yang dah berpassword "root123"
require_once '../includes/connect.php'; 

// Sekatan akses untuk Donor sahaja
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hand2Hand - Home (Donor)</title>
    <link rel="stylesheet" type="text/css" href="../css/home_page_donor.css">
</head>
<body>

<header class="donor-header">
    <div class="top-header">
        <div class="logo">
            <img src="../image/logo.png" alt="Hand2Hand logo" class="logo-circle" width="80">
            <h1>Hand2Hand</h1>
        </div>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>

    
        <a href="donor/home_page_donor.php" class="active">Home</a> |
        <a href="donor/donation_event_donor.php">My Events</a> |
        <a href="donor/donate_item.php">My Donations</a>
    
</header>

<main class="content-container">
    <h2 class="welcome-title">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Donor') ?>!</h2>

    <section class="events-section">
        <h3 class="section-title">Active Donation Events</h3>
        
        <div class="events-grid">
            <div class="event-card"><div class="image-placeholder"><img src="../image/food.webp" alt="Food Bank"></div><p class="event-label">Food Bank</p></div>
            <div class="event-card"><div class="image-placeholder"><img src="../image/school.webp" alt="Back To School"></div><p class="event-label">Back To School</p></div>
            <div class="event-card"><div class="image-placeholder"><img src="../image/baby.jpg" alt="Baby Care"></div><p class="event-label">Baby Care</p></div>
            <div class="event-card"><div class="image-placeholder"><img src="../image/women.jpg" alt="Her Essentials"></div><p class="event-label">Her Essentials</p></div>
            <div class="event-card"><div class="image-placeholder"><img src="../image/medical.jpg" alt="Medical Aid"></div><p class="event-label">Medical Aid</p></div>
            <div class="event-card"><div class="image-placeholder"><img src="../image/clothes.jpg" alt="Wear & Share"></div><p class="event-label">Wear & Share</p></div>
        </div>
    </section>
</main>
</body>
</html>