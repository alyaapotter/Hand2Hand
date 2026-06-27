<?php
// includes/navbar.php
$role = $_SESSION['role'];

if ($role == 'Admin') {
    $nav_links = "<a href='../admin/dashboard.php'>Dashboard</a> | <a href='../admin/beneficiary.php'>Beneficiaries</a> | <a href='../admin/event_management.php'>Events</a> | <a href='../admin/inventory.php'>Inventory</a> | <a href='../admin/distribution.php'>Distribution</a>";
} elseif ($role == 'Donor') {
    $nav_links = "<a href='../donor/home_page_donor.php'>Home</a> | <a href='../donor/donation_event_donor.php'>Events</a> | <a href='../donor/donation_history.php'>My Donations</a>";
} else {
    $nav_links = "<a href='../beneficiary/home_beneficiary.php'>Home</a> | <a href='../beneficiary/aid_status.php'>My Aid</a> | <a href='../beneficiary/profile_page_bene.php'>Profile</a>";
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
