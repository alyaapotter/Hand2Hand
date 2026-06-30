<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include('../includes/connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $conn->real_escape_string(trim($_POST['item']));
    $category    = $conn->real_escape_string(trim($_POST['category']));
    $description = $conn->real_escape_string(trim($_POST['description']));

    // Check if item already exists
    $check = $conn->query("SELECT item_id FROM item WHERE name = '$name'");
    if ($check->num_rows > 0) {
        $error = "Error: '$name' already exists. Use Update Stock instead.";
    } else {
        // Insert into item table (item_id auto-increments)
        $sql_item = "INSERT INTO item (name, category, description) VALUES ('$name', '$category', '$description')";
        if ($conn->query($sql_item) === TRUE) {
            header('Location: inventory.php?added=1');
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
    <link rel="stylesheet" type="text/css" href="../css/format2.css"> 
    <title>Inventory Page: Add (Admin)</title>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

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
                <label>Name:</label>
                <input type="text" name="item" id="item">
                <div id="itemError" class="error-msg"></div>
            </div>
            <div class="form-container">
                <label>Category:</label>
                <input type="text" name="category" id="category">
                <div id="categoryError" class="error-msg"></div>
            </div>
            <div class="form-container">
                <label>Description:</label>
                <textarea name="description" id="description"></textarea>
                <div id="descriptionError" class="error-msg"></div>
            </div>
            <button type="submit" name="action" value="add">Add New Item</button>
            <button type="button" onclick="window.location.href='inventory.php'">Back</button>
        </form>
    </section>

    <script>
        function validateForm(event) {
            let item = document.getElementById("item").value.trim();

            // Check empty fields
            if (item === "") {
                alert("Name is required.");
                event.preventDefault();
                return false;
            }

            // alert("Add Item Successful!");
            return true;
        }
    </script>
</body>
</html>