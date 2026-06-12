<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Home</title>
  <link rel="stylesheet" href="home.style.css" />
</head>
<body>

  <!-- Navbar -->
  <nav>
    <img src="images/logo.png" alt="Logo" class="logo-circle" />
    <div class="nav-links">
      <a href="home.php">Hand2Hand</a> |
      <a href="about.php">About Us</a> |
      <a href="events.php">Events</a> |
      <a href="login.php">Login</a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <h1>Support Communities Through Donation Events</h1>
      <p class="description">Join us in making a difference by donating to those in need.</p>
      <a href="donate.php"><button class="btn-donate">Donate</button></a>
    </div>
    <div class="hero-divider"></div>
    <img class="hero-image" src="images/hero.jpg" alt="Charity event" />
  </section>

  <div class="divider"></div>

  <!-- About Section -->
  <section class="about-section">
    <h2>About Hand2Hand</h2>
    <p>Hand2Hand is a community-driven platform that connects donors with those in need through organized donation events.</p>
  </section>

  <div class="divider"></div>

  <!-- Active Donation Events Section -->
  <section class="events-section">
    <h2>Active Donation Events</h2>
    <div class="events-grid">

      <div>
        <div class="event-card">
          <img src="images/foodbank.jpg" alt="Food Bank" />
        </div>
        <span class="event-label">Food Bank</span>
      </div>

      <div>
        <div class="event-card">
          <img src="images/backtoschool.jpg" alt="Back To School" />
        </div>
        <span class="event-label">Back To School</span>
      </div>

      <div>
        <div class="event-card">
          <img src="images/babycare.jpg" alt="Baby Care" />
        </div>
        <span class="event-label">Baby Care</span>
      </div>

    </div>
  </section>

  <div class="divider"></div>

  <!-- Footer -->
  <footer>
    <div class="footer-brand">Hand2Hand</div>
    <p>Contact Us:<br/>Email: hand2hand@support.com</p>
  </footer>

</body>
</html>