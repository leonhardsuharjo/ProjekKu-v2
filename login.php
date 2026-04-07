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

