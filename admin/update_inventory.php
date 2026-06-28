<?php
include('../includes/connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $conn->real_escape_string(trim($_POST['item']));
    $quantity = $_POST['quantity'];

    // Check if item exists
    $check = $conn->query("SELECT item_id FROM item WHERE name = '$name'");
    if ($check->num_rows == 0) {
        $error = "Error: Item '$name' not found.";
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
            $error = "Error: " . $conn->error;
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <link rel="stylesheet" type="text/css" href="../css/format2.css"> 
=======
    <link rel="stylesheet" type="text/css" href="../css/updateInventory_admin.css"> 
>>>>>>> origin/main
    <title>Inventory Page: Update (Admin)</title>
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Hand2Hand logo" width="80">
            <h1>Hand2Hand</h1>
        </div>
        <nav>
            |
            <a href="dashboard.php">Dashboard</a> |
            <a href="beneficiary.php">Beneficiaries</a> |
            <a href="event_management.php">Events</a> |
            <a href="inventory.php">Inventory</a> |
            <a href="distribution.php">Distribution</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
    </header>

    <div class="title">
        <h1>Inventory</h1>
    </div>

    <section class="update-stock">
        <div>
            <p class="text">Update Stock</p>
        </div>

        <?php if (!empty($error)): ?>
            <p style="text-align:center; color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="update_inventory.php" onsubmit="return validateForm(event)">
            <div class="form-container">
                <label>Item:</label>
                <input type="text" name="item" id="item" placeholder="Item">
                <div id="itemError" class="error-msg"></div>
            </div>
            <div class="form-container">
                <label>Quantity:</label>
                <input type="text" name="quantity" id="quantity" placeholder="Quantity">
                <div id="qtyError" class="error-msg"></div>
            </div>
            <button type="submit" name="action" value="update">Update Item</button>
        </form>
    </section>

    <script>
        function validateForm(event) {
            let item     = document.getElementById("item").value.trim();
            let quantity = document.getElementById("quantity").value.trim();

            // Check empty fields
            if (item === "" || quantity === "") {
                alert("All fields are required.");
                event.preventDefault();
                return false;
            }

            // Check quantity is a positive whole number
            if (isNaN(quantity) || !Number.isInteger(Number(quantity)) || Number(quantity) <= 0) {
                alert("Quantity must be a non-negative whole number.");
                event.preventDefault();
                return false;
            }

            alert("Update Successful!");
            return true;
        }
    </script>
</body>
</html>