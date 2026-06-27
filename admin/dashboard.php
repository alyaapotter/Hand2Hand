<?php
session_start();
require_once '../includes/db.php';
$sql = "SELECT 
            de.name AS event_name,
            i.name AS item_name,
            t.quantity AS target_qty,
            COALESCE(SUM(di.quantity), 0) AS collected_qty
        FROM target t
        JOIN donationevent de ON t.event_id = de.event_id
        JOIN item i ON t.item_id = i.item_id
        LEFT JOIN donation d ON d.event_id = t.event_id AND d.status = 'Received'
        LEFT JOIN donation_item di ON di.donation_id = d.donation_id AND di.item_id = t.item_id
        GROUP BY t.target_id, de.name, i.name, t.quantity
        ORDER BY de.event_id, i.item_id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll();


$totalEvents = $pdo->query("SELECT COUNT(*) FROM donationevent")->fetchColumn();
$activeEvents = $pdo->query("SELECT COUNT(*) FROM donationevent WHERE status = 'Active'")->fetchColumn();
$itemsCollected = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM donation_item")->fetchColumn();
$beneficiaries = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM request")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Dashboard (Admin)</title>
  <link rel="stylesheet" href="../css/dashboard_admin.css" />
</head>
<body>

  <!-- Navigation bar -->
  <nav>
    <div class="nav-left">
      <img src="../image/logo.png" alt="Hand2Hand Logo" class="logo-circle">
      <div class="nav-text">
        <h1>Hand2Hand</h1>
        <p> <a href="dashboard.php">Dashboard</a> | <a href="beneficiary.php">Beneficiaries</a> | <a href="event_management.php">Events</a> | <a href="inventory.php">Inventory</a> | <a href="distribution.php">Distribution</a></p>
      </div>
    </div>
    <button class="btn-logout" onclick="window.location.href='../logout.php'">Logout</button>
  </nav>

  <!-- Main -->
  <div class="main">
    <h2>Target Tracking Dashboard</h2>

  <div class="stats">
  <span class="badge">Total events: <?= $totalEvents ?></span>
  <span class="badge">Active events: <?= $activeEvents ?></span>
  <span class="badge">Items collected: <?= $itemsCollected ?></span>
  <span class="badge">Beneficiaries: <?= $beneficiaries ?></span>
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
          <?php foreach ($rows as $row): ?>
            <?php
              $progress = $row['target_qty'] > 0 
                          ? round(($row['collected_qty'] / $row['target_qty']) * 100) 
                          : 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($row['event_name']) ?></td>
              <td><?= htmlspecialchars($row['item_name']) ?></td>
              <td><?= $row['target_qty'] ?></td>
              <td><?= $row['collected_qty'] ?></td>
              <td><?= $progress ?>%</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>