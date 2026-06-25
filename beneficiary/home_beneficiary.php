<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Beneficiary Home</title>
  <link rel="stylesheet" href="../css/home_beneficiary.css" />
</head>
<body>

  <!-- Page label -->
  <div class="page-label">home page (beneficiary)</div>

  <!-- Navbar -->
  <nav>
    <div class="nav-left">
      <img src="../image/logo.png" alt="Hand2Hand Logo" class="logo-circle">
      <div class="nav-text">
        <h1>Hand2Hand</h1>
        <a href="home_beneficiary.php">Home</a> |
        <a href="aid_status.php">My Aid</a> |
        <a href="profile_page_bene.php">Profile</a>
      </div>
    </div>
    <button class="btn-logout" onclick="window.location.href='logout.php'">Logout</button>
  </nav>

  <!-- Welcome Banner -->
  <div class="welcome-banner">
    <h2>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></h2>
  </div>

  <!-- Main -->
  <div class="main">

    <!-- Beneficiary Dashboard -->
    <div class="section-box">
      <h3>Beneficiary Dashboard</h3>
      <p>Family Size</p>
      <p>Priority Level</p>
    </div>

    <div class="divider"></div>

    <!-- Latest Aid Status -->
    <div class="section-box">
      <h3>Latest Aid Status</h3>
      <table>
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td></tr>
        </tbody>
      </table>
    </div>

    <div class="divider"></div>

    <!-- Upcoming Distribution -->
    <div class="section-box">
      <h3>Upcoming Distribution</h3>
      <p>Next Distribution Date</p>
      <p>Location</p>
    </div>

  </div>

  <div class="divider"></div>

  <!-- Footer -->
  <footer>
    <div class="footer-brand">Hand2Hand</div>
    <p>Contact Us:<br/>Email: hand2hand@support.com</p>
  </footer>

</body>
</html>