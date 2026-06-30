<?php
session_start();
require_once '../includes/connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    /* ---------- Verify Donation ---------- */
    if ($_POST['action'] == 'receive') {

        $donation_id = intval($_POST['donation_id']);

        mysqli_begin_transaction($conn);

        try {

            $check = mysqli_query(
                $conn,
                "SELECT status
                 FROM DONATION
                 WHERE donation_id = $donation_id"
            );

            $donation = mysqli_fetch_assoc($check);

            if ($donation && $donation['status'] == 'Pending') {

                mysqli_query(
                    $conn,
                    "UPDATE DONATION
                     SET status='Received'
                     WHERE donation_id=$donation_id"
                );

                $items = mysqli_query(
                    $conn,
                    "SELECT item_id, quantity
                     FROM DONATION_ITEM
                     WHERE donation_id=$donation_id"
                );

                while ($item = mysqli_fetch_assoc($items)) {

                    $item_id = $item['item_id'];
                    $qty     = $item['quantity'];

                    $inv = mysqli_query(
                        $conn,
                        "SELECT inventory_id
                         FROM INVENTORY
                         WHERE item_id=$item_id"
                    );

                    if (mysqli_num_rows($inv) > 0) {

                        mysqli_query(
                            $conn,
                            "UPDATE INVENTORY
                             SET quantity = quantity + $qty
                             WHERE item_id=$item_id"
                        );
                    } else {

                        mysqli_query(
                            $conn,
                            "INSERT INTO INVENTORY(item_id, quantity)
                             VALUES($item_id, $qty)"
                        );
                    }
                }

                mysqli_commit($conn);

                $success = "Donation verified successfully.";
            } else {

                mysqli_rollback($conn);

                $error = "Donation has already been verified.";
            }
        } catch (Exception $e) {

            mysqli_rollback($conn);

            $error = "Failed to verify donation.";
        }
    }

    /* ---------- Reject / Cancel Donation ---------- */
    if ($_POST['action'] == 'cancel') {

        $donation_id = intval($_POST['donation_id']);

        $check = mysqli_query(
            $conn,
            "SELECT status
             FROM DONATION
             WHERE donation_id = $donation_id"
        );

        $donation = mysqli_fetch_assoc($check);

        if ($donation && $donation['status'] == 'Pending') {

            mysqli_query(
                $conn,
                "UPDATE DONATION
                 SET status='Cancelled'
                 WHERE donation_id=$donation_id"
            );

            $success = "Donation cancelled.";
        } else {

            $error = "Donation has already been verified.";
        }
    }

    if ($_POST['action'] == 'delete') {

        $donation_id = intval($_POST['donation_id']);

        mysqli_begin_transaction($conn);

        try {

            mysqli_query(
                $conn,
                "DELETE FROM DONATION_ITEM
                 WHERE donation_id=$donation_id"
            );

            mysqli_query(
                $conn,
                "DELETE FROM DONATION
                 WHERE donation_id=$donation_id"
            );

            mysqli_commit($conn);

            $success = "Donation deleted successfully.";
        } catch (Exception $e) {

            mysqli_rollback($conn);

            $error = "Unable to delete donation.";
        }
    }
}

$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn, trim($_GET['search']))
    : '';

$filter = isset($_GET['filter'])
    ? $_GET['filter']
    : 'all';


$query = "
SELECT
    d.donation_id,
    d.date,
    d.status,

    u.username,
    u.email,

    e.name AS event_name,

    i.name AS item_name,

    di.quantity

FROM DONATION d

JOIN USER u
ON d.user_id = u.user_id

JOIN DONATIONEVENT e
ON d.event_id = e.event_id

JOIN DONATION_ITEM di
ON d.donation_id = di.donation_id

JOIN ITEM i
ON di.item_id = i.item_id

WHERE 1=1
";


if ($search != "") {

    $query .= "
    AND
    (
        u.username LIKE '%$search%'
        OR
        e.name LIKE '%$search%'
        OR
        i.name LIKE '%$search%'
    )
    ";
}


if ($filter != "all") {

    $query .= "
    AND d.status='" .
        mysqli_real_escape_string($conn, $filter) .
        "'";
}


$query .= "
ORDER BY
FIELD(d.status,'Pending','Received','Cancelled'),
d.date DESC,
d.donation_id DESC
";


$result = mysqli_query($conn, $query);

$donations = mysqli_fetch_all($result, MYSQLI_ASSOC);

$counts = [
    'all' => 0,
    'Pending' => 0,
    'Received' => 0,
    'Cancelled' => 0
];


$countResult = mysqli_query(
    $conn,
    "SELECT status,
            COUNT(*) AS total
     FROM DONATION
     GROUP BY status"
);

while ($row = mysqli_fetch_assoc($countResult)) {

    $counts[$row['status']] = $row['total'];

    $counts['all'] += $row['total'];
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Verification - Hand2Hand</title>

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

        .badge { padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
        .badge-pending { background-color: #f59e0b; }
        .badge-received { background-color: #22c55e; }
        .badge-cancelled { background-color: #ef4444; }

    </style>

</head>

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="page-container">

        <div class="page-title2">
            <h1>Donation Verification</h1>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin:12px 0 15px 45px;width:30%;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error" style="margin:12px 0 15px 45px;width:30%;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="tab-container">

            <a href="?filter=all" class="tab-link <?= $filter == 'all' ? 'active' : '' ?>">
                All <span class="tab-cnt"><?= $counts['all'] ?></span>
            </a>

            <a href="?filter=Pending" class="tab-link <?= $filter == 'Pending' ? 'active' : '' ?>">
                Pending <span class="tab-cnt"><?= $counts['Pending'] ?></span>
            </a>

            <a href="?filter=Received" class="tab-link <?= $filter == 'Received' ? 'active' : '' ?>">
                Received <span class="tab-cnt"><?= $counts['Received'] ?></span>
            </a>

            <a href="?filter=Cancelled" class="tab-link <?= $filter == 'Cancelled' ? 'active' : '' ?>">
                Cancelled <span class="tab-cnt"><?= $counts['Cancelled'] ?></span>
            </a>

        </div>

        <input type="text"
            class="search-bar"
            placeholder="Search donor, event or item..."
            onkeyup="filterTable(this.value)">

        <section class="admin-table">

            <h2>Donation Records</h2>

            <table class="custom-table" id="mainTable">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Donor</th>
                        <th>Event</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($donations)): ?>

                        <tr>
                            <td colspan="8" style="text-align:center;padding:30px;font-style:italic;color:#7a5c3a;">
                                No donation records found.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($donations as $i => $d): ?>

                            <tr>

                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($d['username']) ?></td>
                                <td><?= htmlspecialchars($d['event_name']) ?></td>
                                <td><?= htmlspecialchars($d['item_name']) ?></td>
                                <td><?= $d['quantity'] ?></td>
                                <td style="white-space:nowrap"><?= date('d M Y', strtotime($d['date'])) ?></td>

                                <td>
                                    <span class="badge badge-<?= strtolower($d['status']) ?>">
                                        <?= $d['status'] ?>
                                    </span>
                                </td>

                                <td>

                                    <div class="action-cell">

                                        <?php if ($d['status'] == 'Pending'): ?>

                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="action" value="receive">
                                                <input type="hidden" name="donation_id" value="<?= $d['donation_id'] ?>">

                                                <button type="submit" class="btn-action btn-approve">
                                                    ✓ Received
                                                </button>
                                            </form>

                                            <form method="POST" style="display:inline">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="donation_id" value="<?= $d['donation_id'] ?>">

                                                <button type="submit" class="btn-action btn-reject">
                                                    ✗ Cancel
                                                </button>
                                            </form>

                                        <?php elseif ($d['status'] == 'Received'): ?>

                                            <span style="color:#6366f1;font-weight:bold;font-size:13px;">
                                                ✅ Done
                                            </span>

                                        <?php elseif ($d['status'] == 'Cancelled'): ?>

                                            <span style="color:#ef4444;font-weight:bold;font-size:13px;">
                                                ✗ Cancelled
                                            </span>

                                        <?php endif; ?>

                                        <form method="POST"
                                            onsubmit="return confirm('Delete this donation?')"
                                            style="display:inline">

                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="donation_id" value="<?= $d['donation_id'] ?>">

                                            <button type="submit" class="btn-action btn-delete">
                                                🗑 Delete
                                            </button>

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

    <script>
        function filterTable(val) {
            val = val.toLowerCase();
            document.querySelectorAll("#mainTable tbody tr").forEach(row => {
                row.style.display =
                    row.innerText.toLowerCase().includes(val) ?
                    "" : "none";
            });
        }
    </script>

</body>

</html> untuk page nii