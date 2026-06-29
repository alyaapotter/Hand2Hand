<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php"); exit();
}

$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'distribute') {
    $request_id = intval($_POST['request_id']);
    $item_id    = intval($_POST['item_id']);
    $quantity   = intval($_POST['quantity']);
    $dist_date  = $_POST['dist_date'] ?? '';
    $location   = mysqli_real_escape_string($conn, trim($_POST['location'] ?? ''));
    if (!$request_id || !$item_id || $quantity <= 0 || empty($dist_date) || empty($location)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT quantity FROM INVENTORY WHERE item_id=?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $inv = $stmt->get_result()->fetch_assoc();
        
        if (!$inv || $inv['quantity'] < $quantity) {
            $error = "Not enough stock! Available: " . ($inv['quantity'] ?? 0);
        } else {
            $ins_stmt = $conn->prepare("INSERT INTO DISTRIBUTION (request_id, item_id, quantity, date, location) VALUES (?,?,?,?,?)");
            $ins_stmt->bind_param("iiiss", $request_id, $item_id, $quantity, $dist_date, $location);
            $ins_stmt->execute();
            
            $upd_inv_stmt = $conn->prepare("UPDATE INVENTORY SET quantity=quantity-? WHERE item_id=?");
            $upd_inv_stmt->bind_param("ii", $quantity, $item_id);
            $upd_inv_stmt->execute();
            
            $upd_req_stmt = $conn->prepare("UPDATE REQUEST SET status='Approved' WHERE request_id=?");
            $upd_req_stmt->bind_param("i", $request_id);
            $upd_req_stmt->execute();
            
            $success = "Items distributed successfully!";
        }
    }
}

$request_id_param = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
$selected_request = null;
if ($request_id_param) {
    $stmt = $conn->prepare("SELECT r.*, u.username, u.email, u.address, u.family_size, u.priority_level FROM REQUEST r JOIN USER u ON r.user_id=u.user_id WHERE r.request_id=?");
    $stmt->bind_param("i", $request_id_param);
    $stmt->execute();
    $selected_request = $stmt->get_result()->fetch_assoc();
}

$delivery_type_extracted = 'Pickup'; // default
$reason_extracted = 'N/A';
$delivery_address_extracted = '';
$need_extracted = '';

if ($selected_request) {
    $desc = $selected_request['description'];
    // Extract Delivery type
    if (preg_match('/Delivery type:\s*([^\.]+)/i', $desc, $matches)) {
        $delivery_type_extracted = trim($matches[1]);
    }
    // Extract Delivery Address
    if (preg_match('/Delivery Address:\s*([^\.]+)/i', $desc, $matches)) {
        $delivery_address_extracted = trim($matches[1]);
    }
    // Extract Reason
    if (preg_match('/Reason:\s*([^\.]+)/i', $desc, $matches)) {
        $reason_extracted = trim($matches[1]);
    }
    // Extract Need description
    if (preg_match('/Additional info:\s*(.*)/is', $desc, $matches)) {
        $need_extracted = trim($matches[1]);
    } else {
        $need_extracted = $desc; // fallback
    }
}

$ar_result = $conn->query("SELECT r.request_id, r.description, u.username FROM REQUEST r JOIN USER u ON r.user_id=u.user_id WHERE r.status IN ('Pending','Approved') ORDER BY r.date DESC");
$approved_requests = $ar_result->fetch_all(MYSQLI_ASSOC);

$items_result = $conn->query("SELECT i.item_id, i.name, i.category, COALESCE(inv.quantity,0) AS stock FROM ITEM i LEFT JOIN INVENTORY inv ON i.item_id=inv.item_id ORDER BY i.name");
$items = $items_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribute Items - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">Distribute Items</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" onsubmit="return validateDist()">
        <input type="hidden" name="action" value="distribute">

        <!-- Beneficiary Information -->
        <div class="form-section">
            <div class="form-section-title">Beneficiary Information</div>
            <div class="form-group">
                <label>Select Beneficiary:</label>
                <select name="request_id" id="reqSelect" onchange="loadRequest(this.value)" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($approved_requests as $req): ?>
                        <option value="<?= $req['request_id'] ?>" <?= $request_id_param == $req['request_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($req['username']) ?> — #<?= $req['request_id'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
             <?php if ($selected_request): ?>
            <div class="dist-info-row">Family Size: <span><?= htmlspecialchars($selected_request['family_size'] ?? '—') ?></span></div>
            <div class="dist-info-row">Priority: <span><?= htmlspecialchars($selected_request['priority_level'] ?? '—') ?></span></div>
            <div class="dist-info-row">Requested Delivery Option: <span id="requestedDeliveryType"><?= htmlspecialchars($delivery_type_extracted) ?></span></div>
            <div class="dist-info-row">Reason for Request: <span><?= htmlspecialchars($reason_extracted) ?></span></div>
            <div class="dist-info-row">Full Need Details: <span><?= htmlspecialchars($need_extracted) ?></span></div>
            <?php if ($delivery_type_extracted == 'Delivery'): ?>
                <div class="dist-info-row">Delivery Address: <span><?= htmlspecialchars(!empty($delivery_address_extracted) ? $delivery_address_extracted : ($selected_request['address'] ?? 'No address registered')) ?></span></div>
            <?php else: ?>
                <div class="dist-info-row">Delivery Address: <span>N/A (Pickup at Warehouse)</span></div>
            <?php endif; ?>
            <span id="beneAddress" style="display:none"><?= htmlspecialchars(!empty($delivery_address_extracted) ? $delivery_address_extracted : ($selected_request['address'] ?? '')) ?></span>
            <?php else: ?>
            <div class="dist-info-row">Family Size:</div>
            <div class="dist-info-row">Priority:</div>
            <?php endif; ?>
        </div>

        <!-- Delivery & Location Decision -->
        <div class="form-section">
            <div class="form-section-title">Delivery & Location Decision</div>
            <div class="form-group">
                <label>Is Reason Valid?:</label>
                <select name="reason_validity" id="validitySelect" onchange="checkValidity(this.value)" required>
                    <option value="Valid">Valid - Proceed with Delivery</option>
                    <option value="Invalid">Invalid - Pickup at Warehouse</option>
                </select>
            </div>
            <div class="form-group">
                <label>Distribution Location:</label>
                <input type="text" name="location" id="locationInput" style="width:300px" required>
            </div>
        </div>

        <!-- Item Information -->
        <div class="form-section">
            <div class="form-section-title">Item Information</div>
            <div class="form-group">
                <label>Select Item:</label>
                <select name="item_id" id="itemSelect" onchange="updateStock(this)" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_id'] ?>" data-stock="<?= $item['stock'] ?>">
                            <?= htmlspecialchars($item['name']) ?> (<?= $item['category'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="dist-info-row">Available Stock: <span id="stockDisplay">—</span></div>
            <div class="form-group">
                <label>Quantity To Distribute:</label>
                <input type="number" name="quantity" id="qtyInput" min="1" style="width:80px" required>
            </div>
            <div class="form-group">
                <label>Distribution Date:</label>
                <input type="date" name="dist_date" id="dateInput" required min="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <!-- Distribution Summary -->
        <div class="form-section">
            <div class="form-section-title">Distribution Summary</div>
            <div class="dist-info-row">Beneficiary: <span id="sumBeneficiary"><?= $selected_request ? htmlspecialchars($selected_request['username']) : '—' ?></span></div>
            <div class="dist-info-row">Item: <span id="sumItem">—</span></div>
            <div class="dist-info-row">Quantity: <span id="sumQty">—</span></div>
            <div class="dist-info-row">Date: <span id="sumDate">—</span></div>
<<<<<<< HEAD
=======
            <div class="dist-info-row">Location: <span id="sumLocation">—</span></div>
>>>>>>> origin/main
        </div>

        <button type="submit" class="btn btn-primary">Confirm Distribution</button>
        <a href="distribution_management.php" class="btn btn-outline" style="margin-left:10px">Back</a>
    </form>
</div>

<div class="page-footer">
    Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com
</div>

<script>
function loadRequest(id) {
    if (id) window.location.href = 'distribution.php?request_id=' + id;
}
function updateStock(sel) {
    const stock = sel.options[sel.selectedIndex]?.dataset.stock ?? '—';
    const name  = sel.options[sel.selectedIndex]?.text.split(' (')[0] ?? '—';
    document.getElementById('stockDisplay').textContent = stock;
    document.getElementById('sumItem').textContent = sel.value ? name : '—';
    updateSummary();
}
function updateSummary() {
    const qty = document.getElementById('qtyInput').value;
    const date = document.getElementById('dateInput').value;
    const loc = document.getElementById('locationInput').value;
    document.getElementById('sumQty').textContent = qty || '—';
    document.getElementById('sumDate').textContent = date || '—';
    document.getElementById('sumLocation').textContent = loc || '—';
}
function checkValidity(val) {
    const deliveryType = document.getElementById('requestedDeliveryType')?.textContent.trim() || 'Pickup';
    const address = document.getElementById('beneAddress')?.textContent.trim() || '';
    const locationInput = document.getElementById('locationInput');

    if (val === 'Valid' && deliveryType === 'Delivery') {
        locationInput.value = address !== 'No address registered' ? address : '';
    } else {
        locationInput.value = 'Warehouse';
    }
    updateSummary();
}
document.getElementById('qtyInput')?.addEventListener('input', updateSummary);
document.getElementById('dateInput')?.addEventListener('change', updateSummary);
document.getElementById('locationInput')?.addEventListener('input', updateSummary);

window.addEventListener('DOMContentLoaded', () => {
    const valSelect = document.getElementById('validitySelect');
    if (valSelect) {
        checkValidity(valSelect.value);
    }
});
</script>

<script>
function validateDist() {
    const request = document.querySelector('select[name="request_id"]').value;
    const item    = document.querySelector('select[name="item_id"]').value;
    const qty     = document.querySelector('input[name="quantity"]').value;
    const date    = document.querySelector('input[name="dist_date"]').value;
    const loc     = document.getElementById('locationInput').value.trim();
    const stock   = document.getElementById('stockDisplay').textContent;

    if (request === '') {
        alert('Please select a beneficiary request!');
        return false;
    }
    if (item === '') {
        alert('Please select an item!');
        return false;
    }
    if (qty === '' || qty <= 0) {
        alert('Please enter a valid quantity!');
        return false;
    }
    if (parseInt(qty) > parseInt(stock)) {
        alert('Quantity exceeds available stock! Stock available: ' + stock);
        return false;
    }
    if (date === '') {
        alert('Please select a distribution date!');
        return false;
    }
    if (loc === '') {
        alert('Please enter a distribution location!');
        return false;
    }
    return true;
}
</script>
</body>
</html>