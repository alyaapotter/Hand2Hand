<?php
session_start();
require_once 'includes/connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email           = trim($_POST['email']);
    $username        = trim($_POST['username']);
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role            = $_POST['role'];

    if (empty($email) || empty($username) || empty($password) || empty($role)) {
        $error = "Please fill in all fields.";
    } else if ($password != $confirmPassword) {
        $error = "Password and Confirm Password do not match.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM USER WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO USER (email, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $email, $username, $hashedPassword, $role);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
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
                <p>Community Aid Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateRegister()">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirmPassword" placeholder="Confirm your password" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="">-- Select Role --</option>
                        <option value="Donor">Donor</option>
                        <option value="Requester">Requester</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Register</button>
            </form>

            <p class="auth-footer">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        function validateRegister() {
            const email = document.querySelector('input[name="email"]').value.trim();
            const username = document.querySelector('input[name="username"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirmPassword"]').value;
            const role = document.querySelector('select[name="role"]').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email === '') {
                alert('Please enter your email!');
                return false;
            }
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address!');
                return false;
            }
            if (username === '') {
                alert('Please enter your username!');
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
            if (password !== confirmPassword) {
                alert('Password and Confirm Password do not match!');
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