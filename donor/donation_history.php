<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Donor') {
    header("Location: ../login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$search  = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

$query = "SELECT d.donation_id, d.date, d.status, e.name AS event_name
          FROM DONATION d JOIN DONATIONEVENT e ON d.event_id=e.event_id
          WHERE d.user_id=$user_id";
if ($search) $query .= " AND e.name LIKE '%$search%'";
$query .= " ORDER BY d.donation_id DESC";
$result    = mysqli_query($conn, $query);
$donations = mysqli_fetch_all($result, MYSQLI_ASSOC);

$donation_items = [];
if (!empty($donations)) {
    $ids = implode(',', array_column($donations, 'donation_id'));
    $res = mysqli_query($conn, "SELECT di.donation_id, di.quantity, i.name AS item_name FROM DONATION_ITEM di JOIN ITEM i ON di.item_id=i.item_id WHERE di.donation_id IN ($ids)");
    while ($row = mysqli_fetch_assoc($res)) {
        $donation_items[$row['donation_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Donations - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        /* Table Layout */
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; background-color: #FFE4EF; border: 2px solid #A86B6C; border-radius: 8px; overflow: hidden; }
        .custom-table th { background-color: #443025; color: #FFE4EF; padding: 12px 15px; text-align: left; font-weight: bold; }
        .custom-table td { padding: 12px 15px; border-bottom: 1px solid #A86B6C; color: #443025; vertical-align: middle; }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background-color: #f7d6e4; }
        
        .items-list { font-size:13px; color:#7a5c3a; }
        .badge { padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
        .badge-received { background-color: #22c55e; }
        .badge-pending  { background-color: #f59e0b; }
        .badge-cancelled  { background-color: #ef4444; }
        .empty-msg { text-align:center; color:#7a5c3a; padding:30px; font-style:italic; }


    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title2">
        <h1>My Donations</h1>
    </div>

    <!-- Search Bar on Dark Background -->
    <input type="text" class="search-bar" placeholder="Search event..." onkeyup="filterTable(this.value)">

    <section class="admin-table">
        <h2>Donation History</h2>
        <table class="custom-table" id="mainTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event</th>
                    <th>Items Donated</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($donations)): ?>
                    <tr><td colspan="5" class="empty-msg">No donations yet.</td></tr>
                <?php else: ?>
                <?php foreach ($donations as $i => $d): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($d['event_name']) ?></td>
                    <td class="items-list">
                        <?php if (!empty($donation_items[$d['donation_id']])): ?>
                            <?php foreach ($donation_items[$d['donation_id']] as $di): ?>
                                • <?= htmlspecialchars($di['item_name']) ?> (x<?= $di['quantity'] ?>)<br>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <em>—</em>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap"><?= date('d M Y', strtotime($d['date'])) ?></td>
                    <td><span class="badge badge-<?= strtolower($d['status']) ?>"><?= $d['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<?php include '../includes/footer.php'; ?>

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
