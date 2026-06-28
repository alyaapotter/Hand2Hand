<?php
session_start();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

// Get beneficiary profile info
$stmt = $pdo->prepare("SELECT family_size, priority_level, address FROM user WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Get next upcoming distribution date for this beneficiary
$stmt2 = $pdo->prepare("
    SELECT MIN(d.date) AS next_date
    FROM distribution d
    JOIN request r ON d.request_id = r.request_id
    WHERE r.user_id = ? AND d.date >= CURDATE()
");
$stmt2->execute([$user_id]);
$nextDistribution = $stmt2->fetch();

// Get latest aid status (items distributed to this beneficiary)
$stmt3 = $pdo->prepare("
    SELECT 
        i.name AS item_name,
        d.quantity,
        r.status
    FROM distribution d
    JOIN request r ON d.request_id = r.request_id
    JOIN item i ON d.item_id = i.item_id
    WHERE r.user_id = ?
    ORDER BY d.date DESC
");
$stmt3->execute([$user_id]);
$aidStatus = $stmt3->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Beneficiary Home</title>
  <link rel="stylesheet" href="../css/home_beneficiary.css" />
  <link rel="stylesheet" href="../css/navbar_footer.css" />
</head>
<body>

  <?php include '../includes/navbar.php'; ?>

  <!-- Welcome Banner -->
  <div class="welcome-banner">
    <h2>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></h2>
  </div>

  <!-- Main -->
  <div class="main">

    <!-- Beneficiary Dashboard -->
    <div class="section-box">
      <h3>Beneficiary Dashboard</h3>
      <p>Family Size: <?= $profile['family_size'] !== null ? htmlspecialchars($profile['family_size']) : 'Not set' ?></p>
      <p>Priority Level: <?= $profile['priority_level'] !== null ? htmlspecialchars($profile['priority_level']) : 'Not set' ?></p>
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
          <?php if (count($aidStatus) > 0): ?>
            <?php foreach ($aidStatus as $aid): ?>
              <tr>
                <td><?= htmlspecialchars($aid['item_name']) ?></td>
                <td><?= htmlspecialchars($aid['quantity']) ?></td>
                <td><?= htmlspecialchars($aid['status']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="3">No aid records yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="divider"></div>

    <!-- Upcoming Distribution -->
    <div class="section-box">
      <h3>Upcoming Distribution</h3>
      <p>Next Distribution Date: <?= $nextDistribution['next_date'] ? htmlspecialchars($nextDistribution['next_date']) : 'No upcoming distribution' ?></p>
      <p>Address: <?= !empty($profile['address']) ? htmlspecialchars($profile['address']) : 'Not set' ?></p>
    </div>

  </div>

  <div class="divider"></div>

  <?php include '../includes/footer.php'; ?>

</body>
</html>