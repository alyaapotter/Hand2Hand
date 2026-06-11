<?php
// includes/navbar.php
$role = $_SESSION['role'];

if ($role == 'Admin') {
    $nav_links = "Dashboard | Beneficiaries | Events | Inventory | Distribution";
} elseif ($role == 'Donor') {
    $nav_links = "Home | Events | My Donations";
} else {
    $nav_links = "Home | My Aid | Profile";
}
?>
<nav class="navbar">
    <div class="nav-left">
        <div class="nav-logo">
            <img src="/Hand2Hand/image/logo.png" alt="logo" style="width:45px;height:45px;border-radius:50%;object-fit:cover;">
        </div>
        <div class="nav-brand-text">
            <span class="nav-brand-name">Hand2Hand</span>
            <span class="nav-links-sub"><?= $nav_links ?></span>
        </div>
    </div>
    <a href="/hand2hand/logout.php" class="btn-logout">Logout</a>
</nav>
