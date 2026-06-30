<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Donor') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'submit_donation') {
    $event_id = intval($_POST['event_id']);
    $items    = $_POST['item_id'] ?? [];
    $qtys     = $_POST['quantity'] ?? [];

    if (!$event_id) {
        $error = "Please select a donation event.";
    } elseif (empty($items)) {
        $error = "Please add at least one item.";
    } else {
        $date = date('Y-m-d');

        mysqli_query(
            $conn,
            "INSERT INTO DONATION (event_id, user_id, date, status)
     VALUES ($event_id, $user_id, '$date', 'Pending')"
        );

        $donation_id = mysqli_insert_id($conn);

        foreach ($items as $idx => $item_id) {

            $item_id = intval($item_id);
            $qty = intval($qtys[$idx] ?? 1);

            if ($item_id && $qty > 0) {

                mysqli_query(
                    $conn,
                    "INSERT INTO DONATION_ITEM
            (donation_id, item_id, quantity)
            VALUES ($donation_id, $item_id, $qty)
            ON DUPLICATE KEY UPDATE quantity=quantity+$qty"
                );
            }
        }

        $success = "Thank you for your donation! Your donation is waiting for admin verification.";
    }
}

$ev_result   = mysqli_query($conn, "SELECT * FROM DONATIONEVENT WHERE status='Active' ORDER BY start_date ASC");
$events      = mysqli_fetch_all($ev_result, MYSQLI_ASSOC);
$items       = [];
$target_items = [];
$selected_event = null;

if (isset($_GET['event_id'])) {
    $eid = intval($_GET['event_id']);
    $res = mysqli_query($conn, "SELECT * FROM DONATIONEVENT WHERE event_id=$eid");
    $selected_event = mysqli_fetch_assoc($res);
    if ($selected_event) {
        $target_res = mysqli_query($conn, "SELECT t.quantity AS target_qty, i.item_id, i.name, i.category FROM TARGET t JOIN ITEM i ON t.item_id=i.item_id WHERE t.event_id=$eid ORDER BY i.category, i.name");
        $target_items = mysqli_fetch_all($target_res, MYSQLI_ASSOC);
        $item_result = mysqli_query($conn, "SELECT item_id, name, category FROM ITEM ORDER BY category, name");
        $all_items   = mysqli_fetch_all($item_result, MYSQLI_ASSOC);
        $items = !empty($target_items) ? $target_items : $all_items;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Items - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        .donate-form label {
            display: block;
            font-weight: bold;
            color: #443025;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .donate-form select,
        .donate-form input[type="number"] {
            padding: 10px;
            border: 2px solid #A86B6C;
            border-radius: 10px;
            background: #FFE4EF;
            color: #443025;
            font-size: 14px;
            margin-bottom: 12px;
            outline: none;
            box-sizing: border-box;
        }

        .donate-form select {
            min-width: 280px;
        }

        .donate-form input[type="number"] {
            width: 80px;
        }

        .item-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .needed-list {
            background: #FFE4EF;
            border: 1px solid #A86B6C;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 18px;
        }

        .needed-list h3 {
            color: #443025;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .needed-list ul {
            margin: 0;
            padding-left: 18px;
            color: #443025;
            font-size: 14px;
        }

        .btn-action,
        button.btn-action {
            background-color: #A86B6C !important;
            color: #FFE4EF !important;
            border: none !important;
            padding: 6px 12px !important;
            margin: 2px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-size: 13px !important;
            font-weight: bold !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1.2 !important;
            height: auto !important;
            width: auto !important;
        }

        .btn-action:hover,
        button.btn-action:hover {
            background-color: #a45a66 !important;
        }


    </style>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="page-container">
        <div class="page-title2">
            <h1>Donate Items</h1>
        </div>

        <?php if ($success): ?><div class="alert alert-success" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <section class="admin-table">
            <?php if (!$selected_event): ?>
                <h2>Select Donation Event</h2>
                <?php if (empty($events)): ?>
                    <p style="color:#7a5c3a;">No active events available right now.</p>
                <?php else: ?>
                    <div class="event-list">
                        <?php foreach ($events as $ev): ?>
                            <div class="event-row" style="margin-left:0; background-color:#FFE4EF; border:2px solid #A86B6C; border-radius:10px; display:flex; justify-content:space-between; align-items:center; padding:15px; margin-bottom:12px;">
                                <div class="event-info">
                                    <h3 style="margin:0; color:#443025; font-size:16px; font-weight:bold;"><?= htmlspecialchars($ev['name']) ?></h3>
                                    <p style="margin:5px 0 0 0; color:#666; font-size:13px;"><?= date('d M Y', strtotime($ev['start_date'])) ?> - <?= date('d M Y', strtotime($ev['end_date'])) ?></p>
                                </div>
                                <div class="action-btns">
                                    <a href="donate_item.php?event_id=<?= $ev['event_id'] ?>" style="text-decoration:none;">
                                        <button type="button" class="btn-action" style="margin:0;">Donate to this Event</button>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <h2>Event: <?= htmlspecialchars($selected_event['name']) ?></h2>
                <p style="color:#7a5c3a;margin-bottom:15px;">
                    <?= date('d M Y', strtotime($selected_event['start_date'])) ?> - <?= date('d M Y', strtotime($selected_event['end_date'])) ?>
                </p>

                <?php if (!empty($target_items)): ?>
                    <div class="needed-list">
                        <h3>📋 Items Needed for this Event:</h3>
                        <ul>
                            <?php foreach ($target_items as $t): ?>
                                <li><?= htmlspecialchars($t['name']) ?> — Target: <?= $t['target_qty'] ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p style="color:#7a5c3a;font-style:italic;margin-bottom:15px;">Any item donations are welcomed for this event.</p>
                <?php endif; ?>

                <form method="POST" class="donate-form" onsubmit="return validateDonation()">
                    <input type="hidden" name="action" value="submit_donation">
                    <input type="hidden" name="event_id" value="<?= $selected_event['event_id'] ?>">

                    <div id="itemRows">
                        <div class="item-row">
                            <div>
                                <label>Select item:</label>
                                <select name="item_id[]" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($items as $item): ?>
                                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['name']) ?> (<?= $item['category'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label>Quantity:</label>
                                <input type="number" name="quantity[]" min="1" value="1" required style="margin-bottom:12px;">
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-action" onclick="addRow()" style="margin:0 0 20px 0;">+ Add Item</button>

                    <div style="display:flex;gap:10px;margin-top:10px">
                        <a href="donation_event_donor.php" class="back-btn" style="text-decoration:none;">← Cancel</a>
                        <button type="submit" class="submit-btn" style="margin:0;">Confirm Donation</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        const itemsData = <?= json_encode($items) ?>;

        function addRow() {
            const container = document.getElementById('itemRows');
            const div = document.createElement('div');
            div.className = 'item-row';
            div.innerHTML = `
        <div>
            <label>Select item:</label>
            <select name="item_id[]" required>
                <option value="">-- Select --</option>
                ${itemsData.map(i => `<option value="${i.item_id}">${i.name} (${i.category})</option>`).join('')}
            </select>
        </div>
        <div>
            <label>Quantity:</label>
            <input type="number" name="quantity[]" min="1" value="1" required style="width:80px">
        </div>
        <button type="button" onclick="this.closest('.item-row').remove()" class="btn-action" style="margin-bottom:12px;">Remove</button>`;
            container.appendChild(div);
        }

        function validateDonation() {
            const items = document.querySelectorAll('select[name="item_id[]"]');
            if (items.length === 0) {
                alert('Please add at least one item!');
                return false;
            }
            let valid = true;
            items.forEach((sel, i) => {
                if (sel.value === '') {
                    alert('Please select an item for row ' + (i + 1) + '!');
                    valid = false;
                }
            });
            return valid;
        }
    </script>
</body>

</html>