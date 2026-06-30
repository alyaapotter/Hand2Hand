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

$stmt = $conn->prepare("
    SELECT
        user_id,
        username,
        contact_number,
        address,
        family_size,
        priority_level
    FROM user
    WHERE user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
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
  <link rel="stylesheet" href="../css/formatBulan.css">
  <style>
    /* ===== Page wrapper, matches aid_status.php theme ===== */
    .content-container {
        max-width: 760px;
        margin: 30px auto 60px;
        padding: 0 20px;
    }

    .profile-heading {
        text-align: center;
        color: #443025;
        margin: 25px 0 10px;
        font-size: 26px;
        font-weight: 700;
    }

    /* ===== Alerts ===== */
    .alert {
        max-width: 760px;
        margin: 15px auto 0;
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
    }
    .alert-success { background-color: #d7f7df; color: #1f7a3c; border: 1px solid #22c55e; }
    .alert-error   { background-color: #fde0e0; color: #9c1c1c; border: 1px solid #ef4444; }

    /* ===== Card / Form ===== */
    .profile-form {
        background-color: #FFE4EF;
        border: 2px solid #A86B6C;
        border-radius: 12px;
        padding: 30px 35px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .form-section {
        border: none;
        margin: 0 0 10px;
        padding: 0;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #FFE4EF;
        background-color: #443025;
        padding: 8px 16px;
        border-radius: 8px;
        display: inline-block;
        margin-bottom: 18px;
    }

    .form-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .form-group label {
        flex: 0 0 160px;
        font-weight: 600;
        color: #443025;
        font-size: 14px;
    }

    .input-field {
        padding: 9px 12px;
        border: 1px solid #A86B6C;
        border-radius: 6px;
        background-color: #ffffff;
        color: #443025;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .input-field:focus {
        border-color: #443025;
        box-shadow: 0 0 0 2px rgba(68,48,37,0.15);
    }

    .dynamic-width { width: 100%; max-width: 320px; }
    .short-width   { width: 100%; max-width: 220px; }
    .long-width    { width: 100%; max-width: 420px; }
    .tiny-width    { width: 90px; }
    .dropdown-field { cursor: pointer; }

    .readonly-value {
        font-size: 14px;
        font-weight: 600;
        color: #443025;
        background-color: #f7d6e4;
        padding: 8px 14px;
        border-radius: 6px;
        border: 1px dashed #A86B6C;
    }

    .section-divider {
        border: none;
        border-top: 1px solid #A86B6C;
        margin: 22px 0;
        opacity: 0.6;
    }

    .save-button-container {
        text-align: center;
        margin-top: 10px;
    }

    .btn-save {
        background-color: #443025;
        color: #FFE4EF;
        border: none;
        padding: 11px 38px;
        border-radius: 25px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
    }
    .btn-save:hover {
        background-color: #5c4434;
        transform: translateY(-1px);
    }

    /* Dark footer styling, consistent with aid_status.php */
    footer.dark-footer {
        background-color: #443025 !important;
        color: #FFE4EF !important;
        padding: 30px !important;
        margin-top: 0 !important;
    }
    footer.dark-footer h4 { color: #FFE4EF !important; margin-bottom: 15px !important; }
    footer.dark-footer p  { color: #FFE4EF !important; margin-bottom: 2px !important; font-size: 14px !important; }

    @media (max-width: 600px) {
        .form-group label { flex: 0 0 100%; margin-bottom: 6px; }
        .profile-form { padding: 22px 18px; }
    }
  </style>
</head>
<body>

  <?php include '../includes/navbar.php'; ?>

  <div class="content-container">
    <h1 class="profile-heading">My Profile</h1>

    <?php if ($success_msg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form action="../process_profile.php" method="POST" class="profile-form">
      <input type="hidden" name="user_id" value="<?= (int)$user_id ?>">

      <fieldset class="form-section">
        <legend class="section-title">Personal Information</legend>
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" class="input-field dynamic-width" value="<?= htmlspecialchars($profile['username'] ?? '') ?>" required />
        </div>
        <div class="form-group">
          <label>Beneficiary ID:</label>
          <span class="readonly-value">
            <?= $profile ? 'USR-' . str_pad($profile['user_id'], 5, '0', STR_PAD_LEFT) : 'N/A' ?>
          </span>
        </div>
        <div class="form-group">
          <label for="contact">Contact Number:</label>
          <input type="text" id="contact" name="contact" class="input-field short-width" value="<?= htmlspecialchars($profile['contact_number'] ?? '') ?>" />
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
              <option value="<?= $lvl ?>" <?= (($profile['priority_level'] ?? '') === $lvl) ? 'selected' : '' ?>><?= $lvl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </fieldset>

      <hr class="section-divider">
      <div class="save-button-container">
        <button type="submit" class="btn-save">Save</button>
      </div>
    </form>
  </div>

  <footer class="dark-footer">
      <h4>Hand2Hand</h4>
      <p>Contact Us:</p>
      <p>Email: hand2hand@support.com</p>
  </footer>

</body>
</html>