<?php
session_start();
require_once 'includes/db.php';

$events = $pdo->query("
    SELECT * 
    FROM DONATIONEVENT 
    WHERE status = 'Active'
    ORDER BY start_date ASC
")->fetchAll();

function getTargets($pdo, $event_id)
{
    $stmt = $pdo->prepare("
        SELECT i.name, t.quantity AS target,
               COALESCE(SUM(di.quantity), 0) AS current
        FROM TARGET t
        JOIN ITEM i ON t.item_id = i.item_id
        LEFT JOIN DONATION d ON d.event_id = t.event_id AND d.status = 'Received'
        LEFT JOIN DONATION_ITEM di ON di.donation_id = d.donation_id AND di.item_id = t.item_id
        WHERE t.event_id = ?
        GROUP BY t.target_id, i.name, t.quantity
    ");
    $stmt->execute([$event_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventClass($name)
{
    $map = [
        'food bank'      => 'foodbank',
        'back to school' => 'backtoschool',
        'baby care'      => 'babycare',
        'her essentials' => 'heressentials',
        'medical aid'    => 'medicalaid',
        'wear & share'   => 'wearshare',
    ];
    $key = strtolower(trim($name));
    return $map[$key] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events Page</title>
    <link rel="stylesheet" type="text/css" href="css/formatBulan.css">
</head>

<body>
    <header class="header1">

        <div class="logo">
            <img src="image/logo.png" alt="Hand2Hand logo" width="80">
        </div>

        <nav>
            <a href="home.php">Hand2Hand</a> |
            <a href="about.php">About Us</a> |
            <a href="event.php">Events</a> |
            <a href="login.php">Login</a>
        </nav>

    </header>

    <div class="page-title">
        <h1>Donation Events</h1>
        <p>Description</p>
    </div>

    <div class="search-box">
        <input type="text" class="search-bar" id="searchInput" placeholder="Search Event" onkeyup="searchEvents()">
    </div>

    <section class="event-grid">

        <?php if (count($events) > 0): ?>
            <?php foreach ($events as $event): ?>
                <?php $targets = getTargets($pdo, $event['event_id']); ?>

                <?php
                $bg = $event['image_path']
                    ? "image/" . htmlspecialchars($event['image_path'])
                    : "";
                ?>
                <div class="event-card <?= getEventClass($event['name']) ?>"
                    <?= $bg ? "style=\"background-image: url('$bg');\"" : "" ?>>
                    <div class="card-content">
                        <h2><?= htmlspecialchars($event['name']) ?></h2>

                        <p>Duration: <?= htmlspecialchars($event['start_date']) ?> - <?= htmlspecialchars($event['end_date']) ?></p>
                        <p>Status: <?= htmlspecialchars($event['status']) ?></p>

                        <?php if (count($targets) > 0): ?>
                            <h3>Progress</h3>
                            <?php foreach ($targets as $item): ?>
                                <?php
                                $percent = $item['target'] > 0
                                    ? min(($item['current'] / $item['target']) * 100, 100)
                                    : 0;
                                ?>
                                <div class="item">
                                    <p><?= htmlspecialchars($item['name']) ?></p>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?= round($percent) ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                    <button class="donate-btn" onclick="window.location.href='login.php'">
                        Donate Now
                    </button>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">No events found.</p>
        <?php endif; ?>

    </section>

    <footer>
        <h4>Hand2Hand</h4>
        <p>Contact Us:</p>
        <p>Email: hand2hand@support.com</p>
    </footer>

    <script>
        function searchEvents() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.event-card');
            cards.forEach(card => {
                const name = card.querySelector('h2').textContent.toLowerCase();
                card.style.display = name.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>

</html>