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
            COALESCE(i_dist.name, i_req.name, 'Pending/Unspecified Item') AS item_name, 
            r.status
          FROM REQUEST r
          LEFT JOIN DISTRIBUTION d ON r.request_id = d.request_id
          LEFT JOIN ITEM i_dist ON d.item_id = i_dist.item_id
          LEFT JOIN ITEM i_req ON r.item_id = i_req.item_id
          WHERE r.user_id = $user_id";
if ($search) {
    $query .= " AND (i_dist.name LIKE '%$search%' OR i_req.name LIKE '%$search%' OR 'Pending/Unspecified Item' LIKE '%$search%')";
}
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
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">My Aid Status</div>

    <div style="display:flex;justify-content:flex-end;margin-bottom:15px">
        <a href="submit_request.php" class="btn btn-primary">+ New Request</a>
    </div>

    <input type="text" class="search-bar" placeholder="Search item..." onkeyup="filterTable(this.value)">

    <div class="section-title">Aid Distribution History</div>
    <div class="table-wrapper">
        <table class="data-table" id="mainTable">
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
                    <tr><td colspan="4" class="empty-row">No items received yet.</td></tr>
                <?php else: ?>
                <?php foreach ($distributions as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['item_name']) ?></td>
                    <td><?= $d['quantity'] ?></td>
                    <td><?= date('d M Y', strtotime($d['date'])) ?></td>
                    <td><span class="badge badge-<?= strtolower($d['status']) ?>"><?= $d['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="page-footer">Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com</div>

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