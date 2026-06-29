<?php
// admin/beneficiary_page_admin.php
session_start();
require_once '../includes/connect.php'; 

// Auth check untuk Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Tarik data beneficiary menggunakan variable $conn yang sedia ada dalam connect.php
$query = "SELECT b.*, u.email FROM beneficiaries b JOIN USER u ON b.user_id = u.user_id";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hand2Hand - Admin Beneficiary Management</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 0;">
    <nav style="background: #333; color: #fff; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0; font-size: 20px;">Hand2Hand Admin</h1>
        <div>
            <a href="dashboard.php" style="color: #fff; text-decoration: none; margin-right: 15px;">Dashboard</a>
            <a href="../logout.php" style="color: #fff; text-decoration: none;">Logout</a>
        </div>
    </nav>

    <main style="padding: 20px;">
        <h2>Beneficiary List Management</h2>
        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #f2f2f2; text-align: left;">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Family Size</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>BNF-<?= str_pad($row['beneficiary_id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td><?= htmlspecialchars($row['address']) ?></td>
                            <td><?= htmlspecialchars($row['family_size']) ?></td>
                            <td><span style="font-weight: bold;"><?= htmlspecialchars($row['priority']) ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No beneficiaries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>