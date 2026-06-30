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

        /* ================================================= */
        /* ===== TAMBAHAN CSS RESPONSIF UNTUK MOBILE  ===== */
        /* ================================================= */
        @media (max-width: 768px) {
            
            /* --- 1. MENCANTIKKAN NAVBAR ADMIN DI MOBILE --- */
            nav, .navbar {
                flex-direction: column !important;
                padding: 15px !important;
                text-align: center !important;
                gap: 12px !important;
                height: auto !important;
            }

            /* Menyusun pautan menu secara menegak yang kemas */
            nav .nav-links, .navbar .nav-links {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 10px !important;
                width: 100% !important;
                font-size: 15px !important;
                color: transparent !important; /* Menukar simbol '|' asal menjadi telus/hilang */
            }

            /* Kekalkan warna teks pautan asal & beri ruang sentuhan yang selesa */
            nav .nav-links a, .navbar .nav-links a {
                color: #FFE4EF !important; /* Kekalkan warna pink lembut asal */
                text-decoration: none !important;
                padding: 6px 15px !important;
                width: 80% !important;
                max-width: 200px;
                border-radius: 8px;
                background-color: rgba(255, 255, 255, 0.05); /* Sedikit kesan kotak menu */
                transition: background 0.2s;
                display: inline-block !important;
            }

            nav .nav-links a:hover, .navbar .nav-links a:hover {
                background-color: rgba(255, 255, 255, 0.15);
            }

            /* Butang Logout di bawah sekali */
            .navbar .logout-btn, nav a[href*="logout"] {
                margin-top: 5px !important;
                width: auto !important;
            }

            /* --- 2. KANDUNGAN UTAMA DASHBOARD --- */
            .welcome-banner {
                padding: 20px !important; /* Selaraskan padding kiri dan kanan */
                text-align: center !important; /* Letak teks tajuk di tengah untuk mobile */
            }

            .welcome-banner h1 {
                font-size: 26px !important; 
            }

            .main-content-wrapper {
                padding: 20px 12px !important; 
            }

            /* Kotak statistik: 2 lajur sebaris seimbang */
            .card-row {
                gap: 12px !important;
                margin-bottom: 20px;
            }

            .summary-card {
                width: calc(50% - 6px) !important; 
                min-height: 75px !important;
                padding: 10px !important;
                box-sizing: border-box;
            }

            .card-number {
                font-size: 22px !important;
            }

            .card-label {
                font-size: 11px !important;
            }

            .section-title {
                font-size: 18px !important;
                margin-top: 15px;
                margin-bottom: 10px;
                text-align: center !important; /* Tajuk bahagian di tengah */
            }

            /* Kurangkan padding frame jadual di skrin kecil */
            .admin-table-frame {
                padding: 10px !important;
                margin-bottom: 25px;
                border-radius: 8px !important;
            }

            /* Saiz teks jadual dikecilkan sedikit agar mesra skrol */
            .data-table th, .data-table td {
                padding: 10px 8px !important;
                font-size: 13px !important;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="welcome-banner">
        <h1>Dashboard</h1>
    </div>

    <div class="main-content-wrapper">

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

    <?php include '../includes/footer.php'; ?>
</body>
</html>