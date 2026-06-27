<?php
// includes/navbar.php
$role = $_SESSION['role'];

if ($role == 'Admin') {
    $nav_links = "<a href='../admin/dashboard.php'>Dashboard</a> | <a href='../admin/beneficiaries.php'>Beneficiaries</a> | <a href='../admin/donation_event.php'>Events</a> | <a href='../admin/inventory.php'>Inventory</a> | <a href='../admin/distribution.php'>Distribution</a>";
} else if ($role == 'Donor') {
    $nav_links = "<a href='../donor/home_page_donor.php'>Home</a> | <a href='../donor/donation_event_donor.php'>Events</a> | <a href='../donor/donation_history.php'>My Donations</a>";
} else if ($role == 'Requester'){
    $nav_links = "<a href='../beneficiary/home_beneficiary.php'>Home</a> | <a href='../beneficiary/aid_status.php'>My Aid</a> | <a href='../beneficiary/profile_page_bene.php'>Profile</a>";
} else {
    $nav_links = "<a href='../home.php'>Home</a> | <a href='../about.php'>About Us</a> | <a href='../event.php'>Events</a> | <a href='../login.php'>Login</a>";
}
?>
<link rel="stylesheet" href="../css/navbar_footer.css">
<nav class="navbar">
    <div class="nav-left">
        <div class="nav-logo">
            <img src="../image/logo.png" alt="logo" style="width:70px;height:70px;border-radius:50%;object-fit:cover;">
        </div>
        <div class="nav-brand-text">
            <span class="nav-brand-name">Hand2Hand</span>
            <span class="nav-links-sub"><?= $nav_links ?></span>
        </div>
    </div>
    <a href="../logout.php" class="btn-logout">Logout</a>
</nav>