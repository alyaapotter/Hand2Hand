<?php
session_start();
require_once '../includes/connect.php'; 

$user_id = $_SESSION['user_id'] ?? null;

// Get beneficiary profile info
$stmt = $conn->prepare("SELECT family_size, priority_level, address FROM user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

$stmt2 = $conn->prepare("
    SELECT MIN(d.date) AS next_date
    FROM distribution d
    JOIN request r ON d.request_id = r.request_id
    WHERE r.user_id = ? AND d.date >= CURDATE()
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$nextDistribution = $result2->fetch_assoc();

$stmt3 = $conn->prepare("
    SELECT 
        i.name AS item_name,
        d.quantity,
        r.status
    FROM distribution d
    JOIN request r ON d.request_id = r.request_id
    JOIN item i ON d.item_id = i.item_id
    WHERE r.user_id = ?
    ORDER BY d.date DESC
    LIMIT 5
");
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$result3 = $stmt3->get_result();
$aidStatus = $result3->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Home - Hand2Hand</title>
    
    <link rel="stylesheet" href="../css/formatBulan.css">
    
    <style>
        body { 
            background-color: #f3e7dc !important; 
        }
        
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 20px 20px;
            margin-top: 40px; 
        }

        .welcome-banner {
            background-color: #443025 !important; 
            width: 100%;
            margin: 0;
            padding: 12px 0px 20px 40px; 
            color: #FFE4EF !important;  
            font-size: 26px !important; 
            font-weight: bold !important;
            font-family: sans-serif;
            margin-top: -2px; 
            box-sizing: border-box;
        }

        .card-row {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .summary-card { 
            background-color: #FFE4EF !important; 
            border: 2px solid #A86B6C !important; 
            border-radius: 12px !important;
            padding: 20px !important;
            flex: 1;
            min-width: 250px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100px;
        }

        .card-number { 
            color: #443025 !important; 
            font-size: 32px !important;
            font-weight: bold !important;
            line-height: 1.2;
        }

        .card-label { 
            color: #7F5836 !important; 
            font-size: 14px !important;
            margin-top: 5px !important;
            font-weight: 500;
        }

        .form-section { 
            background-color: #FFE4EF !important; 
            border: 2px solid #A86B6C !important;
            border-radius: 12px !important;
            padding: 20px !important;
            margin-bottom: 30px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .form-section-title { 
            color: #443025 !important; 
            font-size: 18px !important;
            font-weight: bold !important;
            margin-bottom: 8px !important;
        }

        .form-section p { 
            color: #443025 !important; 
            margin: 0 !important;
            font-size: 15px;
        }

        .section-title { 
            color: #443025 !important; 
            font-size: 20px !important;
            font-weight: bold !important;
            margin-bottom: 15px !important;
        }

        .table-wrapper { 
            background: #7F5836 !important; 
            border-radius: 8px;
            overflow: hidden;
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
        }

        .data-table td { 
            background-color: #FFE4EF !important; 
            color: #443025 !important; 
            padding: 12px 15px;
            border-bottom: 1px solid #e8d5c4; 
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }
        .badge-approved { background-color: #22c55e; }
        .badge-pending { background-color: #f59e0b; }
        .badge-rejected { background-color: #ef4444; }

        .empty-row { 
            color: #A86B6C; 
            text-align: center;
            padding: 20px !important;
        }

        footer.dark-footer { 
            background-color: #443025 !important; 
            color: #FFE4EF !important; 
            padding: 30px !important; 
            margin-top: 40px;
        }
        footer.dark-footer h4 { color: #FFE4EF !important; margin-bottom: 10px !important; }
        footer.dark-footer p { color: #FFE4EF !important; margin: 2px 0 !important; font-size: 14px !important; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="welcome-banner">
    Welcome, <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User' ?>
</div>

<div class="page-container">

    <div class="card-row">
        <div class="summary-card">
            <div class="card-number"><?= $profile['family_size'] !== null ? htmlspecialchars($profile['family_size']) : '-' ?></div>
            <div class="card-label">Family Size</div>
        </div>
        <div class="summary-card">
            <div class="card-number"><?= $profile['priority_level'] !== null ? htmlspecialchars($profile['priority_level']) : '-' ?></div>
            <div class="card-label">Priority Level</div>
        </div>
        <div class="summary-card">
            <div class="card-number" style="font-size: 24px !important;"><?= $nextDistribution['next_date'] ? date('d M Y', strtotime($nextDistribution['next_date'])) : 'None' ?></div>
            <div class="card-label">Next Distribution</div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-title">Address</div>
        <p><?= !empty($profile['address']) ? htmlspecialchars($profile['address']) : 'Not set' ?></p>
    </div>

    <div class="section-title">Latest Aid Status</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($aidStatus)): ?>
                    <tr><td colspan="3" class="empty-row">No aid records yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($aidStatus as $aid): ?>
                        <tr>
                            <td><?= htmlspecialchars($aid['item_name']) ?></td>
                            <td><?= htmlspecialchars($aid['quantity']) ?></td>
                            <td><span class="badge badge-<?= strtolower($aid['status']) ?>"><?= htmlspecialchars($aid['status']) ?></span></td>
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