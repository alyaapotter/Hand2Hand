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
        $success = "Request approved!";
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
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        /* Table Layout */
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; background-color: #FFE4EF; border: 2px solid #A86B6C; border-radius: 8px; overflow: hidden; }
        .custom-table th { background-color: #443025; color: #FFE4EF; padding: 12px 15px; text-align: left; font-weight: bold; }
        .custom-table td { padding: 12px 15px; border-bottom: 1px solid #A86B6C; color: #443025; vertical-align: middle; }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background-color: #f7d6e4; }
        
        /* Clean Buttons Override */
        .btn-action, button.btn-action {
            background-color: #A86B6C !important; color: #FFE4EF !important; border: none !important;
            padding: 6px 12px !important; margin: 2px !important; border-radius: 6px !important; cursor: pointer !important;
            font-size: 13px !important; font-weight: bold !important; text-decoration: none !important;
            display: inline-flex !important; align-items: center !important; justify-content: center !important;
            line-height: 1.2 !important; height: auto !important; width: auto !important;
        }
        .btn-action:hover, button.btn-action:hover { background-color: #a45a66 !important; }
        .btn-approve, button.btn-approve { background-color: #22c55e !important; color: white !important; }
        .btn-approve:hover, button.btn-approve:hover { background-color: #16a34a !important; }
        .btn-reject, button.btn-reject { background-color: #ef4444 !important; color: white !important; }
        .btn-reject:hover, button.btn-reject:hover { background-color: #dc2626 !important; }
        .btn-delete, button.btn-delete { background-color: #7F5836 !important; color: white !important; }
        .btn-delete:hover, button.btn-delete:hover { background-color: #5c3f25 !important; }
        .action-cell { display: flex; gap: 5px; flex-wrap: wrap; }
        
        /* Tabs positioning on dark brown background */
        .tab-container { display: flex; gap: 8px; margin: 20px 0 10px 45px; flex-wrap: wrap; }
        .tab-link { padding: 6px 14px; border-radius: 20px; border: 2px solid #A86B6C; background-color: #FFE4EF; color: #443025; text-decoration: none; font-size: 13px; font-weight: bold; transition: all 0.2s; }
        .tab-link:hover, .tab-link.active { background-color: #443025; color: #FFE4EF; border-color: #443025; }
        .tab-cnt { background-color: #A86B6C; color: #FFE4EF; border-radius: 10px; padding: 1px 6px; margin-left: 4px; font-size: 11px; }
        .tab-link.active .tab-cnt { background-color: #FFE4EF; color: #443025; }

        /* Reason box */
        .reason-box { background:#fff8ee; border-left:3px solid #A86B6C; padding:7px 10px; border-radius:6px; font-size:13px; max-width:240px; line-height:1.5; }
        .hint { font-size:11px; color:#A86B6C; font-style:italic; margin-top:3px; }
        .badge { padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
        .badge-pending { background-color: #f59e0b; }
        .badge-approved { background-color: #22c55e; }
        .badge-rejected { background-color: #ef4444; }
        .badge-distributed { background-color: #6366f1; }

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
        <h1>Beneficiary Needs</h1>
    </div>

    <?php if ($success): ?><div class="alert alert-success" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Filter Tabs on Dark Background -->
    <div class="tab-container">
        <a href="?filter=all" class="tab-link <?= $filter=='all'?'active':'' ?>">All <span class="tab-cnt"><?= $counts['all'] ?></span></a>
        <a href="?filter=Pending" class="tab-link <?= $filter=='Pending'?'active':'' ?>">Pending <span class="tab-cnt"><?= $counts['Pending'] ?></span></a>
        <a href="?filter=Approved" class="tab-link <?= $filter=='Approved'?'active':'' ?>">Approved <span class="tab-cnt"><?= $counts['Approved'] ?></span></a>
        <a href="?filter=Distributed" class="tab-link <?= $filter=='Distributed'?'active':'' ?>">Distributed <span class="tab-cnt"><?= $counts['Distributed'] ?></span></a>
        <a href="?filter=Rejected" class="tab-link <?= $filter=='Rejected'?'active':'' ?>">Rejected <span class="tab-cnt"><?= $counts['Rejected'] ?></span></a>
    </div>

    <!-- Search Bar on Dark Background -->
    <input type="text" class="search-bar" placeholder="Search by name, item or reason..." onkeyup="filterTable(this.value)">

    <section class="admin-table">
        <h2>Beneficiary Requests</h2>
        <table class="custom-table" id="mainTable">
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
                    <tr><td colspan="9" style="text-align:center;padding:30px;font-style:italic;color:#7a5c3a;">No requests found.</td></tr>
                <?php else: ?>
                <?php foreach ($requests as $i => $r): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($r['username']) ?></td>
                    <td><?= $r['item_name'] ? htmlspecialchars($r['item_name']) : '<em style="color:#aaa">—</em>' ?></td>
                    <td><?= $r['quantity'] ?? '—' ?></td>
                    <td style="white-space:nowrap"><?= date('d M Y', strtotime($r['date'])) ?></td>
                    <td>
                        <?php if ($r['reason']): ?>
                            <div class="reason-box">
                                <?= htmlspecialchars($r['reason']) ?>
                                <?php if ($r['status'] == 'Pending'): ?>
                                    <div class="hint">Review → Approve or Reject</div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?><em style="color:#aaa">—</em><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['delivery_option'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
                    <td>
                        <div class="action-cell">
                            <?php if ($r['status'] == 'Pending'): ?>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                    <button type="submit" class="btn-action btn-approve">✓ Approve</button>
                                </form>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                    <button type="submit" class="btn-action btn-reject">✗ Reject</button>
                                </form>
                            <?php elseif ($r['status'] == 'Approved'): ?>
                                <a href="distribution.php?request_id=<?= $r['request_id'] ?>" class="btn-action">📦 Distribute</a>
                            <?php elseif ($r['status'] == 'Distributed'): ?>
                                <span style="color:#6366f1;font-weight:bold;font-size:13px;">✅ Done</span>
                            <?php elseif ($r['status'] == 'Rejected'): ?>
                                <span style="color:#ef4444;font-weight:bold;font-size:13px;">✗ Rejected</span>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Delete this request?')" style="display:inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                <button type="submit" class="btn-action btn-delete">🗑 Delete</button>
                            </form>
                        </div>
                    </td>
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
