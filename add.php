<?php

include('connect.php');

$name     = $conn->real_escape_string(trim($_POST['item']));
$category = $conn->real_escape_string(trim($_POST['category']));
$quantity = $_POST['quantity'];

// Check if item already exists
$check = $conn->query("SELECT item_id FROM item WHERE name = '$name'");

if ($check->num_rows > 0) {
    echo "<p style='text-align:center; color:red;'>Error: '$name' already exists. Use Update Stock instead.</p>";
    echo "<p style='text-align:center;'><a href='add_inventory.html'>Go Back</a></p>";
} else {
    // Insert into item table
    $sql_item = "INSERT INTO item (name, category) VALUES ('$name', '$category')";

    if ($conn->query($sql_item) === TRUE) {
        $item_id = $conn->insert_id;

        // Insert into inventory table
        $sql_inv = "INSERT INTO inventory (item_id, quantity) VALUES ('$item_id', '$quantity')";

        if ($conn->query($sql_inv) === TRUE) {
            header('Location: inventory.php?added=1');
            exit;
        } else {
            echo "<p style='text-align:center; color:red;'>Error: " . $conn->error . "</p>";
            echo "<p style='text-align:center;'><a href='add_inventory.html'>Go Back</a></p>";
        }
    } else {
        echo "<p style='text-align:center; color:red;'>Error: " . $conn->error . "</p>";
        echo "<p style='text-align:center;'><a href='add_inventory.html'>Go Back</a></p>";
    }
}

$conn->close();
?>