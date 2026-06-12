<?php
session_start();

if (isset($_POST['submit'])) {

    $usr = $_POST['username'];
    $pwd = $_POST['password'];
    $role = $_POST['role'];

    if ($usr == "admin" and $pwd == "h2h" and $role == "admin") {
        $_SESSION['username'] = $usr;
        $_SESSION['role'] = "admin";

        header("Location: headeradmin.php");
        exit;
    } else if ($role == "donor") {
        $_SESSION['username'] = $usr;
        $_SESSION['password'] = $pwd;
        $_SESSION['role'] = "donor";

        header("Location: headerdonor.php");
        exit;
    } else if ($role == "beneficiary") {
        $_SESSION['username'] = $usr;
        $_SESSION['password'] = $pwd;
        $_SESSION['role'] = "beneficiary";

        header("Location: header.php");
        exit;
    } else {
        session_destroy();
        echo "Sorry. Your login attempt was not successful.";
    }
} else {
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="format.css">
    </head>

    <body>

        <?php include('header.php'); ?>

        <section class="logbackground">
            <div class="form-container">

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

                    <h2>Login</h2>

                    <label>Username:</label><br>
                    <input type="text" name="username"><br><br>

                    <label>Password:</label><br>
                    <input type="password" name="password"><br><br>

                    <label>Role:</label><br>

                    <input type="radio" name="role" value="admin">
                    Admin<br>

                    <input type="radio" name="role" value="donor">
                    Donor<br>

                    <input type="radio" name="role" value="beneficiary">
                    Beneficiary<br><br>

                    <input type="submit" name="submit" value="Login">

                </form>

                <p>
                    Don't have an account?
                    <a href="register.php">Register here</a>
                </p>

            </div>
        </section>
    <?php } ?>
    </body>

    </html>