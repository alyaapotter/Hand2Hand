<?php
session_start();
require_once '../includes/db.php';

$error = "";
$success = "";

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_event') {
    $name       = trim($_POST['name']);
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $status     = $_POST['status'];
    $item_ids   = $_POST['item_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];

    if (!$name || !$start_date || !$end_date || !$status) {
        $error = "Please fill in all event fields.";
    } else if ($end_date < $start_date) {
        $error = "End date cannot be earlier than start date.";
    } else if (empty($item_ids)) {
    $error = "Please add at least one target item.";
    } else {

        // Handle image upload
        $image_path = null;

        if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $filename = time() . '_' . basename($_FILES['event_image']['name']);
                $uploadDir = '../image/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($_FILES['event_image']['tmp_name'], $uploadDir . $filename)) {
                    $image_path = $filename;
                }
            } else {
                $error = "Invalid image format. Only jpg, jpeg, png, webp allowed.";
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO DONATIONEVENT (name, start_date, end_date, status, image_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $start_date, $end_date, $status, $image_path]);
            $new_event_id = $pdo->lastInsertId();

            foreach ($item_ids as $i => $item_id) {
                $qty = intval($quantities[$i]);
                if ($item_id && $qty > 0) {
                    $pdo->prepare("INSERT INTO TARGET (event_id, item_id, quantity) VALUES (?, ?, ?)")
                        ->execute([$new_event_id, $item_id, $qty]);
                }
            }

            $_SESSION['success'] = "Event created successfully!";
            header("Location: event_management.php");
            exit();
        }
    }
}

$items = $pdo->query("SELECT item_id, name, category FROM ITEM ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Donation Event - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="page-container">
        <div class="page-title2">
            <h1>Create Donation Event</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert2 alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert2 alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <section class="event-management">
            <form method="POST" class="event-form" id="mainForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_event">
                <div id="hidden-targets"></div>

                <!-- LEFT PANEL -->
                <div class="left-panel">

                    <label>Event Name</label>
                    <input type="text" name="name" required placeholder="Enter event name">

                    <label>Start Date</label>
                    <input type="date" name="start_date" required>

                    <label>End Date</label>
                    <input type="date" name="end_date" required>

                    <label>Status</label>
                    <select name="status" required>
                        <option value="">-- Select Status --</option>
                        <option value="Active">Active</option>
                        <option value="Completed">Completed</option>
                        <option value="Scheduled">Scheduled</option>
                    </select>

                    <label>Event Image</label>
                    <input type="file" name="event_image" accept="image/*">

                    <button type="submit" class="submit-btn">Create Event</button>

                    <button type="button" class="back-btn" onclick="window.location.href='donation_event.php'">
                        Back
                    </button>

                </div>

                <!-- RIGHT PANEL -->
                <div class="right-panel">

                    <label>Add Target Item</label>

                    <div class="target-input">
                        <select id="itemSelect">
                            <option value="">-- Select Item --</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['item_id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>">
                                    <?= htmlspecialchars($item['name']) ?> (<?= $item['category'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="target-input">
                            <input type="number" id="qtyInput" placeholder="Target Quantity" min="1">
                            <button type="button" class="add-item-btn" onclick="addTarget()">Add</button>
                        </div>
                    </div>

                    <div class="target-list">
                        <h3>Target Progress</h3>
                        <div id="progressList">
                            <p class="no-data" id="emptyProgress">No items added yet.</p>
                        </div>
                    </div>

                    <div class="target-list">
                        <h3>Target Item List</h3>
                        <div id="targetList">
                            <p class="no-data" id="emptyTarget">No items added yet.</p>
                        </div>
                    </div>

                </div>

            </form>
        </section>
    </div>

    <div class="page-footer">
        Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com
    </div>

    <script>
        let targets = [];

        function addTarget() {
            const sel = document.getElementById('itemSelect');
            const qty = parseInt(document.getElementById('qtyInput').value);
            const item_id = sel.value;
            const name = sel.options[sel.selectedIndex]?.dataset.name;

            if (!item_id) {
                alert('Please select an item.');
                return;
            }
            if (!qty || qty <= 0) {
                alert('Please enter a valid quantity.');
                return;
            }
            if (targets.find(t => t.item_id == item_id)) {
                alert('Item already added.');
                return;
            }

            targets.push({ item_id, name, quantity: qty });

            sel.value = '';
            document.getElementById('qtyInput').value = '';

            renderAll();
        }

        function removeTarget(index) {
            targets.splice(index, 1);
            renderAll();
        }

        function renderAll() {
            renderProgress();
            renderTargetList();
            renderHiddenInputs();
        }

        function renderProgress() {
            const container = document.getElementById('progressList');
            const emptyMsg = document.getElementById('emptyProgress');

            container.querySelectorAll('.progress-item').forEach(e => e.remove());

            if (targets.length === 0) {
                emptyMsg.style.display = '';
                return;
            }
            emptyMsg.style.display = 'none';

            targets.forEach(t => {
                const div = document.createElement('div');
                div.className = 'progress-item';
                div.innerHTML = `
                    <div class="progress-header">
                        <span>${t.name}</span>
                        <span>0 / ${t.quantity}</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width:0%;">0%</div>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function renderTargetList() {
            const container = document.getElementById('targetList');
            const emptyMsg = document.getElementById('emptyTarget');

            container.querySelectorAll('.target-row').forEach(e => e.remove());

            if (targets.length === 0) {
                emptyMsg.style.display = '';
                return;
            }
            emptyMsg.style.display = 'none';

            targets.forEach((t, i) => {
                const div = document.createElement('div');
                div.className = 'target-row';
                div.innerHTML = `
                    <span>${t.name}</span>
                    <span>Target: ${t.quantity}</span>
                    <span>Current: 0</span>
                    <div class="action-btns">
                        <button type="button" class="remove-btn" onclick="removeTarget(${i})">Remove</button>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function renderHiddenInputs() {
            const container = document.getElementById('hidden-targets');
            container.innerHTML = '';
            targets.forEach(t => {
                container.innerHTML += `
                    <input type="hidden" name="item_id[]" value="${t.item_id}">
                    <input type="hidden" name="quantity[]" value="${t.quantity}">
                `;
            });
        }

        setTimeout(function () {
            const alert = document.querySelector('.alert2');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 3000);
    </script>
</body>

</html>