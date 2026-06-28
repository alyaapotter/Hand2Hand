<?php
session_start();
require_once 'includes/connect.php';

$result = $conn->query("SELECT * FROM DONATIONEVENT WHERE status = 'Active' ORDER BY start_date ASC");
$events = $result->fetch_all(MYSQLI_ASSOC);

function getTargets($conn, $event_id) {
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
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
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
        <img src="logo.png" alt="Hand2Hand logo" width="80">
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
        <input type="text" class="search-bar" placeholder="Search Event">
    </div>

    <section class="event-grid">
        <div class="event-card foodbank">
            <div class="card-content">
                <h2>Food Bank</h2>

        <?php if (count($events) > 0): ?>
            <?php foreach ($events as $event): ?>
                <?php $targets = getTargets($conn, $event['event_id']); ?>

                <?php
                $bg = $event['image_path']
                    ? "image/" . htmlspecialchars($event['image_path'])
                    : "";
                ?>
                <div class="event-card <?= getEventClass($event['name']) ?>"
                    <?= $bg ? "style=\"background-image: url('$bg');\"" : "" ?>>
                    <div class="card-content">
                        <h2><?= htmlspecialchars($event['name']) ?></h2>

                <div class="item">
                    <p>Sugar</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 20%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Flour</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 10%;"></div>
                    </div>
                </div>
                <div class="item">
                    <p>Instant Noodles</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 80%;"></div>
                    </div>
                </div>

            </div>
            <button class="donate-btn">Donate Now</button>
        </div>

        <div class="event-card backtoschool">
            <div class="card-content">
                <h2>Back To School</h2>

                <p>Duration: 1 May 2026 - 30 Jun 2026</p>
                <p>Status: Active</p>

                <h3>Progress</h3>
                <div class="item">
                    <p>School Bag</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 20%;"></div>
                    </div>
                </div>
                    
                <div class="item">
                    <p>Exercise Books</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 40%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Pencil Case</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 23%;"></div>
                    </div>
                    <button class="donate-btn" onclick="window.location.href='login.php'">
                        Donate Now
                    </button>
                </div>

                <div class="item">
                    <p>Stationery Set</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 30%;"></div>
                    </div>
                </div>
                <div class="item">
                    <p>School Shoes</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 70%;"></div>
                    </div>
                </div>

            </div>
            <button class="donate-btn">Donate Now</button>
        </div>

        <div class="event-card babycare">
            <div class="card-content">
                <h2>Baby Care</h2>

                <p>Duration: 1 May 2026 - 30 Jun 2026</p>
                <p>Status: Active</p>

                <h3>Progress</h3>
                <div class="item">
                    <p>Baby Diapers</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 10%;"></div>
                    </div>
                </div>
                    
                <div class="item">
                    <p>Baby Wipes</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 90%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Baby Formula</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 5%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Baby Bottles</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 20%;"></div>
                    </div>
                </div>
                <div class="item">
                    <p>Baby Clothes</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 90%;"></div>
                    </div>
                </div>

            </div>
            <button class="donate-btn">Donate Now</button>
        </div>

        <div class="event-card heressentials">
            <div class="card-content">
                <h2>Her Essentials</h2>

                <p>Duration: 1 May 2026 - 30 Jun 2026</p>
                <p>Status: Active</p>

                <h3>Progress</h3>
                <div class="item">
                    <p>Sanitary Pads</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 60%;"></div>
                    </div>
                </div>
                    
                <div class="item">
                    <p>Pantyliners</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 50%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Wet Wipes</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 10%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Shampoo</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 3%;"></div>
                    </div>
                </div>
                <div class="item">
                    <p>Soap</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 27%;"></div>
                    </div>
                </div>

            </div>
            <button class="donate-btn">Donate Now</button>
        </div>

        <div class="event-card medicalaid">
            <div class="card-content">
                <h2>Medical Aid</h2>

                <p>Duration: 1 May 2026 - 30 Jun 2026</p>
                <p>Status: Active</p>

                <h3>Progress</h3>
                <div class="item">
                    <p>First Aid Kit</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 30%;"></div>
                    </div>
                </div>
                    
                <div class="item">
                    <p>Paracetamol</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 100%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Adhesive Bandages</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 40%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Antiseptic Solution</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 2%;"></div>
                    </div>
                </div>
                <div class="item">
                    <p>Face Masks</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 40%;"></div>
                    </div>
                </div>

            </div>
            <button class="donate-btn">Donate Now</button>
        </div>

        <div class="event-card wearshare">
            <div class="card-content">
                <h2>Wear & Share</h2>

                <p>Duration: 1 May 2026 - 30 Jun 2026</p>
                <p>Status: Active</p>

                <h3>Progress</h3>
                <div class="item">
                    <p>T-Shirts</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 98%;"></div>
                    </div>
                </div>
                    
                <div class="item">
                    <p>Pants</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 40%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Jackets</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 10%;"></div>
                    </div>
                </div>

                <div class="item">
                    <p>Shoes</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 50%;"></div>
                    </div>
                </div>
                <div class="item">
                    <p>Blankets</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 30%;"></div>
                    </div>
                </div>

            </div>
            <button class="donate-btn">Donate Now</button>
        </div>

    </section>

    <footer>
        <h4>Hand2Hand</h4>

        <p>Contact Us:</p>
        <p>Email: hand2hand@support.com</p>

    </footer>

</body>
</html>