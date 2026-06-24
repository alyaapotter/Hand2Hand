<?php
session_start();
include('connect.php');

$name     = $conn->real_escape_string(trim($_POST['item']));
$quantity = $_POST['quantity'];

// Check if item exists
$check = $conn->query("SELECT item_id FROM item WHERE name = '$name'");

if ($check->num_rows == 0) {
    echo "<p style='text-align:center; color:red;'>Error: Item '$name' not found.</p>";
    echo "<p style='text-align:center;'><a href='updateInventory_admin.html'>Go Back</a></p>";
} else {
    $row     = $check->fetch_assoc();
    $item_id = $row['item_id'];

    // Check if inventory row exists for this item
    $inv_check = $conn->query("SELECT inventory_id FROM inventory WHERE item_id = '$item_id'");

    if ($inv_check->num_rows > 0) {
        // Update existing row
        $sql = "UPDATE inventory SET quantity = '$quantity' WHERE item_id = '$item_id'";
    } else {
        // Insert new row if missing
        $sql = "INSERT INTO inventory (item_id, quantity) VALUES ('$item_id', '$quantity')";
    }

    if ($conn->query($sql) === TRUE) {
        header('Location: inventory.php?updated=1');
        exit;
    } else {
        echo "<p style='text-align:center; color:red;'>Error: " . $conn->error . "</p>";
        echo "<p style='text-align:center;'><a href='updateInventory_admin.html'>Go Back</a></p>";
    }
}

$conn->close();
?>