<?php
session_start();
require_once '../includes/db.php';

$success = "";
$error = "";

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {

    $event_id = intval($_POST['event_id']);

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM TARGET WHERE event_id = ?")
             ->execute([$event_id]);
        $pdo->prepare("DELETE FROM DONATIONEVENT WHERE event_id = ?")
             ->execute([$event_id]);
        $pdo->commit();

        $_SESSION['success'] = "Event deleted successfully!";
        header("Location: donation_event.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Delete failed!";
    }
}

$stmt = $pdo->query("SELECT * FROM DONATIONEVENT ORDER BY start_date DESC");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events - Hand2Hand</title>
    <link rel="stylesheet" href="../css/format.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="page-container">
        <div class="page-title2"><h1>Donation Events</h1></div>

        <div class="search-box">
            <input type="text" class="search-bar" id="searchInput" placeholder="Search Event" onkeyup="searchEvents()">
        </div>

        <section class="admin-table">
            <h2>Donation Events List</h2>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="event-list" id="eventList">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="event-row">
                            <div class="event-info">
                                <h3><?= htmlspecialchars($event['name']) ?></h3>
                                <p>
                                    <?= htmlspecialchars($event['start_date']) ?>
                                    -
                                    <?= htmlspecialchars($event['end_date']) ?>
                                </p>
                                <span class="status <?= strtolower(htmlspecialchars($event['status'])) ?>">
                                    <?= htmlspecialchars($event['status']) ?>
                                </span>
                            </div>
                            <a href="edit_donation_event.php?id=<?= $event['event_id'] ?>">
                                <button class="edit-btn">Edit</button>
                            </a>
                            <form method="POST" onsubmit="return confirm('Delete this event?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-data">No event found.</p>
                <?php endif; ?>
            </div>

            <a href="event_management.php">
                <button type="button" class="submit-btn">
                    <h3>Create Event</h3>
                </button>
            </a>
        </section>
    </div>

    <div class="page-footer">
        Hand2Hand<br>Contact Us:<br>Email: hand2hand@support.com
    </div>

    <script>
        function searchEvents() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.event-row');
            rows.forEach(row => {
                const name = row.querySelector('h3').textContent.toLowerCase();
                row.style.display = name.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>

</html>