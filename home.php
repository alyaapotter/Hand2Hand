<?php
session_start();
require_once __DIR__ . '/includes/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Home</title>
  <link rel="stylesheet" href="css/homeStyle.css" />
  <link rel="stylesheet" href="css/navbar_footer.css" />
  
  <style>
    /* ===== TAMBAHAN CSS RESPONSIF UNTUK MOBILE ===== */
    @media (max-width: 768px) {
      
      /* 1. Navigasi Mobile (Kekalkan struktur garisan tapi beri ruang bernafas) */
      nav {
        flex-direction: column !important;
        padding: 15px !important;
        text-align: center !important;
        gap: 12px !important;
      }
      
      .nav-links {
        display: block !important; /* Kekalkan susunan sebaris asal */
        font-size: 14px !important; /* Kecilkan sikit saiz font di mobile */
        line-height: 1.8 !important; /* Beri ruang menegak sekiranya menu turun ke bawah */
      }
      
      .nav-links a {
        padding: 0 4px !important; /* Jarakkan sikit teks dengan garisan pemisah */
      }

      /* 2. Hero Section Mobile (Teks atas, Gambar bawah) */
      .hero {
        flex-direction: column !important;
        text-align: center !important;
        padding: 25px 20px !important;
        gap: 20px !important;
        height: auto !important;
      }

      .hero-text {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
      }

      .hero-text h1 {
        font-size: 26px !important; 
        line-height: 1.2 !important;
      }

      .hero-text .description {
        font-size: 15px !important;
        margin-bottom: 20px !important;
      }

      /* Sembunyikan pembahagi garis menegak hero sahaja di mobile */
      .hero-divider {
        display: none !important;
      }

      .hero-image {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        border-radius: 12px !important;
      }

      /* 3. About Section Mobile */
      .about-section {
        padding: 25px 20px !important;
        text-align: justify !important;
      }

      /* 4. Active Events Section Mobile (Susun Menegak) */
      .events-section {
        padding: 25px 20px !important;
      }

      .events-grid {
        display: flex !important;
        flex-direction: column !important; 
        align-items: center !important;
        gap: 25px !important;
      }

      .event-card {
        width: 100% !important;
        max-width: 280px !important; 
        margin: 0 auto !important;
      }
      
      .event-label {
        display: block !important;
        text-align: center !important;
        margin-top: 8px !important;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
   <nav>
    <img src="image/logo.png" alt="Logo" class="logo-circle" />
    <div class="nav-links">
      <a href="home.php">Hand2Hand</a> |
      <a href="about.php">About Us</a> |
      <a href="event.php">Events</a> |
      <a href="login.php">Login</a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <h1>Support Communities Through Donation Events</h1>
      <p class="description">Join us in making a difference by donating to those in need.</p>
      <a href="login.php"><button class="btn-donate">Donate</button></a>
    </div>
    <div class="hero-divider"></div>
    <img class="hero-image" src="image/donation.jpg" alt="Charity event" />
  </section>

  <div class="divider"></div>

  <!-- About Section -->
  <section class="about-section">
    <h2 style="font-size: 22px;">About Hand2Hand</h2>
    <p style="font-size: 16px;">Hand2Hand is more than just a donation platform — it's a bridge between generosity and genuine need. We connect everyday donors with families and individuals facing hardship, making sure every contribution, big or small, finds its way to someone who truly needs it. Through organized donation events covering essentials like food, school supplies, baby care items, and medical aid, we strive to build a community where no one is left behind.</p>
  </section>

  <div class="divider"></div>

  <!-- Active Donation Events Section -->
  <section class="events-section">
    <h2 style="font-size: 22px;">Active Donation Events</h2>
    <div class="events-grid">

      <div>
        <div class="event-card">
          <img src="image/food.webp" alt="Food Bank" />
        </div>
        <span class="event-label">Food Bank</span>
      </div>

      <div>
        <div class="event-card">
          <img src="image/school.webp" alt="Back To School" />
        </div>
        <span class="event-label">Back To School</span>
      </div>

      <div>
        <div class="event-card">
          <img src="image/baby.jpg" alt="Baby Care" />
        </div>
        <span class="event-label">Baby Care</span>
      </div>

    </div>
  </section>

  <div class="divider"></div>

  <?php include 'includes/footer.php'; ?>

</body>
</html>