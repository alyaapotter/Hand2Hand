<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php"); exit();
}

$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'distribute') {
    $request_id = intval($_POST['request_id']);
    $dist_date  = $_POST['dist_date'] ?? '';
    $location   = mysqli_real_escape_string($conn, trim($_POST['location'] ?? ''));

    if (!$request_id || empty($dist_date) || empty($location)) {
        $error = "Please fill in all fields.";
    } else {
        // Get item_id and quantity from REQUEST
        $req_stmt = $conn->prepare("SELECT item_id, quantity FROM REQUEST WHERE request_id=? AND status='Approved'");
        $req_stmt->bind_param("i", $request_id);
        $req_stmt->execute();
        $req_data = $req_stmt->get_result()->fetch_assoc();

        if (!$req_data || !$req_data['item_id'] || $req_data['quantity'] <= 0) {
            $error = "This request has no item or quantity specified. Cannot distribute.";
        } else {
            $item_id  = $req_data['item_id'];
            $quantity = $req_data['quantity'];

            // Check inventory stock
            $inv_stmt = $conn->prepare("SELECT quantity FROM INVENTORY WHERE item_id=?");
            $inv_stmt->bind_param("i", $item_id);
            $inv_stmt->execute();
            $inv = $inv_stmt->get_result()->fetch_assoc();

            if (!$inv || $inv['quantity'] < $quantity) {
                $error = "Not enough stock! Available: " . ($inv['quantity'] ?? 0) . ", Needed: $quantity";
            } else {
                // Insert into DISTRIBUTION
                $ins = $conn->prepare("INSERT INTO DISTRIBUTION (request_id, item_id, quantity, date, location) VALUES (?,?,?,?,?)");
                $ins->bind_param("iiiss", $request_id, $item_id, $quantity, $dist_date, $location);
                $ins->execute();

                // Deduct inventory
                $conn->prepare("UPDATE INVENTORY SET quantity=quantity-? WHERE item_id=?")->bind_param("ii", $quantity, $item_id);
                $conn->query("UPDATE INVENTORY SET quantity=quantity-$quantity WHERE item_id=$item_id");

                // Update REQUEST status to Distributed
                $conn->query("UPDATE REQUEST SET status='Distributed' WHERE request_id=$request_id");

                $success = "Items distributed successfully!";
            }
        }
    }
}

// Only load Approved requests
$request_id_param = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
$selected_request = null;

if ($request_id_param) {
    $stmt = $conn->prepare("SELECT r.*, u.username, u.email, u.address, u.family_size, u.priority_level, i.name AS item_name
                            FROM REQUEST r
                            JOIN USER u ON r.user_id = u.user_id
                            LEFT JOIN ITEM i ON r.item_id = i.item_id
                            WHERE r.request_id=? AND r.status='Approved'");
    $stmt->bind_param("i", $request_id_param);
    $stmt->execute();
    $selected_request = $stmt->get_result()->fetch_assoc();
}

$ar_result = $conn->query("SELECT r.request_id, r.quantity, u.username, i.name AS item_name
                            FROM REQUEST r
                            JOIN USER u ON r.user_id=u.user_id
                            LEFT JOIN ITEM i ON r.item_id=i.item_id
                            WHERE r.status='Approved'
                            ORDER BY r.date DESC");
$approved_requests = $ar_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribute Items - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .info-card {
            background:#fef9ec; border-left:4px solid #f59e0b;
            border-radius:8px; padding:14px 18px; margin-bottom:8px;
        }
        .info-card .label { font-size:0.82em; color:#9a7a5a; margin-bottom:2px; }
        .info-card .value { font-size:1em; color:#5a3520; font-weight:600; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:10px; }
        .stock-ok   { color:#16a34a; font-weight:600; }
        .stock-low  { color:#ef4444; font-weight:600; }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">Distribute Items</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="beneficiary_needs.php" style="color:#065f46;font-weight:600">← Back to Requests</a></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" onsubmit="return validateDist()">
        <input type="hidden" name="action" value="distribute">

        <!-- Select Beneficiary -->
        <div class="form-section">
            <div class="form-section-title">Select Approved Request</div>
            <div class="form-group">
                <label>Beneficiary Request:</label>
                <select name="request_id" id="reqSelect" onchange="loadRequest(this.value)" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($approved_requests as $req): ?>
                        <option value="<?= $req['request_id'] ?>" <?= $request_id_param == $req['request_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($req['username']) ?> — <?= htmlspecialchars($req['item_name'] ?? 'Item not specified') ?> (Qty: <?= $req['quantity'] ?? '—' ?>) — #<?= $req['request_id'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($selected_request): ?>

        <!-- Request Details (read-only, from beneficiary's submission) -->
        <div class="form-section">
            <div class="form-section-title">Request Details</div>
            <div class="info-grid">
                <div class="info-card">
                    <div class="label">Beneficiary Name</div>
                    <div class="value"><?= htmlspecialchars($selected_request['username']) ?></div>
                </div>
                <div class="info-card">
                    <div class="label">Item Requested</div>
                    <div class="value"><?= htmlspecialchars($selected_request['item_name'] ?? 'Not specified') ?></div>
                </div>
                <div class="info-card">
                    <div class="label">Quantity</div>
                    <div class="value"><?= $selected_request['quantity'] ?? '—' ?></div>
                </div>
                <div class="info-card">
                    <div class="label">Delivery Option</div>
                    <div class="value"><?= htmlspecialchars($selected_request['delivery_option'] ?? '—') ?></div>
                </div>
                <div class="info-card">
                    <div class="label">Reason</div>
                    <div class="value"><?= htmlspecialchars($selected_request['reason'] ?? '—') ?></div>
                </div>
                <?php if ($selected_request['delivery_option'] == 'Delivery'): ?>
                <div class="info-card">
                    <div class="label">Beneficiary Address</div>
                    <div class="value"><?= htmlspecialchars($selected_request['address'] ?? 'Not provided') ?></div>
                </div>
                <?php endif; ?>
            </div>

            <?php
            // Check stock for this item
            $item_id_check = $selected_request['item_id'];
            $qty_needed = $selected_request['quantity'];
            $stock_row = null;
            if ($item_id_check) {
                $s = $conn->prepare("SELECT quantity FROM INVENTORY WHERE item_id=?");
                $s->bind_param("i", $item_id_check);
                $s->execute();
                $stock_row = $s->get_result()->fetch_assoc();
            }
            $stock_available = $stock_row['quantity'] ?? 0;
            $stock_ok = $stock_available >= $qty_needed;
            ?>
            <div style="margin-top:12px; padding:10px 14px; background:<?= $stock_ok ? '#f0fdf4' : '#fef2f2' ?>; border-radius:8px; border-left:4px solid <?= $stock_ok ? '#22c55e' : '#ef4444' ?>;">
                <strong>Stock Check:</strong>
                Available = <span class="<?= $stock_ok ? 'stock-ok' : 'stock-low' ?>"><?= $stock_available ?></span> |
                Needed = <strong><?= $qty_needed ?></strong> |
                <?= $stock_ok ? '<span class="stock-ok">✓ Sufficient</span>' : '<span class="stock-low">✗ Not enough stock!</span>' ?>
            </div>
        </div>

        <!-- Admin fills: Date + Location only -->
        <div class="form-section">
            <div class="form-section-title">Distribution Details</div>
            <div class="form-group">
                <label>Distribution Date: <span class="required">*</span></label>
                <input type="date" name="dist_date" required min="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label>Distribution Location: <span class="required">*</span></label>
                <?php if ($selected_request['delivery_option'] == 'Delivery'): ?>
                    <input type="text" name="location" style="width:350px" required
                           value="<?= htmlspecialchars($selected_request['address'] ?? '') ?>"
                           placeholder="Delivery address...">
                    <div style="font-size:0.8em;color:#9a7a5a;margin-top:4px;">Pre-filled with beneficiary's address. Edit if needed.</div>
                <?php else: ?>
                    <input type="text" name="location" style="width:350px" required
                           value="Warehouse"
                           placeholder="Pickup location...">
                    <div style="font-size:0.8em;color:#9a7a5a;margin-top:4px;">Pickup — default is Warehouse. Edit if different.</div>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" <?= !$stock_ok ? 'disabled style="opacity:0.5"' : '' ?>>
            📦 Confirm Distribution
        </button>
        <a href="beneficiary_needs.php" class="btn btn-outline" style="margin-left:10px">← Back</a>

        <?php else: ?>
            <?php if (!empty($approved_requests)): ?>
            <div style="padding:20px;background:#f0fdf4;border-radius:8px;color:#166534;">
                👆 Please select an approved request above to proceed.
            </div>
            <?php else: ?>
            <div style="padding:20px;background:#fef9ec;border-radius:8px;color:#92400e;">
                No approved requests yet. Go to <a href="beneficiary_needs.php">Beneficiary Needs</a> to approve requests first.
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </form>
</div>

<div class="page-footer">Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com</div>

<script>
function loadRequest(id) {
    if (id) window.location.href = 'distribution.php?request_id=' + id;
}
function validateDist() {
    const date = document.querySelector('input[name="dist_date"]')?.value;
    const loc  = document.querySelector('input[name="location"]')?.value.trim();
    if (!date) { alert('Please select a distribution date!'); return false; }
    if (!loc)  { alert('Please enter a distribution location!'); return false; }
    return true;
}
</script>
</body>
</html>