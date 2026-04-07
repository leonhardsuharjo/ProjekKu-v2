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
        $name = trim($_POST['ProductName']);
        $price = (float)$_POST['SellingPrice'];
        $sid = (int)$_POST['SupplierID'];
        if ($name !== "") {
            $stmt = $conn->prepare("INSERT INTO product (ProductName, SellingPrice, SupplierID) VALUES (?, ?, ?)");
            $stmt->bind_param("sdi", $name, $price, $sid);
            $stmt->execute();
            $message = "Product added successfully.";
        } else {
            $message = "Product name is required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['ProductID'];
        $name = trim($_POST['ProductName']);
        $price = (float)$_POST['SellingPrice'];
        $sid = (int)$_POST['SupplierID'];
        if ($name !== "") {
            $stmt = $conn->prepare("UPDATE product SET ProductName=?, SellingPrice=?, SupplierID=? WHERE ProductID=?");
            $stmt->bind_param("sdii", $name, $price, $sid, $id);
            $stmt->execute();
            $message = "Product updated.";
        } else {
            $message = "Product name is required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['ProductID'];
        $stmt = $conn->prepare("DELETE FROM product WHERE ProductID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Product deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM product WHERE ProductID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT p.*, s.SupplierName FROM product p LEFT JOIN supplier s ON p.SupplierID = s.SupplierID ORDER BY p.ProductID ASC");
$suppliers = $conn->query("SELECT SupplierID, SupplierName FROM supplier ORDER BY SupplierName ASC");
$suppliers_arr = [];
while ($s = $suppliers->fetch_assoc()) {
    $suppliers_arr[] = $s;
}
?>