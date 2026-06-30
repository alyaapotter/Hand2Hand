<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include('../includes/connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id     = intval($_POST['item_id']);
    $name        = trim($_POST['name']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);

    if (!$item_id) {
        $error = "Error: Please select an item.";
    } elseif ($name === '' || $category === '') {
        $error = "Error: Name and category are required.";
    } else {
        $name_esc        = $conn->real_escape_string($name);
        $category_esc    = $conn->real_escape_string($category);
        $description_esc = $conn->real_escape_string($description);

        $sql = "UPDATE item SET name = '$name_esc', category = '$category_esc', description = '$description_esc' WHERE item_id = '$item_id'";

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
$items_result = $conn->query("SELECT item_id, name, category, description FROM item ORDER BY category, name");
$items_list = [];
$items_map  = []; // used for JS auto-fill, keyed by item_id
while ($row = $items_result->fetch_assoc()) {
    $items_list[] = $row;
    $items_map[$row['item_id']] = [
        'name'        => $row['name'],
        'category'    => $row['category'],
        'description' => $row['description'],
    ];
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
                <select id="itemSelect" onchange="fillItemDetails()">
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
                <label>Item ID:</label>
                <input type="text" name="item_id" id="item" readonly>
            </div>

            <div class="form-container">
                <label>Name:</label>
                <input type="text" name="name" id="name">
            </div>

            <div class="form-container">
                <label>Category:</label>
                <input type="text" name="category" id="category">
            </div>

            <div class="form-container">
                <label>Description:</label>
                <textarea name="description" id="description"></textarea>
            </div>

            <button type="submit" name="action" value="update">Update Item</button>
            <button type="button" onclick="window.location.href='inventory.php'">Back</button>
        </form>
    </section>

    <script>
        // Item data passed from PHP, keyed by item_id
        const itemsData = <?php echo json_encode($items_map, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        function fillItemDetails() {
            const itemId = document.getElementById("itemSelect").value;
            const idField = document.getElementById("item");
            const nameField = document.getElementById("name");
            const categoryField = document.getElementById("category");
            const descriptionField = document.getElementById("description");

            if (itemId && itemsData[itemId]) {
                idField.value = itemId;
                nameField.value = itemsData[itemId].name;
                categoryField.value = itemsData[itemId].category;
                descriptionField.value = itemsData[itemId].description;
            } else {
                idField.value = "";
                nameField.value = "";
                categoryField.value = "";
                descriptionField.value = "";
            }
        }

        function validateForm(event) {
            let item = document.getElementById("item").value.trim();
            let name = document.getElementById("name").value.trim();
            let category = document.getElementById("category").value.trim();

            // Check empty fields
            if (item === "" || name === "" || category === "") {
                alert("Item, name, and category are required.");
                event.preventDefault();
                return false;
            }

            return true;
        }
    </script>
</body>
</html>