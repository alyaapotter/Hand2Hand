<?php
session_start();
require_once '../includes/connect.php';

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

$result = $conn->query($sql);
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Stat badge queries
$totalEvents = $conn->query("SELECT COUNT(*) FROM donationevent")->fetch_row()[0];
$activeEvents = $conn->query("SELECT COUNT(*) FROM donationevent WHERE status = 'Active'")->fetch_row()[0];
$itemsCollected = $conn->query("SELECT COALESCE(SUM(quantity), 0) FROM donation_item")->fetch_row()[0];
$beneficiaries = $conn->query("SELECT COUNT(DISTINCT user_id) FROM request")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Dashboard (Admin)</title>
  <link rel="stylesheet" href="../css/formatBulan.css" />
  <link rel="stylesheet" href="../css/navbar_footer.css" />
</head>
<body>

  <?php include '../includes/navbar.php'; ?>

  <!-- Page Title -->
  <div class="page-title2">
    <h1>Target Tracking Dashboard</h1>
  </div>

  <!-- Stats -->
  <div class="admin-table">
    <div class="event-row">
      <span class="badge">Total events: <?= $totalEvents ?></span>
      <span class="badge">Active events: <?= $activeEvents ?></span>
      <span class="badge">Items collected: <?= $itemsCollected ?></span>
      <span class="badge">Beneficiaries: <?= $beneficiaries ?></span>
    </div>
  </div>

  <!-- Event Tracking -->
  <div class="admin-table">
    <h2>Event Tracking Table</h2>
    <div class="event-list">
      <?php foreach ($rows as $row): ?>
        <?php
          $progress = $row['target_qty'] > 0 
                      ? round(($row['collected_qty'] / $row['target_qty']) * 100) 
                      : 0;
        ?>
        <div class="event-row">
          <div class="event-info">
            <h3><?= htmlspecialchars($row['event_name']) ?> — <?= htmlspecialchars($row['item_name']) ?></h3>
            <p>Target: <?= $row['target_qty'] ?> | Collected: <?= $row['collected_qty'] ?></p>
          </div>
          <div class="progress-item">
            <div class="progress-container">
              <div class="progress-bar" style="width: <?= $progress ?>%;"><?= $progress ?>%</div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php include '../includes/footer.php'; ?>

</body>
</html>