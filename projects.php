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
        $name = trim($_POST['ProjectName']);
        $date = $_POST['ProjectDate'];
        $transport = (float)$_POST['TransportCost'];
        $value = (float)$_POST['ProjectValue'];
        $cid = (int)$_POST['CustomerID'];
        if ($name !== "" && $date !== "") {
            $stmt = $conn->prepare("INSERT INTO project (ProjectName, ProjectDate, TransportCost, ProjectValue, CustomerID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddi", $name, $date, $transport, $value, $cid);
            $stmt->execute();
            $message = "Project added successfully.";
        } else {
            $message = "Project name and date are required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['ProjectID'];
        $name = trim($_POST['ProjectName']);
        $date = $_POST['ProjectDate'];
        $transport = (float)$_POST['TransportCost'];
        $value = (float)$_POST['ProjectValue'];
        $cid = (int)$_POST['CustomerID'];
        if ($name !== "" && $date !== "") {
            $stmt = $conn->prepare("UPDATE project SET ProjectName=?, ProjectDate=?, TransportCost=?, ProjectValue=?, CustomerID=? WHERE ProjectID=?");
            $stmt->bind_param("ssddii", $name, $date, $transport, $value, $cid, $id);
            $stmt->execute();
            $message = "Project updated.";
        } else {
            $message = "Project name and date are required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['ProjectID'];
        $stmt = $conn->prepare("DELETE FROM project WHERE ProjectID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Project deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM project WHERE ProjectID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT p.*, c.CustomerName FROM project p LEFT JOIN customer c ON p.CustomerID = c.CustomerID ORDER BY p.ProjectDate DESC");
$customers = $conn->query("SELECT CustomerID, CustomerName FROM customer ORDER BY CustomerName ASC");
$customers_arr = [];
while ($c = $customers->fetch_assoc()) {
    $customers_arr[] = $c;
}
?>