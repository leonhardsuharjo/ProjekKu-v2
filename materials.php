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
        $name = trim($_POST['MaterialName']);
        $price = (float)$_POST['PricePerUnit'];
        $sid = (int)$_POST['SupplierID'];
        if ($name !== "") {
            $stmt = $conn->prepare("INSERT INTO material (MaterialName, PricePerUnit, SupplierID) VALUES (?, ?, ?)");
            $stmt->bind_param("sdi", $name, $price, $sid);
            $stmt->execute();
            $message = "Material added successfully.";
        } else {
            $message = "Material name is required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['MaterialID'];
        $name = trim($_POST['MaterialName']);
        $price = (float)$_POST['PricePerUnit'];
        $sid = (int)$_POST['SupplierID'];
        if ($name !== "") {
            $stmt = $conn->prepare("UPDATE material SET MaterialName=?, PricePerUnit=?, SupplierID=? WHERE MaterialID=?");
            $stmt->bind_param("sdii", $name, $price, $sid, $id);
            $stmt->execute();
            $message = "Material updated.";
        } else {
            $message = "Material name is required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['MaterialID'];
        $stmt = $conn->prepare("DELETE FROM material WHERE MaterialID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Material deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM material WHERE MaterialID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT m.*, s.SupplierName FROM material m LEFT JOIN supplier s ON m.SupplierID = s.SupplierID ORDER BY m.MaterialID ASC");
$suppliers = $conn->query("SELECT SupplierID, SupplierName FROM supplier ORDER BY SupplierName ASC");
$suppliers_arr = [];
while ($s = $suppliers->fetch_assoc()) {
    $suppliers_arr[] = $s;
}
?>
