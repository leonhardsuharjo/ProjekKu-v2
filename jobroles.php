<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once "db.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $type = trim($_POST['JobType']);
        $wage = (float)$_POST['WagePerDay'];
        if ($type !== "") {
            $stmt = $conn->prepare("INSERT INTO jobrole (JobType, WagePerDay) VALUES (?, ?)");
            $stmt->bind_param("sd", $type, $wage);
            $stmt->execute();
            $message = "Job role added.";
        } else {
            $message = "Job type is required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['JobRoleID'];
        $type = trim($_POST['JobType']);
        $wage = (float)$_POST['WagePerDay'];
        if ($type !== "") {
            $stmt = $conn->prepare("UPDATE jobrole SET JobType=?, WagePerDay=? WHERE JobRoleID=?");
            $stmt->bind_param("sdi", $type, $wage, $id);
            $stmt->execute();
            $message = "Job role updated.";
        } else {
            $message = "Job type is required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['JobRoleID'];
        $stmt = $conn->prepare("DELETE FROM jobrole WHERE JobRoleID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Job role deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM jobrole WHERE JobRoleID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT * FROM jobrole ORDER BY JobRoleID ASC");
?>