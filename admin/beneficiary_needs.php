<?php
session_start();
require_once '../includes/connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php"); exit();
}

$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_request') {
        $user_id     = intval($_POST['user_id']);
        $description = mysqli_real_escape_string($conn, trim($_POST['description']));
        $date        = date('Y-m-d');
        if (!$user_id || !$description) {
            $error = "Please fill in all fields.";
        } else {
            mysqli_query($conn, "INSERT INTO REQUEST (date, status, description, user_id) VALUES ('$date', 'Pending', '$description', $user_id)");
            $success = "Beneficiary need added!";
        }
    }
    if ($_POST['action'] == 'approve') {
        $id = intval($_POST['request_id']);
        mysqli_query($conn, "UPDATE REQUEST SET status='Approved' WHERE request_id=$id");
        $success = "Request approved!";
    }
    if ($_POST['action'] == 'reject') {
        $id = intval($_POST['request_id']);
        mysqli_query($conn, "UPDATE REQUEST SET status='Rejected' WHERE request_id=$id");
        $success = "Request rejected.";
    }
    if ($_POST['action'] == 'delete') {
        $id = intval($_POST['request_id']);
        mysqli_query($conn, "DELETE FROM REQUEST WHERE request_id=$id");
        $success = "Request deleted.";
    }
}

$ben_result    = mysqli_query($conn, "SELECT user_id, username, email, contact_number, address FROM USER WHERE role='Requester' ORDER BY username");
$beneficiaries = mysqli_fetch_all($ben_result, MYSQLI_ASSOC);

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$query  = "SELECT r.*, u.username, u.email FROM REQUEST r JOIN USER u ON r.user_id=u.user_id WHERE 1=1";
if ($search) $query .= " AND (u.username LIKE '%$search%' OR r.description LIKE '%$search%')";
$query   .= " ORDER BY r.date DESC";
$result   = mysqli_query($conn, $query);
$requests = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary Needs - Hand2Hand</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="page-container">
    <div class="page-title">Add Beneficiary Needs</div>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="form-section">
        <div class="form-section-title">Beneficiary Information</div>
        <form method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="action" value="add_request">
            <div class="form-group">
                <label>Name:</label>
                <select name="user_id" onchange="fillInfo(this)" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($beneficiaries as $b): ?>
                        <option value="<?= $b['user_id'] ?>"
                            data-contact="<?= htmlspecialchars($b['contact_number'] ?? '') ?>"
                            data-address="<?= htmlspecialchars($b['address'] ?? '') ?>">
                            <?= htmlspecialchars($b['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Contact Number:</label>
                <input type="text" name="contact_number" placeholder="(from profile)" readonly style="background:#e8d5d5">
            </div>
            <div class="form-group form-group-full">
                <label>Address:</label>
                <input type="text" name="address" placeholder="(from profile)" readonly style="width:100%;background:#e8d5d5">
            </div>
    </div>

    <div class="form-section">
        <div class="form-section-title">Needed Items</div>
        <div class="form-group">
            <label>Description of Need:</label>
            <textarea name="description" rows="3" placeholder="Describe what items are needed..." required style="width:300px"></textarea>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-title">Priority Level</div>
        <div class="form-group">
            <label>Priority:</label>
            <select name="priority">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add</button>
        </form>
    </div>

    <input type="text" class="search-bar" placeholder="Search..."
           onkeyup="filterTable(this.value)" id="searchInput"
           value="<?= htmlspecialchars($search) ?>">

    <div class="section-title">Beneficiary Needs List</div>
    <div class="table-wrapper">
        <table class="data-table" id="mainTable">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Date</th><th>Description</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="7" class="empty-row">No requests yet.</td></tr>
                <?php else: ?>
                <?php foreach ($requests as $i => $r): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($r['username']) ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= date('d M Y', strtotime($r['date'])) ?></td>
                    <td><?= htmlspecialchars(substr($r['description'], 0, 40)) ?>...</td>
                    <td><span class="badge badge-<?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
                    <td style="display:flex;gap:5px;flex-wrap:wrap">
                        <?php if ($r['status'] == 'Pending'): ?>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                            <button type="submit" class="btn-edit">✓ Approve</button>
                        </form>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                            <button type="submit" class="btn-edit">✗ Reject</button>
                        </form>
                        <?php elseif ($r['status'] == 'Approved'): ?>
                        <a href="distribution.php?request_id=<?= $r['request_id'] ?>" class="btn-edit">📦 Distribute</a>
                        <?php endif; ?>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                            <button type="submit" class="btn-edit">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function filterTable(val) {
    val = val.toLowerCase();
    document.querySelectorAll('#mainTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
}
function validateForm() {
    const user = document.querySelector('select[name="user_id"]').value;
    const desc = document.querySelector('textarea[name="description"]').value.trim();
    if (user === '') { alert('Please select a beneficiary!'); return false; }
    if (desc === '') { alert('Please enter description of need!'); return false; }
    if (desc.length < 10) { alert('Description is too short!'); return false; }
    return true;
}
function fillInfo(select) {
    const selected = select.options[select.selectedIndex];
    document.querySelector('input[name="contact_number"]').value = selected.dataset.contact || '';
    document.querySelector('input[name="address"]').value = selected.dataset.address || '';
}
</script>
</body>
</html>
