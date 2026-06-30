
<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php"); exit();
}

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
$totalEvents    = $conn->query("SELECT COUNT(*) FROM donationevent")->fetch_row()[0];
$activeEvents   = $conn->query("SELECT COUNT(*) FROM donationevent WHERE status = 'Active'")->fetch_row()[0];
$itemsCollected = $conn->query("SELECT COALESCE(SUM(quantity), 0) FROM donation_item")->fetch_row()[0];
$beneficiaries  = $conn->query("SELECT COUNT(DISTINCT user_id) FROM request")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* ===== Color overrides to match formatBulan.css palette ===== */
        body { background-color: #f3e7dc; }
        .page-container .page-title { color: #443025; }
        .summary-card { background: #FFE4EF; border: 2px solid #A86B6C; }
        .card-number { color: #443025; }
        .card-label { color: #7F5836; }
        .section-title { color: #443025; }
        .table-wrapper { background: #7F5836; }
        .data-table th { background: #7F5836; color: #FFE4EF; border-bottom: 1px solid #443025; }
        .data-table td { background: #FFE4EF; color: #443025; border-bottom: 1px solid #e8d5c4; }
        .empty-row { color: #A86B6C; }
        .page-footer { background: #443025; color: #FFE4EF; border-top: 2px solid #A86B6C; }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="page-container">
    <div class="page-title">Target Tracking Dashboard</div>

    <!-- Summary Stats -->
    <div class="card-row">
        <div class="summary-card">
            <div class="card-number"><?= $totalEvents ?></div>
            <div class="card-label">Total Events</div>
        </div>
        <div class="summary-card">
            <div class="card-number"><?= $activeEvents ?></div>
            <div class="card-label">Active Events</div>
        </div>
        <div class="summary-card">
            <div class="card-number"><?= $itemsCollected ?></div>
            <div class="card-label">Items Collected</div>
        </div>
        <div class="summary-card">
            <div class="card-number"><?= $beneficiaries ?></div>
            <div class="card-label">Beneficiaries</div>
        </div>
    </div>

    <div class="section-title">Event Tracking</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Item</th>
                    <th>Target</th>
                    <th>Collected</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="5" class="empty-row">No tracking data yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                            $progress = $row['target_qty'] > 0
                                ? round(($row['collected_qty'] / $row['target_qty']) * 100)
                                : 0;
                            $progress = min(100, $progress);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['event_name']) ?></td>
                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                            <td><?= $row['target_qty'] ?></td>
                            <td><?= $row['collected_qty'] ?></td>
                            <td>
                                <div style="background:#7F5836;border-radius:20px;overflow:hidden;width:140px;height:18px;border:2px solid #443025;">
                                    <div style="background:#443025;height:100%;width:<?= $progress ?>%;color:#FFE4EF;font-size:10px;text-align:center;line-height:18px;">
                                        <?= $progress ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


</body>
</html>