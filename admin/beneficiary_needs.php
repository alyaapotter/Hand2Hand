<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php"); exit();
}

$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'approve') {
        $id = intval($_POST['request_id']);
        mysqli_query($conn, "UPDATE REQUEST SET status='Approved' WHERE request_id=$id");
        $success = "Request approved! You can now proceed to distribute.";
    }
    if ($_POST['action'] == 'reject') {
        $id = intval($_POST['request_id']);
        mysqli_query($conn, "UPDATE REQUEST SET status='Rejected' WHERE request_id=$id");
        $success = "Request rejected.";
    }
    if ($_POST['action'] == 'delete') {
        $id = intval($_POST['request_id']);
        mysqli_query($conn, "DELETE FROM REQUEST WHERE request_id=$id");
        $success = "Request deleted.";
    }
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$query = "SELECT r.*, u.username, u.email, i.name AS item_name
          FROM REQUEST r
          JOIN USER u ON r.user_id = u.user_id
          LEFT JOIN ITEM i ON r.item_id = i.item_id
          WHERE 1=1";
if ($search) $query .= " AND (u.username LIKE '%$search%' OR r.reason LIKE '%$search%' OR i.name LIKE '%$search%')";
if ($filter != 'all') $query .= " AND r.status = '" . mysqli_real_escape_string($conn, $filter) . "'";
$query   .= " ORDER BY FIELD(r.status,'Pending','Approved','Distributed','Rejected'), r.date DESC";
$result   = mysqli_query($conn, $query);
$requests = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Count by status for tabs
$counts = ['all'=>0,'Pending'=>0,'Approved'=>0,'Rejected'=>0,'Distributed'=>0];
$cnt_res = mysqli_query($conn, "SELECT status, COUNT(*) as c FROM REQUEST GROUP BY status");
while ($row = mysqli_fetch_assoc($cnt_res)) {
    $counts[$row['status']] = $row['c'];
    $counts['all'] += $row['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Needs - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .filter-tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
        .filter-tab {
            padding:6px 16px; border-radius:20px; border:2px solid #c8a96e;
            background:#fff; color:#5a3520; cursor:pointer; text-decoration:none;
            font-size:0.88em; font-weight:600; transition:all 0.2s;
        }
        .filter-tab:hover, .filter-tab.active { background:#5a3520; color:#fff; border-color:#5a3520; }
        .filter-tab .cnt { background:#c8a96e; color:#fff; border-radius:10px; padding:1px 7px; margin-left:5px; font-size:0.85em; }
        .filter-tab.active .cnt { background:#fff; color:#5a3520; }

        .reason-box {
            background:#fef9ec; border-left:4px solid #f59e0b;
            border-radius:6px; padding:8px 12px;
            font-size:0.87em; color:#5a3520; max-width:260px; line-height:1.5;
        }
        .badge-pending    { background:#f59e0b;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.82em;white-space:nowrap; }
        .badge-approved   { background:#22c55e;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.82em;white-space:nowrap; }
        .badge-rejected   { background:#ef4444;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.82em;white-space:nowrap; }
        .badge-distributed{ background:#6366f1;color:#fff;padding:3px 10px;border-radius:12px;font-size:0.82em;white-space:nowrap; }
        .btn-approve { background:#22c55e;color:#fff;border:none;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:0.83em;white-space:nowrap; }
        .btn-reject  { background:#ef4444;color:#fff;border:none;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:0.83em;white-space:nowrap; }
        .btn-approve:hover { background:#16a34a; }
        .btn-reject:hover  { background:#dc2626; }
        .hint-text { font-size:0.75em; color:#9a7a5a; margin-top:3px; font-style:italic; }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">Beneficiary Needs</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="?filter=all" class="filter-tab <?= $filter=='all'?'active':'' ?>">All <span class="cnt"><?= $counts['all'] ?></span></a>
        <a href="?filter=Pending" class="filter-tab <?= $filter=='Pending'?'active':'' ?>">Pending <span class="cnt"><?= $counts['Pending'] ?></span></a>
        <a href="?filter=Approved" class="filter-tab <?= $filter=='Approved'?'active':'' ?>">Approved <span class="cnt"><?= $counts['Approved'] ?></span></a>
        <a href="?filter=Distributed" class="filter-tab <?= $filter=='Distributed'?'active':'' ?>">Distributed <span class="cnt"><?= $counts['Distributed'] ?></span></a>
        <a href="?filter=Rejected" class="filter-tab <?= $filter=='Rejected'?'active':'' ?>">Rejected <span class="cnt"><?= $counts['Rejected'] ?></span></a>
    </div>

    <input type="text" class="search-bar" placeholder="Search by name, item or reason..."
           onkeyup="filterTable(this.value)" id="searchInput"
           value="<?= htmlspecialchars($search) ?>">

    <div class="section-title">Beneficiary Requests</div>
    <div class="table-wrapper">
        <table class="data-table" id="mainTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Delivery</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="9" class="empty-row">No requests found.</td></tr>
                <?php else: ?>
                <?php foreach ($requests as $i => $r): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($r['username']) ?></td>
                    <td><?= $r['item_name'] ? htmlspecialchars($r['item_name']) : '<em style="color:#aaa">—</em>' ?></td>
                    <td><?= $r['quantity'] ?? '—' ?></td>
                    <td><?= date('d M Y', strtotime($r['date'])) ?></td>
                    <td>
                        <?php if ($r['reason']): ?>
                            <div class="reason-box">
                                <?= htmlspecialchars($r['reason']) ?>
                                <?php if ($r['status'] == 'Pending'): ?>
                                <div class="hint-text">👆 Review this reason then Approve or Reject</div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <em style="color:#aaa">—</em>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['delivery_option'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
                    <td style="display:flex;gap:5px;flex-wrap:wrap;align-items:center">
                        <?php if ($r['status'] == 'Pending'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                <button type="submit" class="btn-approve">✓ Approve</button>
                            </form>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                <button type="submit" class="btn-reject">✗ Reject</button>
                            </form>
                        <?php elseif ($r['status'] == 'Approved'): ?>
                            <a href="distribution.php?request_id=<?= $r['request_id'] ?>" class="btn-edit">📦 Distribute</a>
                        <?php elseif ($r['status'] == 'Distributed'): ?>
                            <span style="color:#6366f1;font-size:0.85em;">✅ Done</span>
                        <?php elseif ($r['status'] == 'Rejected'): ?>
                            <span style="color:#ef4444;font-size:0.85em;">✗ Rejected</span>
                        <?php endif; ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this request?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                            <button type="submit" class="btn-edit">🗑</button>
                        </form>
                    </td>
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
