<?php
// admin/beneficiary_page_admin.php
session_start();
require_once '../includes/connect.php'; 

// Auth check untuk Admin sahaja
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// 1. Ambil nilai carian jika ada
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// 2. Bina SQL Query asas
$query = "
SELECT
    r.request_id,                
    u.username AS name,   
    u.email,
    r.description,
    r.status
FROM request r                   
JOIN user u ON r.user_id = u.user_id
";

// 3. Tambah klausa WHERE jika pengguna membuat carian
if ($search !== '') {
    // Menapis mengikut request_id ATAU nama pengguna
    $query .= " WHERE r.request_id = '$search' OR u.username LIKE '%$search%'";
}

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hand2Hand - Admin Beneficiary Management</title>
    <link rel="stylesheet" href="../css/beneficiary_page_admin.css"> 
</head>
<body>

    <header class="admin-header">
        <div class="header-top">
            <div class="logo-brand-container">
                <img src="../image/logo.png" alt="Logo" class="logo-circle">
                <div class="brand-nav-box">
                    <span class="brand-title">Hand2Hand</span>
                    <nav class="admin-nav">
                        <a href="dashboard.php">Dashboard</a> | 
                        <a href="beneficiary_page_admin.php" class="active">Beneficiaries</a> | 
                        <a href="event_management.php">Events</a> | 
                        <a href="inventory.php">Inventory</a> | 
                        <a href="distribution.php">Distribution</a>
                    </nav>
                </div>
            </div>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
        
        <div class="header-title-section">
            <h2>Beneficiaries</h2>
            
            <form action="" method="GET" class="search-container">
                <span class="search-icon">🔍</span>
                <input type="text" name="search" class="search-input" placeholder="Fill with id number or name.." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" style="display: none;"></button>
            </form>
            
        </div>
    </header>

    <main class="content-container">
        <h3 class="section-title">
            <?= $search !== '' ? "Search.. \"" . htmlspecialchars($search) . "\"" : "Beneficiaries List" ?>
            <?php if ($search !== ''): ?>
                <a href="beneficiary_page_admin.php" style="font-size: 12px; margin-left: 10px; color: black;">Reset</a>
            <?php endif; ?>
        </h3>
        
        <div class="table-action-wrapper">
            <table class="beneficiaries-table">
                <thead>
                    <tr>
                      <th>Request ID</th>
                      <th>Name</th>
                      <th>Needs</th>
                      <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>#<?= str_pad($row['request_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['description'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="add-button-container">
            <button class="btn-add" onclick="location.href='beneficiary_needs.php'">Add Beneficiary</button>
        </div>
    </main>

</body>
</html>