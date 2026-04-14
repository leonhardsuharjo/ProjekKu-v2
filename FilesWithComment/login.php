<?php
// ============================================================
// login.php
// Purpose : Displays the login form and processes login submissions.
// On success, creates a session and redirects to the dashboard.
// Uses prepared statements to prevent SQL injection.
// ============================================================

// Start or resume a PHP session so we can store user data after login.
session_start();

// Include the database connection; $conn becomes available after this line.
require_once 'db.php';

// If the user is already logged in, redirect them straight to the dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to the home/dashboard page
    exit();                        // Stop further script execution
}

// Initialise $message to empty; it will hold success or error text for the view.
$message = '';

// Check if the form was submitted via POST (the login button was clicked).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve and trim whitespace from the submitted username field.
    $username = trim($_POST['username']);

    // Retrieve the submitted password (not trimmed — passwords may contain spaces).
    $password = $_POST['password'];

    // Only proceed if both username and password were provided.
    if ($username !== '' && $password !== '') {

        // Prepare a SELECT statement to look up the user by username.
        // Using ? placeholder prevents SQL injection.
        $stmt = $conn->prepare('SELECT UserID, Name, Username, Password FROM user WHERE Username = ?');

        // Bind the username value to the ? placeholder; 's' means string type.
        $stmt->bind_param('s', $username);

        // Execute the prepared statement against the database.
        $stmt->execute();

        // Retrieve the full result set from the executed statement.
        $result = $stmt->get_result();

        // Check if exactly one row was returned (username exists and is unique).
        if ($result->num_rows === 1) {

            // Fetch the single matching row as an associative array.
            $row = $result->fetch_assoc();

            // Verify the submitted password against the stored bcrypt hash.
            // password_verify() returns true only if they match.
            if (password_verify($password, $row['Password'])) {

                // Correct password — store the user's data in the session.
                $_SESSION['user_id'] = $row['UserID'];   // Numeric user ID
                $_SESSION['username'] = $row['Username']; // Login username
                $_SESSION['name']     = $row['Name'];     // Display name for the nav bar

                // Redirect to the dashboard after successful login.
                header('Location: index.php');
                exit(); // Halt execution so no further output is sent
            } else {
                // Password did not match — set a generic error message.
                $message = 'Invalid username or password.';
            }
        } else {
            // No user found with that username — same generic message to avoid user enumeration.
            $message = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- UTF-8 encoding for full character support -->
    <meta charset="UTF-8">
    <!-- Responsive viewport meta tag for mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Page title shown in the browser tab -->
    <title>Login – Enterprise Manager</title>
    <!-- Link to the global stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Centred login card wrapper; styled by .login-wrap in style.css -->
<div class="login-wrap">

    <!-- Page heading inside the login card -->
    <h1>Enterprise Manager</h1>

    <!-- Show the error/info message if one was set during form processing -->
    <?php if ($message): ?>
        <!-- .msg class gives the green success style; displayed for errors too -->
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Login form — sends data back to this same page (login.php) via POST -->
    <form method="POST" action="login.php">

        <!-- Username field group -->
        <div class="form-group">
            <label for="username">Username</label> <!-- Label linked to the input below -->
            <!-- Text input for the username; autofocus places cursor here on page load -->
            <input type="text" id="username" name="username" required autofocus>
        </div>

        <!-- Password field group -->
        <div class="form-group">
            <label for="password">Password</label>
            <!-- Password input hides characters as the user types -->
            <input type="password" id="password" name="password" required>
        </div>

        <!-- Submit button; .btn .btn-primary applies the dark teal button style -->
        <button type="submit" class="btn btn-primary">Login</button>

    </form>

    <!-- Link to the registration page for new users -->
    <p style="margin-top:14px;">No account? <a href="register.php">Register</a></p>

</div><!-- /.login-wrap -->
</body>
</html>
