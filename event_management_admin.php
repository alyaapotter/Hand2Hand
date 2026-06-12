<?php

$eventName = "";
$startDate = "";
$endDate = "";
$description = "";
$status = "Active";

$targetItems = [
    [
        "itemName" => "Rice 5kg",
        "quantity" => 10
    ],
    [
        "itemName" => "Cooking Oil",
        "quantity" => 20
    ]
];

if (isset($_POST['submit'])) {
    $eventName = $_POST['eventName'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $description = $_POST['description'];
    $status = $_POST['status'];
}

function displayTargetItem($item)
{
?>

    <div class="target-row">

        <span class="item-name">
            <?php echo $item['itemName']; ?>
        </span>

        <span class="item-qty">
            <?php echo $item['quantity']; ?>
        </span>

        <button type="button" class="remove-btn">
            Remove
        </button>

    </div>

<?php
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Donation Event</title>
    <link rel="stylesheet" href="format.css">
</head>

<body>

    <?php include('headeradmin.php'); ?>

    <div class="page-title2">
        <h1>Donation Events</h1>
    </div>

    <section class="event-management">

        <form class="event-form" method="post">

            <div class="left-panel">

                <label>Event Name</label>
                <input type="text"
                    name="eventName"
                    value="<?php echo $eventName; ?>"
                    placeholder="Enter event name">

                <label>Start Date</label>
                <input type="date"
                    name="startDate"
                    value="<?php echo $startDate; ?>">

                <label>End Date</label>
                <input type="date"
                    name="endDate"
                    value="<?php echo $endDate; ?>">

                <label>Description</label>
                <textarea name="description"><?php echo $description; ?></textarea>

                <label>Status</label>

                <select name="status">

                    <option value="Active"
                        <?php if ($status == "Active") echo "selected"; ?>>
                        Active
                    </option>

                    <option value="Upcoming"
                        <?php if ($status == "Upcoming") echo "selected"; ?>>
                        Upcoming
                    </option>

                    <option value="Ended"
                        <?php if ($status == "Ended") echo "selected"; ?>>
                        Ended
                    </option>

                </select>

                <button type="submit"
                    name="submit"
                    class="submit-btn">
                    <h3>Create</h3>
                </button>

            </div>

            <div class="right-panel">

                <label>Target Item</label>

                <div class="target-input">

                    <input type="text"
                        name="itemName"
                        placeholder="e.g. Rice 5kg">

                    <input type="number"
                        name="quantity"
                        placeholder="Quantity"
                        min="1">

                    <button type="button"
                        class="add-item-btn">
                        Add
                    </button>

                </div>

                <div class="target-list">

                    <h3>Target Item List</h3>

                    <?php

                    foreach ($targetItems as $item) {
                        displayTargetItem($item);
                    }

                    ?>

                </div>

            </div>

        </form>

    </section>

</body>

</html>