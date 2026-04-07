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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Products</h1>
    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <?php if ($edit_data): ?>
        <h2>Edit Product</h2>
        <form method="post" action="products.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="ProductID" value="<?php echo $edit_data['ProductID']; ?>">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="ProductName" value="<?php echo htmlspecialchars($edit_data['ProductName']); ?>" required>
            </div>
            <div class="form-group">
                <label>Selling Price</label>
                <input type="number" name="SellingPrice" step="0.01" value="<?php echo $edit_data['SellingPrice']; ?>" required>
            </div>
            <div class="form-group">
                <label>Supplier</label>
                <select name="SupplierID">
                    <option value="0">-- None --</option>
                    <?php foreach ($suppliers_arr as $s): ?>
                    <option value="<?php echo $s['SupplierID']; ?>" <?php if ($edit_data['SupplierID'] == $s['SupplierID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($s['SupplierName']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="products.php" class="btn btn-edit" style="margin-left:8px;">Cancel</a>
        </form>
        <?php else: ?>
        <h2>Add New Product</h2>
        <form method="post" action="products.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="ProductName" required>
            </div>
            <div class="form-group">
                <label>Selling Price</label>
                <input type="number" name="SellingPrice" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Supplier</label>
                <select name="SupplierID">
                    <option value="0">-- None --</option>
                    <?php foreach ($suppliers_arr as $s): ?>
                    <option value="<?php echo $s['SupplierID']; ?>"><?php echo htmlspecialchars($s['SupplierName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Selling Price</th>
                <th>Supplier</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['ProductID']; ?></td>
                <td><?php echo htmlspecialchars($row['ProductName']); ?></td>
                <td><?php echo number_format($row['SellingPrice'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['SupplierName'] ?? '-'); ?></td>
                <td class="action-btns">
                    <a href="products.php?edit=<?php echo $row['ProductID']; ?>" class="btn btn-edit">Edit</a>
                    <a href="productmaterials.php?product_id=<?php echo $row['ProductID']; ?>" class="btn btn-link">Materials</a>
                    <form method="post" action="products.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ProductID" value="<?php echo $row['ProductID']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
