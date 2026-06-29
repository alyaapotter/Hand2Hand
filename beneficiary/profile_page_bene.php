<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hand2Hand - Profile (Beneficiary)</title>
  <link rel="stylesheet" href="../css/profile_page_bene.css" />
  <link rel="stylesheet" href="../css/navbar_footer.css" />
</head>
<body>

  <?php include '../includes/navbar.php'; ?>

  <div style="padding: 20px 40px 0 40px;">
    <h2 style="color: #443025; font-size: 32px;">Profile</h2>
  </div>

  <main class="content-container">
    <form action="process_profile.php" method="POST" class="profile-form">
      
      <fieldset class="form-section">
        <legend class="section-title">Personal Information</legend>
        
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" class="input-field dynamic-width" />
        </div>

        <div class="form-group">
          <label>Beneficiary ID</label>
          </div>

        <div class="form-group">
          <label for="contact">Contact Number:</label>
          <input type="text" id="contact" name="contact" class="input-field short-width" />
        </div>

        <div class="form-group">
          <label for="address">Address:</label>
          <input type="text" id="address" name="address" class="input-field long-width" />
        </div>
      </fieldset>

      <hr class="section-divider">

      <fieldset class="form-section">
        <legend class="section-title">Add Target Item</legend>
        
        <div class="form-group">
          <label for="family_size">Family Size:</label>
          <input type="number" id="family_size" name="family_size" class="input-field tiny-width" min="1" />
        </div>

        <div class="form-group">
          <label for="priority">Priority Level:</label>
          <select id="priority" name="priority" class="input-field short-width dropdown-field">
            <option value=""></option>
            <option value="Low">Low</option>
            <option value="Medium">Medium</option>
            <option value="High">High</option>
          </select>
        </div>
      </fieldset>

      <hr class="section-divider">

      <div class="save-button-container">
        <button type="submit" class="btn-save">Save</button>
      </div>

    </form>
  </main>

  <footer class="main-footer">
    <hr class="footer-divider">
    <div class="footer-content">
        <h3>Hand2Hand</h3>
        <p>Contact Us:</p>
        <p>Email: <a href="mailto:hand2hand@support.com">hand2hand@support.com</a></p>
    </div>
  </footer>

</body>
</html>