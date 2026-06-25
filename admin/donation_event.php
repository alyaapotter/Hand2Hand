<?php
session_start();
require_once '../includes/db.php';


$stmt = $pdo->query("SELECT * FROM DONATIONEVENT ORDER BY start_date ASC");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events - Hand2Hand</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="page-container">
        <div class="page-title">Donation Events</div>

        <div class="search-box">
            <input type="text" class="search-bar" id="searchInput" placeholder="Search Event" onkeyup="searchEvents()">
        </div>

        <section class="admin-table">
            <h2>Donation Events List</h2>

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