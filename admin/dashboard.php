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
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        body { 
            background-color: #f3e7dc !important; 
            margin: 0;
            padding: 0;
        }

        /* ===== BANNER TAJUK BERSAMBUNG DENGAN NAVBAR ===== */
        .welcome-banner {
            background-color: #443025 !important; 
            width: 100%;
            margin: 0;
            padding: 15px 0px 25px 60px; 
            margin-top: -2px; 
            box-sizing: border-box;
        }

        .welcome-banner h1 {
            color: #FFE4EF !important; 
            font-size: 32px !important; 
            font-weight: bold !important;
            margin: 0;
            font-family: sans-serif;
        }

        /* Pembungkus kandungan utama - Di-tengah-kan */
        .main-content-wrapper {
            max-width: 1100px; 
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Container 4 Box Statistik */
        .card-row {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center; 
        }

        /* 4 Box Statistik */
        .summary-card { 
            background-color: #FFE4EF !important; 
            border: 2px solid #A86B6C !important; 
            border-radius: 12px !important;
            padding: 15px !important;
            width: 220px; 
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 85px;
        }

        .card-number { 
            color: #443025 !important; 
            font-size: 28px !important; 
            font-weight: bold !important;
            line-height: 1.1;
        }

        .card-label { 
            color: #7F5836 !important; 
            font-size: 13px !important; 
            margin-top: 4px !important;
            font-weight: 500;
        }

        /* Tajuk List "Event Tracking" */
        .section-title {
            color: #443025 !important;
            font-size: 22px !important;
            font-weight: bold !important;
            margin-top: 20px;
            margin-bottom: 15px;
            text-align: left;
        }

        /* ===== KOTAK JADUAL (FRAME) ===== */
        .admin-table-frame {
            background-color: #FFE4EF !important; 
            border: 2px solid #A86B6C !important; 
            border-radius: 12px !important;
            padding: 20px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            overflow-x: auto; 
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th { 
            background-color: #7F5836 !important; 
            color: #FFE4EF !important; 
            padding: 12px 15px;
            text-align: left;
            font-weight: bold;
        }

        .data-table td { 
            background-color: transparent !important; 
            color: #443025 !important; 
            padding: 12px 15px;
            border-bottom: 1px solid #e8d5c4; 
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .empty-row { 
            color: #A86B6C; 
            text-align: center;
            padding: 20px !important;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <!-- Bahagian tajuk yang bersambung dengan navigasi -->
    <div class="welcome-banner">
        <h1>Dashboard</h1>
    </div>

    <!-- Semua kandungan halaman dalam wrapper berpusat -->
    <div class="main-content-wrapper">

        <!-- 4 Box Statistik -->
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

        <!-- Nama List: Event Tracking -->
        <div class="section-title">Event Tracking</div>

        <!-- Jadual Ber-Frame Box -->
        <div class="admin-table-frame">
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

    <!-- Panggilan fail footer modular ber-pathing sepadan -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>