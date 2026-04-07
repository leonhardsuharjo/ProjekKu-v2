<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($name === "" || $username === "" || $password === "" || $confirm === "") {
        $message = "All fields are required.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT UserID FROM user WHERE Username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $message = "Username already taken.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (Name, Username, Password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $username, $hashed);
            $stmt->execute();
            $message = "Registration successful. <a href='login.php'>Login here</a>.";
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-wrap">
    <h1>Register</h1>
    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
        &nbsp;<a href="login.php">Already have an account?</a>
    </form>
</div>
</body>
</html>
