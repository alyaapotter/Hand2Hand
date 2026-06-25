<?php

function displayProgress($items)
{
    foreach ($items as $item) {
?>

        <div class="item">
            <p><?php echo $item['name']; ?></p>

            <div class="progress-container">
                <div class="progress-bar"
                    style="width: <?php echo $item['progress']; ?>%;">
                </div>
            </div>
        </div>

    <?php
    }
}

function displayEvent($event)
{
    ?>

    <div class="event-card <?php echo $event['class']; ?>">

        <div class="card-content">

            <h2><?php echo $event['name']; ?></h2>

            <p>Duration: <?php echo $event['duration']; ?></p>

            <p>Status: <?php echo $event['status']; ?></p>

            <h3>Progress</h3>

            <?php displayProgress($event['items']); ?>

        </div>

        <button class="donate-btn">
            Donate Now
        </button>

    </div>

<?php
}

$events = [

    [
        "name" => "Food Bank",
        "class" => "foodbank",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["name" => "Rice", "progress" => 70],
            ["name" => "Cooking Oil", "progress" => 30],
            ["name" => "Sugar", "progress" => 20],
            ["name" => "Flour", "progress" => 10],
            ["name" => "Instant Noodles", "progress" => 80]
        ]
    ],

    [
        "name" => "Back To School",
        "class" => "backtoschool",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["name" => "School Bag", "progress" => 20],
            ["name" => "Exercise Books", "progress" => 40],
            ["name" => "Pencil Case", "progress" => 23],
            ["name" => "Stationery Set", "progress" => 30],
            ["name" => "School Shoes", "progress" => 70]
        ]
    ],

    [
        "name" => "Baby Care",
        "class" => "babycare",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["name" => "Baby Diapers", "progress" => 10],
            ["name" => "Baby Wipes", "progress" => 90],
            ["name" => "Baby Formula", "progress" => 5],
            ["name" => "Baby Bottles", "progress" => 20],
            ["name" => "Baby Clothes", "progress" => 90]
        ]
    ],

    [
        "name" => "Her Essentials",
        "class" => "heressentials",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["name" => "Sanitary Pads", "progress" => 60],
            ["name" => "Pantyliners", "progress" => 50],
            ["name" => "Wet Wipes", "progress" => 10],
            ["name" => "Shampoo", "progress" => 3],
            ["name" => "Soap", "progress" => 27]
        ]
    ],

    [
        "name" => "Medical Aid",
        "class" => "medicalaid",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["name" => "First Aid Kit", "progress" => 30],
            ["name" => "Paracetamol", "progress" => 100],
            ["name" => "Adhesive Bandages", "progress" => 40],
            ["name" => "Antiseptic Solution", "progress" => 2],
            ["name" => "Face Masks", "progress" => 40]
        ]
    ],

    [
        "name" => "Wear & Share",
        "class" => "wearshare",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["name" => "T-Shirts", "progress" => 98],
            ["name" => "Pants", "progress" => 40],
            ["name" => "Jackets", "progress" => 10],
            ["name" => "Shoes", "progress" => 50],
            ["name" => "Blankets", "progress" => 30]
        ]
    ]

];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Events Page</title>

    <link rel="stylesheet" type="text/css" href="../css/formatBulan.css">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <?php include('headerdonor.php'); ?>

    <div class="page-title2">
        <h1>Donation Events</h1>
    </div>

    <div class="search-box">
        <input type="text"
            class="search-bar"
            placeholder="Search Event">
    </div>

    <section class="event-grid">

        <?php

        foreach ($events as $event) {
            displayEvent($event);
        }

        ?>

    </section>

    <footer>

        <h4>Hand2Hand</h4>

        <p>Contact Us:</p>
        <p>Email: hand2hand@support.com</p>

    </footer>

</body>

</html>