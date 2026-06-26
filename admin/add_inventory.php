<?php
include('../includes/connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $conn->real_escape_string(trim($_POST['item']));
    $category = $conn->real_escape_string(trim($_POST['category']));
    $quantity = $_POST['quantity'];

    // Check if item already exists
    $check = $conn->query("SELECT item_id FROM item WHERE name = '$name'");
    if ($check->num_rows > 0) {
        $error = "Error: '$name' already exists. Use Update Stock instead.";
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
                $error = "Error: " . $conn->error;
            }
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
    <link rel="stylesheet" type="text/css" href="../css/format2.css"> 
    <title>Inventory Page: Add (Admin)</title>
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

    <section class="add-item">
        <div>
            <p class="text">Add New Item</p>
        </div>

        <?php if (!empty($error)): ?>
            <p style="text-align:center; color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="add_inventory.php" onsubmit="return validateForm(event)">
            <div class="form-container">
                <label>Item:</label>
                <input type="text" name="item" id="item" placeholder="Item">
                <div id="itemError" class="error-msg"></div>
            </div>
            <div class="form-container">
                <label>Category:</label>
                <input type="text" name="category" id="category" placeholder="Category">
                <div id="categoryError" class="error-msg"></div>
            </div>
            <div class="form-container">
                <label>Quantity:</label>
                <input type="text" name="quantity" id="quantity" placeholder="Quantity">
                <div id="qtyError" class="error-msg"></div>
            </div>
            <button type="submit" name="action" value="add">Add New Item</button>
        </form>
    </section>

    <script>
        function validateForm(event) {
            let item     = document.getElementById("item").value.trim();
            let category = document.getElementById("category").value.trim();
            let quantity = document.getElementById("quantity").value.trim();

            // Check empty fields
            if (item === "" || category === "" || quantity === "") {
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

            alert("Add Item Successful!");
            return true;
        }
    </script>
</body>
</html>