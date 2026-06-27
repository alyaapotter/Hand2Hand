<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php"); exit();
}

$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $did = intval($_POST['distribution_id']);
        $res = mysqli_query($conn, "SELECT item_id, quantity FROM DISTRIBUTION WHERE distribution_id=$did");
        $d   = mysqli_fetch_assoc($res);
        if ($d) {
            mysqli_query($conn, "UPDATE INVENTORY SET quantity=quantity+{$d['quantity']} WHERE item_id={$d['item_id']}");
            mysqli_query($conn, "DELETE FROM DISTRIBUTION WHERE distribution_id=$did");
            $success = "Record deleted, inventory restored.";
        }
    }
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$query  = "SELECT d.distribution_id, d.date, d.quantity, u.username AS beneficiary, i.name AS item, r.request_id
           FROM DISTRIBUTION d
           JOIN REQUEST r ON d.request_id=r.request_id
           JOIN USER u ON r.user_id=u.user_id
           JOIN ITEM i ON d.item_id=i.item_id WHERE 1=1";
if ($search) $query .= " AND (u.username LIKE '%$search%' OR i.name LIKE '%$search%')";
$query        .= " ORDER BY d.date DESC";
$result        = mysqli_query($conn, $query);
$distributions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution Management - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">Distribution Management</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <input type="text" class="search-bar" placeholder="Search..." onkeyup="filterTable(this.value)">

    <div class="section-title">Distribution List</div>
    <div class="table-wrapper">
        <table class="data-table" id="mainTable">
            <thead>
                <tr>
                    <th>Distribution ID</th><th>Beneficiary</th><th>Item</th><th>Quantity</th><th>Date</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($distributions)): ?>
                    <tr><td colspan="6" class="empty-row">No distribution records yet.</td></tr>
                <?php else: ?>
                <?php foreach ($distributions as $d): ?>
                <tr>
                    <td>#<?= str_pad($d['distribution_id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($d['beneficiary']) ?></td>
                    <td><?= htmlspecialchars($d['item']) ?></td>
                    <td><?= $d['quantity'] ?></td>
                    <td><?= date('d M Y', strtotime($d['date'])) ?></td>
                    <td style="display:flex;gap:5px">
                        <a href="distribution.php?request_id=<?= $d['request_id'] ?>" class="btn-edit">Edit</a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="distribution_id" value="<?= $d['distribution_id'] ?>">
                            <button type="submit" class="btn-edit">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="distribution.php" class="btn btn-primary">Distribute Items</a>
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
