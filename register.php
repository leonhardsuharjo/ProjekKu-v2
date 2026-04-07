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