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

    if (!$request_id || empty($dist_date)) {
        $error = "Please select a distribution date.";
    } else {
        // Fetch item, quantity, delivery option and address from database automatically
        $req_stmt = $conn->prepare("SELECT r.item_id, r.quantity, r.delivery_option, u.address 
                                    FROM REQUEST r 
                                    JOIN USER u ON r.user_id = u.user_id 
                                    WHERE r.request_id=? AND r.status='Approved'");
        $req_stmt->bind_param("i", $request_id);
        $req_stmt->execute();
        $req_data = $req_stmt->get_result()->fetch_assoc();

        if (!$req_data || !$req_data['item_id'] || $req_data['quantity'] <= 0) {
            $error = "This request has no item or quantity specified.";
        } else {
            $item_id  = $req_data['item_id'];
            $quantity = $req_data['quantity'];
            
            // Connect location automatically: if Delivery -> use user address, else -> Warehouse
            $location = ($req_data['delivery_option'] == 'Delivery') ? $req_data['address'] : 'Warehouse';

            $inv_stmt = $conn->prepare("SELECT quantity FROM INVENTORY WHERE item_id=?");
            $inv_stmt->bind_param("i", $item_id);
            $inv_stmt->execute();
            $inv = $inv_stmt->get_result()->fetch_assoc();

            if (!$inv || $inv['quantity'] < $quantity) {
                $error = "Not enough stock! Available: " . ($inv['quantity'] ?? 0) . ", Needed: $quantity";
            } else {
                $ins = $conn->prepare("INSERT INTO DISTRIBUTION (request_id, item_id, quantity, date, location) VALUES (?,?,?,?,?)");
                $ins->bind_param("iiiss", $request_id, $item_id, $quantity, $dist_date, $location);
                $ins->execute();
                $conn->query("UPDATE INVENTORY SET quantity=quantity-$quantity WHERE item_id=$item_id");
                $conn->query("UPDATE REQUEST SET status='Distributed' WHERE request_id=$request_id");
                $success = "Items distributed successfully!";
            }
        }
    }
}

$request_id_param = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
$selected_request = null;

if ($request_id_param) {
    $stmt = $conn->prepare("SELECT r.*, u.username, u.address, i.name AS item_name
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

$stock_available = 0;
$stock_ok = false;
if ($selected_request && $selected_request['item_id']) {
    $s = $conn->prepare("SELECT quantity FROM INVENTORY WHERE item_id=?");
    $s->bind_param("i", $selected_request['item_id']);
    $s->execute();
    $stock_row = $s->get_result()->fetch_assoc();
    $stock_available = $stock_row['quantity'] ?? 0;
    $stock_ok = $stock_available >= $selected_request['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribute Items - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
    <style>
        .dist-form label { display:block; font-weight:bold; color:#443025; margin-bottom:5px; font-size:14px; }
        .dist-form input[type="date"],
        .dist-form select { width:100%; padding:10px; margin-bottom:18px; border:2px solid #A86B6C; border-radius:10px; background:#FFE4EF; color:#443025; font-size:14px; box-sizing:border-box; outline:none; }
        .dist-form select { width:auto; min-width:350px; }
        .info-row { display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px; }
        .info-card { background:#FFE4EF; border:1px solid #A86B6C; border-radius:10px; padding:12px 16px; min-width:180px; flex:1; }
        .info-card .lbl { font-size:11px; color:#A86B6C; font-weight:bold; text-transform:uppercase; margin-bottom:3px; }
        .info-card .val { font-size:14px; color:#443025; font-weight:600; }
        .stock-ok  { background:#d1fae5; border-color:#22c55e; }
        .stock-bad { background:#fee2e2; border-color:#ef4444; }
        .stock-ok .val  { color:#166534; }
        .stock-bad .val { color:#991b1b; }

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
        <h1>Distribute Items</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($success) ?> <a href="beneficiary_needs.php" style="color:#1a5c1a;font-weight:bold">← Back to Requests</a></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error" style="margin: 12px 0 15px 45px; width: 30%;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="admin-table">
        <h2>Select Approved Request</h2>
        <form method="POST" class="dist-form" onsubmit="return validateDist()">
            <input type="hidden" name="action" value="distribute">

            <label>Beneficiary Request:</label>
            <select name="request_id" onchange="loadRequest(this.value)" required>
                <option value="">-- Select --</option>
                <?php foreach ($approved_requests as $req): ?>
                    <option value="<?= $req['request_id'] ?>" <?= $request_id_param == $req['request_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($req['username']) ?> — <?= htmlspecialchars($req['item_name'] ?? 'Item not specified') ?> (Qty: <?= $req['quantity'] ?? '—' ?>) — #<?= $req['request_id'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($selected_request): ?>
            <h2 style="margin:20px 0 12px; color:#443025;">Request Details</h2>
            <div class="info-row">
                <div class="info-card">
                    <div class="lbl">Beneficiary</div>
                    <div class="val"><?= htmlspecialchars($selected_request['username']) ?></div>
                </div>
                <div class="info-card">
                    <div class="lbl">Item Requested</div>
                    <div class="val"><?= htmlspecialchars($selected_request['item_name'] ?? 'Not specified') ?></div>
                </div>
                <div class="info-card">
                    <div class="lbl">Quantity</div>
                    <div class="val"><?= $selected_request['quantity'] ?? '—' ?></div>
                </div>
                <div class="info-card">
                    <div class="lbl">Delivery Option</div>
                    <div class="val"><?= htmlspecialchars($selected_request['delivery_option'] ?? '—') ?></div>
                </div>
                <div class="info-card">
                    <div class="lbl">Reason</div>
                    <div class="val"><?= htmlspecialchars($selected_request['reason'] ?? '—') ?></div>
                </div>
                <div class="info-card <?= $stock_ok ? 'stock-ok' : 'stock-bad' ?>">
                    <div class="lbl">Stock Check</div>
                    <div class="val"><?= $stock_ok ? '✓ Sufficient ('.$stock_available.' available)' : '✗ Not enough ('.$stock_available.' available, need '.$selected_request['quantity'].')' ?></div>
                </div>
            </div>

            <h2 style="margin:20px 0 12px; color:#443025;">Distribution Details</h2>
            
            <label>Distribution Date: <span style="color:#ef4444">*</span></label>
            <input type="date" name="dist_date" required min="<?= date('Y-m-d') ?>" style="width:250px">

            <label>Distribution Location (Auto Connected):</label>
            <p style="font-size:15px; font-weight:bold; color:#443025; margin-bottom:18px; padding-left:5px;">
                📍 <?= $selected_request['delivery_option'] == 'Delivery' ? htmlspecialchars($selected_request['address'] ?? 'No address registered') : 'Warehouse (Collect at distribution centre)' ?>
            </p>

            <button type="submit" class="submit-btn" <?= !$stock_ok ? 'disabled style="opacity:0.5;cursor:not-allowed"' : '' ?>>📦 Confirm Distribution</button>
            <a href="beneficiary_needs.php" class="back-btn" style="margin-left:10px; text-decoration:none;">← Back</a>

            <?php else: ?>
                <?php if (!empty($approved_requests)): ?>
                    <p style="color:#7a5c3a;font-style:italic;">👆 Select an approved request above to continue.</p>
                <?php else: ?>
                    <p style="color:#A86B6C;">No approved requests yet. <a href="beneficiary_needs.php" style="color:#443025;font-weight:bold">Go approve requests first →</a></p>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </section>
</div>

<footer class="dark-footer">
    <h4>Hand2Hand</h4>
    <p>Contact Us:</p>
    <p>Email: hand2hand@support.com</p>
</footer>

<script>
function loadRequest(id) { if (id) window.location.href = 'distribution.php?request_id=' + id; }
function validateDist() {
    const date = document.querySelector('input[name="dist_date"]')?.value;
    if (!date) { alert('Please select a distribution date!'); return false; }
    return true;
}
</script>
</body>
</html>