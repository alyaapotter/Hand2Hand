<?php

function displayProgress(array $items)
{
    foreach ($items as $item) {
        echo "
        <div class='item'>
            <p>{$item[0]}</p>

            <div class='progress-container'>
                <div class='progress-bar' style='width: {$item[1]}%;'></div>
            </div>
        </div>";
    }
}

$events = [

    [
        "name" => "Food Bank",
        "class" => "foodbank",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["Rice", 70],
            ["Cooking Oil", 30],
            ["Sugar", 20],
            ["Flour", 10],
            ["Instant Noodles", 80]
        ]
    ],

    [
        "name" => "Back To School",
        "class" => "backtoschool",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["School Bag", 20],
            ["Exercise Books", 40],
            ["Pencil Case", 23],
            ["Stationery Set", 30],
            ["School Shoes", 70]
        ]
    ],

    [
        "name" => "Baby Care",
        "class" => "babycare",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["Baby Diapers", 10],
            ["Baby Wipes", 90],
            ["Baby Formula", 5],
            ["Baby Bottles", 20],
            ["Baby Clothes", 90]
        ]
    ],

    [
        "name" => "Her Essentials",
        "class" => "heressentials",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["Sanitary Pads", 60],
            ["Pantyliners", 50],
            ["Wet Wipes", 10],
            ["Shampoo", 3],
            ["Soap", 27]
        ]
    ],

    [
        "name" => "Medical Aid",
        "class" => "medicalaid",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["First Aid Kit", 30],
            ["Paracetamol", 100],
            ["Adhesive Bandages", 40],
            ["Antiseptic Solution", 2],
            ["Face Masks", 40]
        ]
    ],

    [
        "name" => "Wear & Share",
        "class" => "wearshare",
        "duration" => "1 May 2026 - 30 Jun 2026",
        "status" => "Active",
        "items" => [
            ["T-Shirts", 98],
            ["Pants", 40],
            ["Jackets", 10],
            ["Shoes", 50],
            ["Blankets", 30]
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

    <link rel="stylesheet" type="text/css" href="format.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <?php include('headermain.php'); ?>

    <div class="page-title">
        <h1>Donation Events</h1>
        <p>Description</p>
    </div>

    <div class="search-box">
        <input type="text" class="search-bar" placeholder="Search Event">
    </div>

    <section class="event-grid">

        <?php foreach ($events as $event) { ?>

            <div class="event-card <?php echo $event['class']; ?>">

                <div class="card-content">

                    <h2><?php echo $event['name']; ?></h2>

                    <p>Duration: <?php echo $event['duration']; ?></p>
                    <p>Status: <?php echo $event['status']; ?></p>

                    <h3>Progress</h3>

                    <?php displayProgress($event['items']); ?>

                </div>

                <button class="donate-btn">Donate Now</button>

            </div>

        <?php } ?>

    </section>

    <footer>

        <h4>Hand2Hand</h4>

        <p>Contact Us:</p>
        <p>Email: hand2hand@support.com</p>

    </footer>

</body>

</html>