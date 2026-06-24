<?php

function displayEvent(array $event)
{
?>

    <div class="event-row">

        <div class="event-info">
            <h3><?php echo $event['name']; ?></h3>

            <p>
                <?php echo $event['start_date']; ?>
                -
                <?php echo $event['end_date']; ?>
            </p>

            <span class="status <?php echo strtolower($event['status']); ?>">
                <?php echo $event['status']; ?>
            </span>
        </div>

        <button class="edit-btn">Edit</button>

    </div>

<?php
}

$events = [

    [
        "name" => "Food Bank",
        "start_date" => "1 May 2026",
        "end_date" => "30 Jun 2026",
        "status" => "Active"
    ],

    [
        "name" => "Back To School",
        "start_date" => "1 May 2026",
        "end_date" => "30 Jun 2026",
        "status" => "Active"
    ],

    [
        "name" => "Baby Care",
        "start_date" => "1 May 2026",
        "end_date" => "30 Jun 2026",
        "status" => "Active"
    ],

    [
        "name" => "Her Essentials",
        "start_date" => "1 May 2026",
        "end_date" => "30 Jun 2026",
        "status" => "Active"
    ],

    [
        "name" => "Medical Aid",
        "start_date" => "1 May 2026",
        "end_date" => "30 Jun 2026",
        "status" => "Active"
    ],

    [
        "name" => "Wear & Share",
        "start_date" => "1 May 2026",
        "end_date" => "30 Jun 2026",
        "status" => "Active"
    ],

    [
        "name" => "Elder Aid",
        "start_date" => "1 Aug 2026",
        "end_date" => "31 Sep 2026",
        "status" => "Upcoming"
    ],

    [
        "name" => "Ramadan Blessing",
        "start_date" => "1 Feb 2026",
        "end_date" => "31 Mar 2026",
        "status" => "Ended"
    ]

];

?>

<!DOCTYPE html>
<html>

<head>
    <title>Donation Events Page (Admin)</title>

    <link rel="stylesheet" type="text/css" href="format.css">

</head>

<body>

    <?php include('headeradmin.php'); ?>

    <div class="page-title2">
        <h1>Donation Events</h1>
    </div>

    <div class="search-box">
        <input type="text"
            class="search-bar"
            placeholder="Search Event">
    </div>

    <section class="admin-table">

        <h2>Donation Events List</h2>

        <div class="event-list">

            <?php

            foreach ($events as $event) {
                displayEvent($event);
            }

            ?>

        </div>

        <button type="submit" class="submit-btn">
            <h3>Create Event</h3>
        </button>

    </section>

</body>

</html>