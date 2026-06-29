<?php
// beneficiary/profile_page_bene.php
session_start();
// Sambung dengan fail connect.php kau yang dah berpassword "root123"
require_once '../includes/connect.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Requester') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$profile = null;

// Gunakan variable $conn yang sedia ada dari connect.php
$stmt = $conn->prepare("
    SELECT b.beneficiary_id, b.name, b.contact, b.address, b.family_size, b.priority
    FROM   beneficiaries b
    WHERE  b.user_id = ?
    LIMIT  1
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
}
$stmt->close();

$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg   = $_SESSION['error_msg']   ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Profile (Beneficiary)</title>
  <link rel="stylesheet" href="profile_page_bene.css" />
</head>
<body>

  <nav>
    <div class="nav-left">
      <img src="images/logo.png" alt="Hand2Hand Logo" class="logo-circle">
      <div class="nav-text">
        <h1>Hand2Hand</h1>
        <div class="nav-links">
          <a href="home_beneficiary.php">Home</a> |
          <a href="aid_status.php">My Aid</a> |
          <a href="profile_page_bene.php" class="active">Profile</a> |
        </div>
      </div>
    </div>
    <a href="../logout.php" class="btn-logout">Logout</a>
  </nav>

  <?php if ($success_msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
  <?php endif; ?>
  <?php if ($error_msg): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
  <?php endif; ?>

  <main class="content-container">
    <form action="process_profile.php" method="POST" class="profile-form">
      <input type="hidden" name="user_id" value="<?= (int)$user_id ?>">

      <fieldset class="form-section">
        <legend class="section-title">Personal Information</legend>
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" class="input-field dynamic-width" value="<?= htmlspecialchars($profile['name'] ?? '') ?>" required />
        </div>
        <div class="form-group">
          <label>Beneficiary ID:</label>
          <span class="readonly-value">
            <?= $profile ? 'BNF-' . str_pad($profile['beneficiary_id'], 5, '0', STR_PAD_LEFT) : 'Belum ditetapkan' ?>
          </span>
        </div>
        <div class="form-group">
          <label for="contact">Contact Number:</label>
          <input type="text" id="contact" name="contact" class="input-field short-width" value="<?= htmlspecialchars($profile['contact'] ?? '') ?>" />
        </div>
        <div class="form-group">
          <label for="address">Address:</label>
          <input type="text" id="address" name="address" class="input-field long-width" value="<?= htmlspecialchars($profile['address'] ?? '') ?>" />
        </div>
      </fieldset>

      <hr class="section-divider">

      <fieldset class="form-section">
        <legend class="section-title">Aid Target Information</legend>
        <div class="form-group">
          <label for="family_size">Family Size:</label>
          <input type="number" id="family_size" name="family_size" class="input-field tiny-width" min="1" max="99" value="<?= (int)($profile['family_size'] ?? 1) ?>" />
        </div>
        <div class="form-group">
          <label for="priority">Priority Level:</label>
          <select id="priority" name="priority" class="input-field short-width dropdown-field">
            <option value="">-- Pilih --</option>
            <?php foreach (['Low', 'Medium', 'High'] as $lvl): ?>
              <option value="<?= $lvl ?>" <?= (($profile['priority'] ?? '') === $lvl) ? 'selected' : '' ?>><?= $lvl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </fieldset>

      <hr class="section-divider">
      <div class="save-button-container">
        <button type="submit" class="btn-save">Save</button>
      </div>
    </form>
  </main>
</body>
</html>