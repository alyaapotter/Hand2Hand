<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Requester') {
    header("Location: ../login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$success = ""; $error = "";

// Get all items for the dropdown
$itemsResult = $conn->query("SELECT item_id, name FROM item ORDER BY name");
$items = $itemsResult->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $delivery_option = $_POST['delivery_option'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $preferred_date = $_POST['preferred_date'] ?? '';

    if (empty($item_id) || empty($quantity) || empty($delivery_option) || empty($preferred_date)) {
        $error = "Please fill in all required fields.";
    } elseif ($delivery_option == 'Delivery' && empty($reason)) {
        $error = "Reason for delivery is required when choosing Delivery.";
    } else {
        $reasonValue = $delivery_option == 'Delivery' ? $reason : null;
        $date = date('Y-m-d');
        $description = 'Request via distribution form';

        $stmt = $conn->prepare("
            INSERT INTO request (date, status, description, user_id, item_id, quantity, delivery_option, reason, preferred_date)
            VALUES (?, 'Pending', ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssiiisss", $date, $description, $user_id, $item_id, $quantity, $delivery_option, $reasonValue, $preferred_date);
        $stmt->execute();
        $success = "Request submitted! Waiting for admin review.";
    }
}

// Get this beneficiary's past requests (for tracking)
$stmt2 = $conn->prepare("
    SELECT r.request_id, i.name AS item_name, r.quantity, r.delivery_option, r.reason, 
           r.status, r.preferred_date, r.distribution_date, r.distribution_location
    FROM request r
    LEFT JOIN item i ON r.item_id = i.item_id
    WHERE r.user_id = ?
    ORDER BY r.request_id DESC
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$myRequests = $result2->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hand2Hand - Distribution Request</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/navbar_footer.css">
    <style>
    body {
        background-color: #f3e7dc !important;
    }
    .navbar {
        min-height: 90px !important;
        padding: 15px 30px !important;
    }
    .divider {
        height: 12px;
        background-color: #5a3825;
        width: 100%;
    }
</style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="page-container">
    <div class="page-title">Request Distribution</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Submit Request Form -->
    <div class="form-section" style="margin-bottom:20px">
        <div class="form-section-title">New Distribution Request</div>
        <form method="POST" onsubmit="return validateDistribution()">
            <div class="form-group">
                <label>Item Name</label>
                <select name="item_id" required>
                    <option value="">-- Select Item --</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" min="1" required>
            </div>

            <div class="form-group">
                <label>Preferred Date</label>
                <input type="date" name="preferred_date" required min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group form-group-full">
                <label>Option</label>
                <div style="display:flex; gap:20px; margin-top:6px;">
                    <label><input type="radio" name="delivery_option" value="Delivery" onclick="toggleReason(true)" required> Delivery</label>
                    <label><input type="radio" name="delivery_option" value="Pickup" onclick="toggleReason(false)"> Pickup</label>
                </div>
            </div>

            <div class="form-group form-group-full" id="reasonField" style="display:none">
                <label>Reason for Delivery</label>
                <textarea name="reason" rows="3" placeholder="Explain why you need delivery instead of pickup..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>

    <!-- My Requests Table -->
    <div class="section-title">My Distribution Requests</div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Option</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Preferred Date</th>
                    <th>Distribution Date</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($myRequests)): ?>
                    <tr><td colspan="8" class="empty-row">No requests submitted yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($myRequests as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['item_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['quantity'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['delivery_option'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['reason'] ?? '-') ?></td>
                        <td><span class="badge badge-<?= strtolower($r['status']) ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                        <td><?= $r['preferred_date'] ? htmlspecialchars($r['preferred_date']) : '-' ?></td>
                        <td><?= $r['distribution_date'] ? htmlspecialchars($r['distribution_date']) : '-' ?></td>
                        <td><?= $r['distribution_location'] ? htmlspecialchars($r['distribution_location']) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="divider"></div>
<?php include '../includes/footer.php'; ?>

<script>
function toggleReason(show) {
    document.getElementById('reasonField').style.display = show ? 'block' : 'none';
    document.querySelector('textarea[name="reason"]').required = show;
}

function validateDistribution() {
    const option = document.querySelector('input[name="delivery_option"]:checked');
    if (!option) {
        alert('Please choose Delivery or Pickup!');
        return false;
    }
    if (option.value === 'Delivery') {
        const reason = document.querySelector('textarea[name="reason"]').value.trim();
        if (reason === '') {
            alert('Please provide a reason for delivery!');
            return false;
        }
    }
    return true;
}
</script>
</body>
</html>