<?php
// login.php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Admin') header("Location: admin/dashboard.php");
    elseif ($_SESSION['role'] == 'Requester') header("Location: beneficiary/home_beneficiary.php");
    elseif ($_SESSION['role'] == 'Donor') header("Location: donor/home_page_donor.php");
    exit();
}

require_once 'includes/connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM USER WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'Admin') header("Location: admin/dashboard.php");
            elseif ($user['role'] == 'Requester') header("Location: beneficiary/home_beneficiary.php");
            elseif ($user['role'] == 'Donor') header("Location: donor/home_page_donor.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hand2Hand - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><img src="image/logo.png" style="width:50px;height:50px;object-fit:cover;border-radius:50%;vertical-align:middle;"> Hand2Hand</h1>
                <p>Community Aid Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateLogin()">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Login</button>
            </form>

            <p class="auth-footer">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <script>
        function validateLogin() {
            const email = document.querySelector('input[name="email"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === '') {
                alert('Please enter your email!');
                return false;
            }
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address!');
                return false;
            }
            if (password === '') {
                alert('Please enter your password!');
                return false;
            }
            if (password.length < 6) {
                alert('Password must be at least 6 characters!');
                return false;
            }
            return true;
        }
    </script>
</body>

</html>