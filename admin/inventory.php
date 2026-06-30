<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include('../includes/connect.php');
// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_clean = $conn->real_escape_string($search);
if ($search_clean !== '') {
    $sql = "SELECT i.item_id, i.name, i.category, i.description, inv.quantity
            FROM item i
            LEFT JOIN inventory inv ON i.item_id = inv.item_id
            WHERE i.item_id LIKE '%$search_clean%'
               OR i.name LIKE '%$search_clean%'
               OR i.category LIKE '%$search_clean%'
               OR i.description LIKE '%$search_clean%'
               OR inv.quantity LIKE '%$search_clean%'
            ORDER BY i.item_id DESC";
} else {
    $sql = "SELECT i.item_id, i.name, i.category, i.description, inv.quantity
            FROM item i
            LEFT JOIN inventory inv ON i.item_id = inv.item_id
            ORDER BY i.item_id DESC";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/format2.css"> 
    <title>Inventory Page (Admin)</title>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="search">
        <h1>Inventory</h1>
    </div>
    <form method="GET" action="inventory.php">
        <input class="search-bar" type="text" name="search"
               placeholder="Search ID, Name, Category, Description or Quantity"
               value="<?php echo htmlspecialchars($search); ?>">
    </form>
    <section class="inventory-list">
        <div>
            <p class="text">Inventory List</p>
        </div>
        <?php if (isset($_GET['added'])): ?>
            <p style="color:green; margin:10px 0;">✓ Item added successfully!</p>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
            <p style="color:green; margin:10px 0;">✓ Stock updated successfully!</p>
        <?php endif; ?>
        <table class="table-container">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Quantity Available</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity'] ?? 0); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <button onclick="window.location.href='update_inventory.php'">Update Stock</button>
        <button onclick="window.location.href='add_inventory.php'">Add New Item</button>
    </section>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('added') === '1') {
            alert("Add Item Successful!");
        }
        if (urlParams.get('updated') === '1') {
            alert("Stock Updated Successfully!");
        }
    </script>
    
</body>
</html>
<?php $conn->close(); ?>