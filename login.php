<?php
session_start();
require_once "db.php";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === "" || $password === "") {
        $message = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT UserID, Name, Username, Password FROM user WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                $_SESSION['user_id'] = $row['UserID'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['name'] = $row['Name'];
                header("Location: index.php");
                exit();
            } else {
                $message = "Invalid username or password.";
            }
        } else {
            $message = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-wrap">
    <h1>Enterprise Manager</h1>
    <?php if ($message !== ""): ?>
    <div class="msg error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
        &nbsp;<a href="register.php">Create an account</a>
    </form>
</div>
</body>
</html>
