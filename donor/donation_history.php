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
$query    .= " ORDER BY d.date DESC";
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
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">My Donation</div>

    <input type="text" class="search-bar" placeholder="Search event..." onkeyup="filterTable(this.value)">

    <div class="section-title">Donation History</div>
    <div class="table-wrapper">
        <table class="data-table" id="mainTable">
            <thead>
                <tr><th>Date</th><th>Event Name</th><th>Item</th><th>Quantity</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (empty($donations)): ?>
                    <tr><td colspan="5" class="empty-row">No donation history yet.</td></tr>
                <?php else: ?>
                <?php foreach ($donations as $don):
                    $its   = $donation_items[$don['donation_id']] ?? [['item_name'=>'—','quantity'=>'—']];
                    $first = true;
                    foreach ($its as $it): ?>
                <tr>
                    <?php if ($first): ?>
                    <td rowspan="<?= count($its) ?>"><?= date('d M Y', strtotime($don['date'])) ?></td>
                    <td rowspan="<?= count($its) ?>"><?= htmlspecialchars($don['event_name']) ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($it['item_name']) ?></td>
                    <td><?= $it['quantity'] ?></td>
                    <?php if ($first): ?>
                    <td rowspan="<?= count($its) ?>"><span class="badge badge-<?= strtolower($don['status']) ?>"><?= $don['status'] ?></span></td>
                    <?php endif; ?>
                </tr>
                <?php $first = false; endforeach; ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
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
