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
        // Fetch distribution details first
        $res = mysqli_query($conn, "SELECT request_id, item_id, quantity FROM DISTRIBUTION WHERE distribution_id=$did");
        $d   = mysqli_fetch_assoc($res);
        if ($d) {
            $rid = $d['request_id'];
            // 1. Restore inventory quantity
            mysqli_query($conn, "UPDATE INVENTORY SET quantity=quantity+{$d['quantity']} WHERE item_id={$d['item_id']}");
            // 2. Revert request status back to 'Approved'
            mysqli_query($conn, "UPDATE REQUEST SET status='Approved' WHERE request_id=$rid");
            // 3. Delete distribution record
            mysqli_query($conn, "DELETE FROM DISTRIBUTION WHERE distribution_id=$did");
            $success = "Distribution cancelled. Request status reverted to Approved, inventory restored.";
        }
    }
    if ($_POST['action'] == 'delete_request') {
        $rid = intval($_POST['request_id']);
        mysqli_query($conn, "DELETE FROM REQUEST WHERE request_id=$rid");
        $success = "Request deleted successfully.";
    }
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// Query ALL requests and LEFT JOIN with DISTRIBUTION to get details if distributed
$query = "SELECT r.request_id, r.date AS request_date, r.status, r.quantity AS request_qty,
                 u.username AS beneficiary, i.name AS item,
                 d.distribution_id, d.date AS distribution_date, d.location
          FROM REQUEST r
          JOIN USER u ON r.user_id=u.user_id
          LEFT JOIN ITEM i ON r.item_id=i.item_id
          LEFT JOIN DISTRIBUTION d ON r.request_id=d.request_id
          WHERE 1=1";
if ($search) {
    $query .= " AND (u.username LIKE '%$search%' OR i.name LIKE '%$search%' OR r.status LIKE '%$search%')";
}
$query .= " ORDER BY FIELD(r.status,'Pending','Approved','Distributed','Rejected'), r.date DESC";

$result        = mysqli_query($conn, $query);
$distributions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution Management - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        /* Table Layout */
        .custom-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; background-color: #FFE4EF; border: 2px solid #A86B6C; border-radius: 8px; overflow: hidden; }
        .custom-table th { background-color: #443025; color: #FFE4EF; padding: 12px 15px; text-align: left; font-weight: bold; }
        .custom-table td { padding: 12px 15px; border-bottom: 1px solid #A86B6C; color: #443025; vertical-align: middle; }
        .custom-table tr:last-child td { border-bottom: none; }
        .custom-table tr:hover td { background-color: #f7d6e4; }
        
        /* Buttons */
        .btn-action, button.btn-action {
            background-color: #A86B6C !important; color: #FFE4EF !important; border: none !important;
            padding: 6px 12px !important; margin: 2px !important; border-radius: 6px !important; cursor: pointer !important;
            font-size: 13px !important; font-weight: bold !important; text-decoration: none !important;
            display: inline-flex !important; align-items: center !important; justify-content: center !important;
            line-height: 1.2 !important; height: auto !important; width: auto !important;
        }
        .btn-action:hover, button.btn-action:hover { background-color: #a45a66 !important; }
        .btn-delete, button.btn-delete { background-color: #7F5836 !important; color: white !important; }
        .btn-delete:hover, button.btn-delete:hover { background-color: #5c3f25 !important; }
        .action-cell { display: flex; gap: 5px; flex-wrap: wrap; }
        
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
        <h1>Distribution Management</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Search Bar on Dark Background -->
    <input type="text" class="search-bar" placeholder="Search beneficiary, item or status..." onkeyup="filterTable(this.value)">

    <section class="admin-table">
        <h2>Distribution & Request List</h2>
        <table class="custom-table" id="mainTable">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Beneficiary</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Date Requested</th>
                    <th>Status</th>
                    <th>Date Distributed</th>
                    <th>Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($distributions)): ?>
                    <tr><td colspan="9" class="empty-msg">No request or distribution records found.</td></tr>
                <?php else: ?>
                <?php foreach ($distributions as $d): ?>
                <tr>
                    <td>#<?= str_pad($d['request_id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($d['beneficiary']) ?></td>
                    <td><?= htmlspecialchars($d['item'] ?? '—') ?></td>
                    <td><?= $d['request_qty'] ?? '—' ?></td>
                    <td style="white-space:nowrap"><?= date('d M Y', strtotime($d['request_date'])) ?></td>
                    <td><span class="badge badge-<?= strtolower($d['status']) ?>"><?= $d['status'] ?></span></td>
                    <td style="white-space:nowrap">
                        <?= $d['distribution_date'] ? date('d M Y', strtotime($d['distribution_date'])) : '<em style="color:#aaa">—</em>' ?>
                    </td>
                    <td><?= htmlspecialchars($d['location'] ?? '—') ?></td>
                    <td>
                        <div class="action-cell">
                            <?php if ($d['status'] == 'Distributed'): ?>
                                <form method="POST" onsubmit="return confirm('Cancel this distribution and restore inventory stock?')" style="display:inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="distribution_id" value="<?= $d['distribution_id'] ?>">
                                    <button type="submit" class="btn-action btn-delete">🗑 Cancel Dist</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" onsubmit="return confirm('Delete this request permanently?')" style="display:inline">
                                    <input type="hidden" name="action" value="delete_request">
                                    <input type="hidden" name="request_id" value="<?= $d['request_id'] ?>">
                                    <button type="submit" class="btn-action btn-delete">🗑 Delete Req</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="distribution.php" style="text-decoration:none;"><button type="button" class="submit-btn" style="margin: 20px 0 0 0;">📦 Distribute Items</button></a>
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
