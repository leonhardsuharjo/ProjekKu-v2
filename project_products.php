<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once "db.php";

if (!isset($_GET['project_id'])) {
    header("Location: projects.php");
    exit();
}

$project_id = (int)$_GET['project_id'];

$pres = $conn->prepare("SELECT p.*, c.CustomerName FROM project p LEFT JOIN customer c ON p.CustomerID = c.CustomerID WHERE p.ProjectID=?");
$pres->bind_param("i", $project_id);
$pres->execute();
$project = $pres->get_result()->fetch_assoc();

if (!$project) {
    header("Location: projects.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $pid = (int)$_POST['ProductID'];
        $qty = (int)$_POST['Quantity'];
        if ($pid > 0 && $qty > 0) {
            $stmt = $conn->prepare("INSERT INTO projectproduct (ProjectID, ProductID, Quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Quantity=?");
            $stmt->bind_param("iiii", $project_id, $pid, $qty, $qty);
            $stmt->execute();
            $message = "Product added to project.";
        } else {
            $message = "Please select a product and enter quantity.";
        }
    } elseif ($action === 'delete') {
        $pid = (int)$_POST['ProductID'];
        $stmt = $conn->prepare("DELETE FROM projectproduct WHERE ProjectID=? AND ProductID=?");
        $stmt->bind_param("ii", $project_id, $pid);
        $stmt->execute();
        $message = "Product removed from project.";
    }
}

$linked = $conn->prepare("SELECT pp.*, pr.ProductName, pr.SellingPrice FROM projectproduct pp JOIN product pr ON pp.ProductID = pr.ProductID WHERE pp.ProjectID=?");
$linked->bind_param("i", $project_id);
$linked->execute();
$linked_result = $linked->get_result();

$all_products = $conn->query("SELECT ProductID, ProductName FROM product ORDER BY ProductName ASC");
$products_arr = [];
while ($p = $all_products->fetch_assoc()) {
    $products_arr[] = $p;
}
?>