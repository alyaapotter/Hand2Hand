<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Dashboard (Admin)</title>
  <link rel="stylesheet" href="dashboard_admin.css" />
</head>
<body>

  <!-- Navbar -->
  <nav>
    <div class="nav-left">
      <img src="logo.png" alt="Hand2Hand Logo" class="logo-circle">
      <div class="nav-text">
        <h1>Hand2Hand</h1>
        <p>Dashboard | Beneficiaries | Events | Inventory | Distribution</p>
      </div>
    </div>
    <button class="btn-logout" onclick="window.location.href='logout.php'">Logout</button>
  </nav>

  <!-- Main -->
  <div class="main">
    <h2>Target Tracking Dashboard</h2>

    <!-- Stats -->
    <div class="stats">
      <span class="badge">Total events: 10</span>
      <span class="badge">Active events: 6</span>
      <span class="badge">Items collected: 30</span>
      <span class="badge">Beneficiaries: 19</span>
    </div>

    <div class="divider"></div>

    <!-- Table -->
    <div class="table-section">
      <h3>Event Tracking Table</h3>
      <table>
        <thead>
          <tr>
            <th>Event name</th>
            <th>Item</th>
            <th>Target</th>
            <th>Collected</th>
            <th>Progress</th>
          </tr>
        </thead>
        <tbody>
          <tr><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td><td></td><td></td></tr>
          <tr><td></td><td></td><td></td><td></td><td></td></tr>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>