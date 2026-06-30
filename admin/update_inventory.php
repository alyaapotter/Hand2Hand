<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include('../includes/connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id  = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);

    if (!$item_id) {
        $error = "Error: Please select an item.";
    } elseif ($quantity < 1 || $quantity > 1000) {
        $error = "Error: Quantity must be between 1 and 1000.";
    } else {
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

// Fetch items for dropdown, grouped/ordered by category
$items_result = $conn->query("SELECT item_id, name, category FROM item ORDER BY category, name");
$items_list = [];
while ($row = $items_result->fetch_assoc()) {
    $items_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/format2.css"> 
    <title>Inventory Page: Update (Admin)</title>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

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
                <select name="item_id" id="item">
                    <option value="">-- Select --</option>
                    <?php foreach ($items_list as $item): ?>
                        <option value="<?php echo $item['item_id']; ?>">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="itemError" class="error-msg"></div>
            </div>
            <div class="form-container">
                <label>Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="1" max="1000">
                <div id="qtyError" class="error-msg"></div>
            </div>
            <button type="submit" name="action" value="update">Update Item</button>
            <button type="button" onclick="window.location.href='inventory.php'">Back</button>
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

            // alert("Update Successful!");
            return true;
        }
    </script>
</body>
</html>