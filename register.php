<?php
// register.php
session_start();
require_once 'includes/db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $role     = $_POST['role'];

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM USER WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO USER (email, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $username, $hashed, $role]);
            $success = "Account created! You can now <a href='login.php'>login here</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hand2Hand - Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><img src="image/logo.png" style="width:50px;height:50px;object-fit:cover;border-radius:50%;vertical-align:middle;"> Hand2Hand</h1>
                <p>Create your account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateRegister()">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="username" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                </div>
                <div class="form-group">
                    <label>Register as</label>
                    <select name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="Requester">Beneficiary (Requester)</option>
                        <option value="Donor">Donor</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Register</button>
            </form>

            <p class="auth-footer">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

<script>
function validateRegister() {
    const username = document.querySelector('input[name="username"]').value.trim();
    const email    = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value;
    const confirm  = document.querySelector('input[name="confirm_password"]').value;
    const role     = document.querySelector('select[name="role"]').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (username === '') {
        alert('Please enter your full name!');
        return false;
    }
    if (email === '') {
        alert('Please enter your email!');
        return false;
    }
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address!');
        return false;
    }
    if (password === '') {
        alert('Please enter a password!');
        return false;
    }
    if (password.length < 6) {
        alert('Password must be at least 6 characters!');
        return false;
    }
    if (password !== confirm) {
        alert('Passwords do not match!');
        return false;
    }
    if (role === '') {
        alert('Please select a role!');
        return false;
    }
    return true;
}
</script>
</body>
</html>
