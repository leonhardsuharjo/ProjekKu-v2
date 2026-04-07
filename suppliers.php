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
        $name = trim($_POST['SupplierName']);
        $address = trim($_POST['Address']);
        $contact = trim($_POST['ContactNo']);
        $status = $_POST['Status'];
        if ($name !== "") {
            $stmt = $conn->prepare("INSERT INTO supplier (SupplierName, Address, ContactNo, Status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $address, $contact, $status);
            $stmt->execute();
            $message = "Supplier added successfully.";
        } else {
            $message = "Supplier name is required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['SupplierID'];
        $name = trim($_POST['SupplierName']);
        $address = trim($_POST['Address']);
        $contact = trim($_POST['ContactNo']);
        $status = $_POST['Status'];
        if ($name !== "") {
            $stmt = $conn->prepare("UPDATE supplier SET SupplierName=?, Address=?, ContactNo=?, Status=? WHERE SupplierID=?");
            $stmt->bind_param("ssssi", $name, $address, $contact, $status, $id);
            $stmt->execute();
            $message = "Supplier updated.";
        } else {
            $message = "Supplier name is required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['SupplierID'];
        $stmt = $conn->prepare("DELETE FROM supplier WHERE SupplierID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Supplier deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM supplier WHERE SupplierID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT * FROM supplier ORDER BY SupplierID ASC");
?>