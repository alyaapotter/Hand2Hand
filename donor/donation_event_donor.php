<?php
session_start();
require_once '../includes/connect.php';

function getTargets($conn, $event_id)
{
    $stmt = $conn->prepare("
        SELECT i.name, t.quantity AS target,
               COALESCE(SUM(di.quantity), 0) AS current
        FROM TARGET t
        JOIN ITEM i ON t.item_id = i.item_id
        LEFT JOIN DONATION d ON d.event_id = t.event_id AND d.status = 'Received'
        LEFT JOIN DONATION_ITEM di ON di.donation_id = d.donation_id AND di.item_id = t.item_id
        WHERE t.event_id = ?
        GROUP BY t.target_id, i.name, t.quantity
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    return $map[$key] ?? 'default';
}

function displayProgress($items)
{
    foreach ($items as $item) {
        $percent = $item['target'] > 0
            ? min(round(($item['current'] / $item['target']) * 100), 100)
            : 0;
?>
        <div class="item">
            <p><?php echo htmlspecialchars($item['name']); ?></p>
            <div class="progress-container">
                <div class="progress-bar"
                    style="width: <?php echo $percent; ?>%;">
                </div>
            </div>
        </div>
    <?php
    }
}

function displayEvent($event, $items)
{
    $class = getEventClass($event['name']);
    $bg = $event['image_path']
        ? "../image/" . htmlspecialchars($event['image_path'])
        : "";
    $duration = htmlspecialchars($event['start_date']) . ' - ' . htmlspecialchars($event['end_date']);
    ?>
    <div class="event-card <?php echo $class; ?>"
        <?php echo $bg ? "style=\"background-image: url('$bg');\"" : ""; ?>>

        <div class="card-content">

            <h2><?php echo htmlspecialchars($event['name']); ?></h2>

            <p>Duration: <?php echo $duration; ?></p>

            <p>Status: <?php echo htmlspecialchars($event['status']); ?></p>

            <?php if (count($items) > 0): ?>
                <h3>Progress</h3>
                <?php displayProgress($items); ?>
            <?php endif; ?>

        </div>

        <button class="donate-btn" onclick="window.location.href='donate_item.php?event_id=<?= $event['event_id'] ?>'">
            Donate
        </button>

    </div>
<?php
}

$events = $conn->query("
    SELECT *
    FROM DONATIONEVENT
    WHERE status = 'Active'
    ORDER BY start_date ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events Page</title>

    <link rel="stylesheet" type="text/css" href="../css/formatBulan.css">

<body>

    <?php include '../includes/navbar.php'; ?>

    <div class="page-title2">
        <h1>Donation Events</h1>
    </div>

    <div class="search-box">
        <input type="text"
            class="search-bar"
            id="searchInput"
            placeholder="Search Event"
            onkeyup="searchEvents()">
    </div>

    <section class="event-grid">

        <?php if (count($events) > 0): ?>
            <?php foreach ($events as $event): ?>
                <?php $items = getTargets($conn, $event['event_id']); ?>
                <?php displayEvent($event, $items); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">No active events found.</p>
        <?php endif; ?>

    </section>

    <?php include '../includes/footer.php'; ?>

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