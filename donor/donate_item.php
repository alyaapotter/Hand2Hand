<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Donor') {
    header("Location: ../login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$success = ""; $error = "";

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
        mysqli_query($conn, "INSERT INTO DONATION (event_id, user_id, date, status) VALUES ($event_id, $user_id, '$date', 'Pending')");
        $donation_id = mysqli_insert_id($conn);

        foreach ($items as $idx => $item_id) {
            $item_id = intval($item_id);
            $qty     = intval($qtys[$idx] ?? 1);
            if ($item_id && $qty > 0) {
                mysqli_query($conn, "INSERT INTO DONATION_ITEM (donation_id, item_id, quantity) VALUES ($donation_id, $item_id, $qty)
                                     ON DUPLICATE KEY UPDATE quantity=quantity+$qty");
                mysqli_query($conn, "INSERT INTO INVENTORY (item_id, quantity) VALUES ($item_id, $qty)
                                     ON DUPLICATE KEY UPDATE quantity=quantity+$qty");
            }
        }
        mysqli_query($conn, "UPDATE DONATION SET status='Received' WHERE donation_id=$donation_id");
        $success = "Thank you for your donation!";
    }
}

$ev_result      = mysqli_query($conn, "SELECT * FROM DONATIONEVENT WHERE status='Active' ORDER BY date ASC");
$events         = mysqli_fetch_all($ev_result, MYSQLI_ASSOC);
$item_result    = mysqli_query($conn, "SELECT item_id, name, category FROM ITEM ORDER BY category, name");
$items          = mysqli_fetch_all($item_result, MYSQLI_ASSOC);
$selected_event = null;
if (isset($_GET['event_id'])) {
    $eid = intval($_GET['event_id']);
    $res = mysqli_query($conn, "SELECT * FROM DONATIONEVENT WHERE event_id=$eid");
    $selected_event = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Items - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navbar_footer.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">Donate Items</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if (!$selected_event): ?>
    <div class="form-section">
        <div class="form-section-title">Select Donation Event</div>
        <?php if (empty($events)): ?>
            <p style="color:#5a3520">No active events available right now.</p>
        <?php else: ?>
        <?php foreach ($events as $ev): ?>
            <div style="margin-bottom:12px;padding:12px;background:#f2d9d9;border-radius:8px">
                <strong><?= htmlspecialchars($ev['name']) ?></strong><br>
                <small>📅 <?= date('d M Y', strtotime($ev['date'])) ?> &nbsp;|&nbsp; 📍 <?= htmlspecialchars($ev['location']) ?></small><br>
                <a href="donate_item.php?event_id=<?= $ev['event_id'] ?>" class="btn btn-primary btn-sm" style="margin-top:8px">Donate to this Event</a>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <form method="POST" onsubmit="return validateDonation()">
        <input type="hidden" name="action" value="submit_donation">
        <input type="hidden" name="event_id" value="<?= $selected_event['event_id'] ?>">

        <div class="form-section">
            <div class="event-info-line"><strong>Event:</strong> <?= htmlspecialchars($selected_event['name']) ?></div>
            <div class="event-info-line"><strong>Duration:</strong> <?= date('d M Y', strtotime($selected_event['date'])) ?></div>
            <div class="event-info-line"><strong>Location:</strong> <?= htmlspecialchars($selected_event['location']) ?></div>
        </div>

        <div class="form-section">
            <div id="itemRows">
                <div class="form-group" style="margin-bottom:10px">
                    <label>Select item:</label>
                    <select name="item_id[]" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['name']) ?> (<?= $item['category'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity:</label>
                    <input type="number" name="quantity[]" min="1" value="1" style="width:80px" required>
                </div>
            </div>

            <button type="button" class="btn btn-outline btn-sm" onclick="addRow()" style="margin-bottom:15px">+ Add Item</button>
        </div>

        <button type="submit" class="btn btn-primary">Confirm Donation</button>
        <a href="donate_item.php" class="btn btn-outline" style="margin-left:10px">Cancel</a>
    </form>

    <script>
    const itemsData = <?= json_encode($items) ?>;
    function addRow() {
        const container = document.getElementById('itemRows');
        const div = document.createElement('div');
        div.style.marginTop = '15px';
        div.innerHTML = `
            <div class="form-group" style="margin-bottom:10px">
                <label>Select item:</label>
                <select name="item_id[]" required>
                    <option value="">-- Select --</option>
                    ${itemsData.map(i => `<option value="${i.item_id}">${i.name} (${i.category})</option>`).join('')}
                </select>
            </div>
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity[]" min="1" value="1" style="width:80px" required>
                <button type="button" onclick="this.closest('div').parentElement.remove()" style="background:#f5c8c8;border:none;border-radius:6px;padding:5px 10px;cursor:pointer;margin-left:8px">Remove</button>
            </div>`;
        container.appendChild(div);
    }
    function validateDonation() {
        const items = document.querySelectorAll('select[name="item_id[]"]');
        const qtys  = document.querySelectorAll('input[name="quantity[]"]');
        let valid   = true;
        if (items.length === 0) { alert('Please add at least one item!'); return false; }
        items.forEach((sel, i) => {
            if (sel.value === '') { alert('Please select an item for row ' + (i + 1) + '!'); valid = false; }
            if (qtys[i].value <= 0 || qtys[i].value === '') { alert('Please enter a valid quantity for row ' + (i + 1) + '!'); valid = false; }
        });
        return valid;
    }
    </script>
    <?php endif; ?>
</div>

<div class="page-footer">Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com</div>
</body>
</html>
