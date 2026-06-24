<?php

include('connect.php');

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_clean = $conn->real_escape_string($search);

if ($search_clean !== '') {
    $sql = "SELECT i.name, inv.quantity,
                   CASE WHEN inv.quantity > 0 THEN 'Available' ELSE 'Out of Stock' END AS status
            FROM item i
            LEFT JOIN inventory inv ON i.item_id = inv.item_id
            WHERE i.name LIKE '%$search_clean%'
            ORDER BY i.name ASC";
} else {
    $sql = "SELECT i.name, inv.quantity,
                   CASE WHEN inv.quantity > 0 THEN 'Available' ELSE 'Out of Stock' END AS status
            FROM item i
            LEFT JOIN inventory inv ON i.item_id = inv.item_id
            ORDER BY i.name ASC";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="format.css"> 
    <title>Inventory Page (Admin)</title>
</head>

<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="Hand2Hand logo" width="80">
            <h1>Hand2Hand</h1>
        </div>
        <nav>
            |
            <a href="dashboard.html">Dashboard</a> |
            <a href="beneficiaries.html">Beneficiaries</a> |
            <a href="eventAdmin.html">Events</a> |
            <a href="inventory.php">Inventory</a> |
            <a href="distribution.html">Distribution</a>
        </nav>

        <button class="logout-btn">Logout</button>
    </header>

    <div class="search">
        <h1>Inventory</h1>
    </div>

    <form method="GET" action="inventory.php">
        <input class="search-bar" type="text" name="search"
               placeholder="Search"
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
                    <td>Item Name</td>
                    <td>Quantity Available</td>
                    <td>Status</td>
                </tr>
            </thead>

            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <button onclick="window.location.href='update_inventory.html'">Update Stock</button>
        <button onclick="window.location.href='add_inventory.html'">Add New Item</button>
    </section>
</body>
</html>
<?php $conn->close(); ?>