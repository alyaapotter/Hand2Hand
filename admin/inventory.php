<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/inventory_admin.css"> 
    <title>Inventory Page (Admin)</title>
</head>

<body>
    <header>
        <div class="logo">
        <img src="../image/logo.png" alt="Hand2Hand logo" width="80">
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

        <button class="logout-btn">Logout</button>
    </header>

    <div class="search">
        <h1>Inventory</h1>
    </div>
    
    <input class="search-bar" type="text" placeholder="Search">
    
    <section class="inventory-list">
        <div>
            <p class="text">Inventory List</p>
        </div>

            <table class="table-container">
            <tr>	
                <td>Item Name</td>
                <td></td>	
            </tr>

            <tr>	
                <td>Quantity Available</td> 	
                <td></td>
            </tr> 
        
            <tr>
                <td>Status</td>
                <td></td>
            </tr>
            <br />
            </table>

            <!-- button that links to add inventory and update inventory -->
            <button onclick="window.location.href='update_inventory.php'">Update Stock</button>
            <button onclick="window.location.href='add_inventory.php'">Add New Item</button>
    </section>
</body>
</html>