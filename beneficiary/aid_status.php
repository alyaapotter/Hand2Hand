<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Requester') {
    header("Location: ../login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$search  = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$query = "SELECT
            COALESCE(d.quantity, r.quantity, '-') AS quantity,
            COALESCE(d.date, r.date) AS date,
            COALESCE(i_dist.name, i_req.name, '—') AS item_name,
            r.status
          FROM REQUEST r
          LEFT JOIN DISTRIBUTION d ON r.request_id = d.request_id
          LEFT JOIN ITEM i_dist ON d.item_id = i_dist.item_id
          LEFT JOIN ITEM i_req ON r.item_id = i_req.item_id
          WHERE r.user_id = $user_id";
if ($search) $query .= " AND (i_dist.name LIKE '%$search%' OR i_req.name LIKE '%$search%')";
$query .= " ORDER BY COALESCE(d.date, r.date) DESC";
$result        = mysqli_query($conn, $query);
$distributions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Aid Status - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        /* Table Layout */
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; background-color: #FFE4EF; border: 2px solid #A86B6C; border-radius: 8px; overflow: hidden; }
        .custom-table th { background-color: #443025; color: #FFE4EF; padding: 12px 15px; text-align: left; font-weight: bold; }
        .custom-table td { padding: 12px 15px; border-bottom: 1px solid #A86B6C; color: #443025; vertical-align: middle; }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background-color: #f7d6e4; }
        
        /* Badges */
        .badge { padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
        .badge-pending { background-color: #f59e0b; }
        .badge-approved { background-color: #22c55e; }
        .badge-rejected { background-color: #ef4444; }
        .badge-distributed { background-color: #6366f1; }
        .empty-msg { text-align:center; color:#7a5c3a; padding:30px; font-style:italic; }

        /* Dark Footer styling for Light background pages */
        footer.dark-footer { background-color: #443025 !important; color: #FFE4EF !important; padding: 30px !important; margin-top: 0 !important; }
        footer.dark-footer h4 { color: #FFE4EF !important; margin-bottom: 15px !important; }
        footer.dark-footer p { color: #FFE4EF !important; margin-bottom: 2px !important; font-size: 14px !important; }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title2">
        <h1>My Aid Status</h1>
    </div>

    <!-- Actions & Search Bar on Dark Background -->
    <div style="margin: 20px 0 10px 45px;">
        <a href="submit_request.php" style="text-decoration:none;"><button type="button" class="submit-btn" style="margin:0;">+ New Request</button></a>
    </div>
    <input type="text" class="search-bar" placeholder="Search item..." onkeyup="filterTable(this.value)">

    <section class="admin-table">
        <h2>Aid History</h2>
        <table class="custom-table" id="mainTable">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($distributions)): ?>
                    <tr><td colspan="4" class="empty-msg">No requests yet. Click "+ New Request" to submit one!</td></tr>
                <?php else: ?>
                <?php foreach ($distributions as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['item_name']) ?></td>
                    <td><?= $d['quantity'] ?></td>
                    <td style="white-space:nowrap"><?= date('d M Y', strtotime($d['date'])) ?></td>
                    <td><span class="badge badge-<?= strtolower($d['status']) ?>"><?= $d['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<footer class="dark-footer">
    <h4>Hand2Hand</h4>
    <p>Contact Us:</p>
    <p>Email: hand2hand@support.com</p>
</footer>

<script>
function filterTable(val) {
    val = val.toLowerCase();
    document.querySelectorAll('#mainTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
}
</script>
</body>
</html>