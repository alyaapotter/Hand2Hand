<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Beneficiaries (Admin)</title>
  <link rel="stylesheet" href="../css/beneficiary_page_admin.css" />
</head>
<body>

  <header class="admin-header">
    <div class="header-top">
      <div class="logo-brand-container">
        <img src="../image/logo.png" alt="Logo" class="logo-circle" />
        <div class="brand-nav-box">
          <h1 class="brand-title">Hand2Hand</h1>
          <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a> | 
            <a href="beneficiaries.php" class="active">Beneficiaries</a> | 
            <a href="event_management.php">Events</a> | 
            <a href="inventory.php">Inventory</a> | 
            <a href="distribution.php">Distribution</a>
          </nav>
        </div>
      </div>
      <a href="../logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="header-title-section">
      <h2>Beneficiaries</h2>
      <div class="search-container">
        <span class="search-icon">&#128269;</span>
        <input type="text" placeholder="Search..." class="search-input" />
      </div>
    </div>
  </header>

  <main class="content-container">
    <h3 class="section-title">Beneficiaries List</h3>

    <div class="table-action-wrapper">
      <table class="beneficiaries-table">
        <thead>
          <tr>
            <th>Beneficiary ID</th>
            <th>Name</th>
            <th>Family Size</th>
            <th>Priority</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
        </tbody>
      </table>

      <div class="action-buttons-column">
        <button class="btn-edit">Edit</button>
        <button class="btn-edit">Edit</button>
        <button class="btn-edit">Edit</button>
        <button class="btn-edit">Edit</button>
      </div>
    </div>

    <div class="add-button-container">
      <button class="btn-add">Add Beneficiary</button>
    </div>
  </main>

</body>
</html>