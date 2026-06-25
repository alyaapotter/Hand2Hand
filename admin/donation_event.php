<?php

$event = [
    "name" => "Food Bank",
    "start_date" => "2026-05-01",
    "end_date" => "2026-06-30",
    "description" => "Provide food essentials to families in need.",
    "status" => "Active"
];

$targets = [
    [
        "name" => "Rice 5kg",
        "target" => 100,
        "current" => 80
    ],
    [
        "name" => "Cooking Oil",
        "target" => 60,
        "current" => 45
    ],
    [
        "name" => "Milk Powder",
        "target" => 40,
        "current" => 20
    ]
];

function displayProgress($item)
{
    $percent = ($item['current'] / $item['target']) * 100;
?>

    <div class="progress-item">

        <div class="progress-header">
            <span><?php echo $item['name']; ?></span>
            <span>
                <?php echo $item['current']; ?>
                /
                <?php echo $item['target']; ?>
            </span>
        </div>

        <div class="progress-container">
            <div class="progress-bar" style="width: <?php echo $percent; ?>%;">
                <?php echo round($percent); ?>%
            </div>
        </div>

    </div>

<?php
}

function displayTargetRow($item)
{
?>

    <div class="target-row">

        <span><?php echo $item['name']; ?></span>
        <span>Target: <?php echo $item['target']; ?></span>
        <span>Current: <?php echo $item['current']; ?></span>

        <div class="action-btns">
            <button type="button" class="edit-btn">Edit</button>
            <button type="button" class="remove-btn">Remove</button>
        </div>

    </div>

<?php
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Event Management</title>
    <link rel="stylesheet" href="../css/formatBulan.css">
</head>

<body>

    <?php include('header.php'); ?>

    <div class="page-title2">
        <h1>Event Management</h1>
    </div>

    <section class="event-management">

        <form class="event-form">

            <!-- LEFT PANEL -->
            <div class="left-panel">

                <label>Event Name</label>
                <input type="text" value="<?php echo $event['name']; ?>">

                <label>Start Date</label>
                <input type="date" value="<?php echo $event['start_date']; ?>">

                <label>End Date</label>
                <input type="date" value="<?php echo $event['end_date']; ?>">

                <label>Description</label>
                <textarea rows="4"><?php echo $event['description']; ?></textarea>

                <label>Status</label>

                <select>

                    <option <?php if ($event['status'] == "Active") echo "selected"; ?>>
                        Active
                    </option>

                    <option <?php if ($event['status'] == "Upcoming") echo "selected"; ?>>
                        Upcoming
                    </option>

                    <option <?php if ($event['status'] == "Ended") echo "selected"; ?>>
                        Ended
                    </option>

                </select>

                <button type="submit" class="submit-btn">
                    Save Changes
                </button>

            </div>

            <!-- RIGHT PANEL -->
            <div class="right-panel">

                <label>Add Target Item</label>

                <div class="target-input">
                    <input type="text" placeholder="Item Name">
                    <input type="number" placeholder="Target Quantity">
                    <button type="button" class="add-item-btn">Add</button>
                </div>

                <div class="target-list">

                    <h3>Target Progress</h3>

                    <?php
                    foreach ($targets as $item) {
                        displayProgress($item);
                    }
                    ?>

                </div>

                <div class="target-list">

                    <h3>Target Item List</h3>

                    <?php
                    foreach ($targets as $item) {
                        displayTargetRow($item);
                    }
                    ?>

                </div>

            </div>

        </form>

    </section>

</body>

</html>