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
        $name = trim($_POST['CustomerName']);
        $address = trim($_POST['Address']);
        $contact = trim($_POST['ContactNumber']);
        if ($name !== "") {
            $stmt = $conn->prepare("INSERT INTO customer (CustomerName, Address, ContactNumber) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $address, $contact);
            $stmt->execute();
            $message = "Customer added successfully.";
        } else {
            $message = "Customer name is required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['CustomerID'];
        $name = trim($_POST['CustomerName']);
        $address = trim($_POST['Address']);
        $contact = trim($_POST['ContactNumber']);
        if ($name !== "") {
            $stmt = $conn->prepare("UPDATE customer SET CustomerName=?, Address=?, ContactNumber=? WHERE CustomerID=?");
            $stmt->bind_param("sssi", $name, $address, $contact, $id);
            $stmt->execute();
            $message = "Customer updated successfully.";
        } else {
            $message = "Customer name is required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['CustomerID'];
        $stmt = $conn->prepare("DELETE FROM customer WHERE CustomerID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Customer deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM customer WHERE CustomerID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT * FROM customer ORDER BY CustomerID ASC");
?>