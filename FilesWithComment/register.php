<?php
// ============================================================
// register.php
// Purpose : Displays a registration form and creates a new
//           user account in the 'user' table.
// Passwords are hashed with bcrypt before being stored.
// Usernames must be unique (enforced by both DB UNIQUE and PHP check).
// ============================================================

// Start or resume the PHP session.
session_start();

// Include the database connection file; $conn is available after this.
require_once 'db.php';

// Initialise $message to empty string; populated on success or validation error.
$message = '';

// Process the form only when submitted via POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and trim the full name submitted from the form.
    $name     = trim($_POST['name']);

    // Retrieve and trim the desired username.
    $username = trim($_POST['username']);

    // Retrieve the password (not trimmed to preserve intentional spaces).
    $password = $_POST['password'];

    // Validate that all three required fields have been filled in.
    if ($name !== '' && $username !== '' && $password !== '') {

        // Prepare a SELECT to check whether the username is already taken.
        // Using a prepared statement prevents SQL injection.
        $check = $conn->prepare('SELECT UserID FROM user WHERE Username = ?');

        // Bind the username string to the placeholder.
        $check->bind_param('s', $username);

        // Execute the duplicate-check query.
        $check->execute();

        // Store the result set in memory so num_rows becomes accessible.
        $check->store_result();

        // If at least one row is returned, the username is already in use.
        if ($check->num_rows > 0) {
            $message = 'Username already taken.'; // Inform the user to choose a different name
        } else {
            // Hash the plain-text password using PHP's bcrypt algorithm.
            // PASSWORD_DEFAULT automatically selects the strongest available algorithm.
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Prepare an INSERT statement to create the new user record.
            $stmt = $conn->prepare('INSERT INTO user (Name, Username, Password) VALUES (?, ?, ?)');

            // Bind all three string values to the placeholders.
            $stmt->bind_param('sss', $name, $username, $hashed);

            // Execute the INSERT to save the new user to the database.
            $stmt->execute();

            // Inform the user that registration was successful.
            $message = 'Registration successful. <a href="login.php">Login here</a>.';
        }

        // Free the resources used by the duplicate-check statement.
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- UTF-8 encoding -->
    <meta charset="UTF-8">
    <!-- Responsive viewport for mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Browser tab title -->
    <title>Register – Enterprise Manager</title>
    <!-- Global stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Centred login/register card; reuses the .login-wrap style from style.css -->
<div class="login-wrap">

    <!-- Page heading -->
    <h1>Register</h1>

    <!-- Display any success or error message; allow HTML so the login link renders -->
    <?php if ($message): ?>
        <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Registration form — POSTs data back to register.php -->
    <form method="POST" action="register.php">

        <!-- Full name field -->
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>

        <!-- Username field -->
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>

        <!-- Password field -->
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <!-- Submit button -->
        <button type="submit" class="btn btn-primary">Register</button>

    </form>

    <!-- Link back to the login page for users who already have an account -->
    <p style="margin-top:14px;">Already have an account? <a href="login.php">Login</a></p>

</div><!-- /.login-wrap -->
</body>
</html>
