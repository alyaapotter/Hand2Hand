<?php

session_start();

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role'];

    if ($password != $confirmPassword) {
        echo "Password and Confirm Password do not match.";
    } else {
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        $_SESSION['role'] = $role;

        echo "Registration successful!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="css/format.css">
</head>

<body>

    <?php include('header.php'); ?>

    <section class="logbackground">
        <div class="form-container reg">

            <h2>Register</h2>

            <form action="" method="post">

                <label>Email:</label><br>
                <input type="text" name="email"><br><br>

                <label>Username:</label><br>
                <input type="text" name="username"><br><br>

                <label>Password:</label><br>
                <input type="password" name="password"><br><br>

                <label>Confirm Password:</label><br>
                <input type="password" name="confirmPassword"><br><br>

                <label>Role:</label><br>

                <input type="radio" name="role" value="admin">
                Admin<br>

                <input type="radio" name="role" value="donor">
                Donor<br>

                <input type="radio" name="role" value="beneficiary">
                Beneficiary<br><br>

                <input type="submit" value="Register">

            </form>

            <p>
                Already have an account?
                <a href="login.php">Login here</a>
            </p>

        </div>
    </section>
</body>

</html>