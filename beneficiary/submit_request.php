<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Requester') {
    header("Location: ../login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id        = intval($_POST['item_id']);
    $quantity       = intval($_POST['quantity']);
    $delivery_type  = mysqli_real_escape_string($conn, $_POST['delivery_type']);
    $reason         = mysqli_real_escape_string($conn, trim($_POST['reason']));
    $description    = mysqli_real_escape_string($conn, trim($_POST['description']));
    $date           = date('Y-m-d');

    $delivery_address = isset($_POST['delivery_address']) ? mysqli_real_escape_string($conn, trim($_POST['delivery_address'])) : '';

    if (!$item_id || $quantity <= 0 || empty($delivery_type) || empty($reason) || empty($description)) {
        $error = "Please fill in all required fields.";
    } else {
        $full_description = "Item requested: See item field. Delivery type: $delivery_type. Delivery Address: $delivery_address. Reason: $reason. Additional info: $description";
        mysqli_query($conn, "INSERT INTO REQUEST (date, status, description, user_id) VALUES ('$date', 'Pending', '$full_description', $user_id)");
        $request_id = mysqli_insert_id($conn);
        $success = "Your request has been submitted! The admin will review it soon.";
    }
}

// Fetch items for dropdown
$item_result = mysqli_query($conn, "SELECT item_id, name, category FROM ITEM ORDER BY category, name");
$items       = mysqli_fetch_all($item_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Request - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">Submit Aid Request</div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?>
            <br><a href="aid_status.php" style="color:#065f46;font-weight:600">← Back to Aid Status</a>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" onsubmit="return validateForm()">

        <!-- Item Selection -->
        <div class="form-section">
            <div class="form-section-title">Item Request</div>
            <div class="form-group">
                <label>Select Item: <span class="required">*</span></label>
                <select name="item_id" id="itemSelect" required>
                    <option value="">-- Select Item --</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['name']) ?> (<?= $item['category'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity: <span class="required">*</span></label>
                <input type="number" name="quantity" min="1" value="1" style="width:80px" required>
            </div>
        </div>

        <!-- Delivery Option -->
        <div class="form-section">
            <div class="form-section-title">Delivery Option</div>
            <div class="form-group">
                <label>Choose Option: <span class="required">*</span></label>
                <select name="delivery_type" id="deliverySelect" onchange="toggleDelivery(this.value)" required>
                    <option value="">-- Select --</option>
                    <option value="Pickup">Pickup (collect at distribution centre)</option>
                    <option value="Delivery">Delivery (deliver to my address)</option>
                </select>
            </div>

            <!-- Show address field only if Delivery -->
            <div id="addressField" style="display:none">
                <div class="form-group form-group-full">
                    <label>Delivery Address: <span class="required">*</span></label>
                    <input type="text" name="delivery_address" id="deliveryAddress" placeholder="Enter your full address..." style="width:100%">
                </div>
            </div>
        </div>

        <!-- Reason -->
        <div class="form-section">
            <div class="form-section-title">Request Details</div>
            <div class="form-group form-group-full">
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
            </div>
            <div class="form-group form-group-full">
                <label>Additional Description: <span class="required">*</span></label>
                <textarea name="description" rows="4"
                    placeholder="Please describe your situation in detail. E.g. family size, specific needs, urgency..."
                    required></textarea>
            </div>
        </div>

        <div style="display:flex;gap:10px">
            <a href="aid_status.php" class="btn btn-outline">← Back</a>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<div class="page-footer">Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com</div>

<script>
function toggleDelivery(val) {
    const addressField = document.getElementById('addressField');
    const addressInput = document.getElementById('deliveryAddress');
    if (val === 'Delivery') {
        addressField.style.display = 'block';
        addressInput.required = true;
    } else {
        addressField.style.display = 'none';
        addressInput.required = false;
    }
}

function validateForm() {
    const item     = document.querySelector('select[name="item_id"]').value;
    const qty      = document.querySelector('input[name="quantity"]').value;
    const delivery = document.querySelector('select[name="delivery_type"]').value;
    const reason   = document.querySelector('select[name="reason"]').value;
    const desc     = document.querySelector('textarea[name="description"]').value.trim();

    if (item === '') {
        alert('Please select an item!');
        return false;
    }
    if (qty <= 0) {
        alert('Please enter a valid quantity!');
        return false;
    }
    if (delivery === '') {
        alert('Please select delivery or pickup option!');
        return false;
    }
    if (delivery === 'Delivery') {
        const addr = document.getElementById('deliveryAddress').value.trim();
        if (addr === '') {
            alert('Please enter your delivery address!');
            return false;
        }
    }
    if (reason === '') {
        alert('Please select a reason for your request!');
        return false;
    }
    if (desc === '') {
        alert('Please provide additional description!');
        return false;
    }
    if (desc.length < 10) {
        alert('Description is too short. Please provide more details!');
        return false;
    }
    return true;
}
</script>
</body>
</html>