<?php
session_start();
// Masukkan logik semakan session jika perlu (cth: semak id donor)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hand2Hand - Home (Donor)</title>
    <link rel="stylesheet" type="text/css" href="home_page_donor.css">
</head>
<body>

<header class="donor-header">
    <div class="top-header">
        <div class="logo">
            <img src="logo.png" alt="Hand2Hand logo" class="logo-circle" width="80">
            <h1>Hand2Hand</h1>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <nav class="donor-nav">
        <a href="home_donor.php" class="active">Home</a> |
        <a href="eventdonor.html">My Events</a> |
        <a href="#">My Donations</a>
    </nav>
</header>

<main class="content-container">
    <h2 class="welcome-title">Welcome, User</h2>

    <section class="events-section">
        <h3 class="section-title">Active Donation Events</h3>
        
        <div class="events-grid">
            <div class="event-card">
                <div class="image-placeholder">
                    <img src="food.webp" alt="Food Bank">
                </div>
                <p class="event-label">Food Bank</p>
            </div>

            <div class="event-card">
                <div class="image-placeholder">
                    <img src="school.webp" alt="Back To School">
                </div>
                <p class="event-label">Back To School</p>
            </div>

            <div class="event-card">
                <div class="image-placeholder">
                    <img src="baby.jpg" alt="Baby Care">
                </div>
                <p class="event-label">Baby Care</p>
            </div>

            <div class="event-card">
                <div class="image-placeholder">
                    <img src="women.jpg" alt="Her Essentials">
                </div>
                <p class="event-label">Her Essentials</p>
            </div>

            <div class="event-card">
                <div class="image-placeholder">
                    <img src="medical.jpg" alt="Medical Aid">
                </div>
                <p class="event-label">Medical Aid</p>
            </div>

            <div class="event-card">
                <div class="image-placeholder">
                    <img src="clothes.jpg" alt="Wear & Share">
                </div>
                <p class="event-label">Wear & Share</p>
            </div>
        </div>
    </section>
</main>

<footer class="main-footer">
    <hr class="footer-divider">
    <div class="footer-content">
        <h3>Hand2Hand</h3>
        <p>Contact Us:</p>
        <p>Email: <a href="mailto:hand2hand@support.com">hand2hand@support.com</a></p>
    </div>
</footer>

</body>
</html>