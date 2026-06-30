<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Requester') {
    header("Location: ../login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id       = intval($_POST['item_id']);
    $quantity      = intval($_POST['quantity']);
    $delivery_type = mysqli_real_escape_string($conn, $_POST['delivery_type']);
    $reason        = mysqli_real_escape_string($conn, $_POST['reason']);
    $description   = mysqli_real_escape_string($conn, trim($_POST['description']));
    $date          = date('Y-m-d');
    $delivery_address = isset($_POST['delivery_address']) ? mysqli_real_escape_string($conn, trim($_POST['delivery_address'])) : '';

    if (!$item_id || $quantity <= 0 || empty($delivery_type) || empty($reason) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        $full_description = "Item requested: See item field. Delivery type: $delivery_type. Delivery Address: $delivery_address. Reason: $reason. Additional info: $description";
        mysqli_query($conn, "INSERT INTO REQUEST (date, status, description, user_id, item_id, quantity, delivery_option, reason) VALUES ('$date', 'Pending', '$full_description', $user_id, $item_id, $quantity, '$delivery_type', '$reason')");
        $success = "Your request has been submitted! The admin will review it soon.";
    }
}

$item_result = mysqli_query($conn, "SELECT item_id, name, category FROM ITEM ORDER BY category, name");
$items       = mysqli_fetch_all($item_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Request - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        .req-form label { display:block; font-weight:bold; color:#443025; margin-bottom:5px; font-size:14px; }
        .req-form input[type="text"],
        .req-form input[type="number"],
        .req-form select,
        .req-form textarea { width:100%; padding:10px; margin-bottom:18px; border:2px solid #A86B6C; border-radius:10px; background:#FFE4EF; color:#443025; font-size:14px; box-sizing:border-box; outline:none; }
        .req-form select { width:auto; min-width:300px; }
        .req-form textarea { resize:vertical; min-height:100px; }
        .req-form .small { width:100px; }
        .required { color:#ef4444; }

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
        <h1>Submit Aid Request</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($success) ?>
            <br><a href="aid_status.php" style="color:#1a5c1a;font-weight:bold">← Back to My Aid</a>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="admin-table">
        <?php if (!$success): ?>
        <h2>Request Details</h2>
        <form method="POST" class="req-form" onsubmit="return validateForm()">

            <label>Select Item: <span class="required">*</span></label>
            <select name="item_id" required>
                <option value="">-- Select Item --</option>
                <?php foreach ($items as $item): ?>
                    <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['name']) ?> (<?= $item['category'] ?>)</option>
                <?php endforeach; ?>
            </select>

            <label>Quantity: <span class="required">*</span></label>
            <input type="number" name="quantity" min="1" value="1" class="small" required>

            <label>Delivery Option: <span class="required">*</span></label>
            <select name="delivery_type" id="deliverySelect" onchange="toggleDelivery(this.value)" required>
                <option value="">-- Select --</option>
                <option value="Pickup">Pickup (collect at distribution centre)</option>
                <option value="Delivery">Delivery (deliver to my address)</option>
            </select>

            <div id="addressField" style="display:none">
                <label>Delivery Address: <span class="required">*</span></label>
                <input type="text" name="delivery_address" id="deliveryAddress" placeholder="Enter your full address...">
            </div>

            <label>Reason for Request: <span class="required">*</span></label>
            <select name="reason" required>
                <option value="">-- Select Reason --</option>
                <option value="Low income family">Low income family</option>
                <option value="Single parent household">Single parent household</option>
                <option value="Loss of job">Loss of job</option>
                <option value="Medical emergency">Medical emergency</option>
                <option value="Natural disaster affected">Natural disaster affected</option>
                <option value="Elderly living alone">Elderly living alone</option>
                <option value="Other">Other</option>
            </select>

            <label>Additional Description: <span class="required">*</span></label>
            <textarea name="description" placeholder="Please describe your situation in detail..." required></textarea>

            <div style="display:flex;gap:10px;margin-top:5px">
                <a href="aid_status.php" class="back-btn" style="text-decoration:none;">← Back</a>
                <button type="submit" class="submit-btn" style="margin:0;">Submit Request</button>
            </div>
        </form>
        <?php endif; ?>
    </section>
</div>

<footer class="dark-footer">
    <h4>Hand2Hand</h4>
    <p>Contact Us:</p>
    <p>Email: hand2hand@support.com</p>
</footer>

<script>
function toggleDelivery(val) {
    const field = document.getElementById('addressField');
    const input = document.getElementById('deliveryAddress');
    if (val === 'Delivery') { field.style.display='block'; input.required=true; }
    else { field.style.display='none'; input.required=false; }
}
function validateForm() {
    const item = document.querySelector('select[name="item_id"]').value;
    const qty  = document.querySelector('input[name="quantity"]').value;
    const del  = document.querySelector('select[name="delivery_type"]').value;
    const rsn  = document.querySelector('select[name="reason"]').value;
    const desc = document.querySelector('textarea[name="description"]').value.trim();
    if (!item) { alert('Please select an item!'); return false; }
    if (qty <= 0) { alert('Please enter a valid quantity!'); return false; }
    if (!del)  { alert('Please select delivery or pickup!'); return false; }
    if (del === 'Delivery' && !document.getElementById('deliveryAddress').value.trim()) { alert('Please enter your delivery address!'); return false; }
    if (!rsn)  { alert('Please select a reason!'); return false; }
    if (!desc || desc.length < 10) { alert('Please provide more details in the description!'); return false; }
    return true;
}
</script>
</body>
</html>