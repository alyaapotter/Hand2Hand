<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Requester') {
    header("Location: ../login.php"); exit();
}

$user_id = $_SESSION['user_id'];
$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'submit_request') {
        $description = trim($_POST['description']);
        if (empty($description)) { $error = "Please describe what you need."; }
        else {
            $date = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO REQUEST (date, status, description, user_id) VALUES (?, 'Pending', ?, ?)");
            $stmt->bind_param("ssi", $date, $description, $user_id);
            $stmt->execute();
            $success = "Request submitted!";
        }
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT d.quantity, d.date, i.name AS item_name, r.status
          FROM DISTRIBUTION d
          JOIN REQUEST r ON d.request_id=r.request_id
          JOIN ITEM i ON d.item_id=i.item_id
          WHERE r.user_id=?";

if ($search) {
    $query .= " AND i.name LIKE ?";
    $query .= " ORDER BY d.date DESC";
    $searchParam = "%$search%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $searchParam);
} else {
    $query .= " ORDER BY d.date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$distributions = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Aid Status - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">My Aid Status</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Submit Request -->
    <div class="form-section" style="margin-bottom:20px">
        <div class="form-section-title">Submit New Request</div>
        <form method="POST" onsubmit="return validateRequest()">
            <input type="hidden" name="action" value="submit_request">
            <div class="form-group form-group-full">
                <label>Describe what items you need:</label>
                <textarea name="description" rows="3" placeholder="e.g. family of 4, need rice, cooking oil and school supplies..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </form>
    </div>

    <input type="text" class="search-bar" placeholder="Search item..." onkeyup="filterTable(this.value)">

    <div class="section-title">Aid Distribution History</div>
    <div class="table-wrapper">
        <table class="data-table" id="mainTable">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity Available</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($distributions)): ?>
                    <tr><td colspan="4" class="empty-row">No items received yet.</td></tr>
                <?php else: ?>
                <?php foreach ($distributions as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['item_name']) ?></td>
                    <td><?= $d['quantity'] ?></td>
                    <td><?= date('d M Y', strtotime($d['date'])) ?></td>
                    <td><span class="badge badge-<?= strtolower($d['status']) ?>"><?= $d['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="page-footer">
    Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com
</div>

<script>
function filterTable(val) {
    val = val.toLowerCase();
    document.querySelectorAll('#mainTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
}
</script>
<script>
function validateRequest() {
    const desc = document.querySelector('textarea[name="description"]').value.trim();

    if (desc === '') {
        alert('Please describe what items you need!');
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