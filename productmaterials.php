<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once "db.php";

if (!isset($_GET['product_id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['product_id'];

$pres = $conn->prepare("SELECT * FROM product WHERE ProductID=?");
$pres->bind_param("i", $product_id);
$pres->execute();
$product = $pres->get_result()->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $mid = (int)$_POST['MaterialID'];
        $qty = (float)$_POST['QuantityNeeded'];
        if ($mid > 0 && $qty > 0) {
            $stmt = $conn->prepare("INSERT INTO productmaterial (ProductID, MaterialID, QuantityNeeded) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE QuantityNeeded=?");
            $stmt->bind_param("iidd", $product_id, $mid, $qty, $qty);
            $stmt->execute();
            $message = "Material linked to product.";
        } else {
            $message = "Please select a material and enter quantity.";
        }
    } elseif ($action === 'delete') {
        $mid = (int)$_POST['MaterialID'];
        $stmt = $conn->prepare("DELETE FROM productmaterial WHERE ProductID=? AND MaterialID=?");
        $stmt->bind_param("ii", $product_id, $mid);
        $stmt->execute();
        $message = "Material removed.";
    }
}

$linked = $conn->prepare("SELECT pm.*, m.MaterialName, m.PricePerUnit FROM productmaterial pm JOIN material m ON pm.MaterialID = m.MaterialID WHERE pm.ProductID=?");
$linked->bind_param("i", $product_id);
$linked->execute();
$linked_result = $linked->get_result();

$all_materials = $conn->query("SELECT MaterialID, MaterialName FROM material ORDER BY MaterialName ASC");
$materials_arr = [];
while ($m = $all_materials->fetch_assoc()) {
    $materials_arr[] = $m;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product Materials - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Materials for: <?php echo htmlspecialchars($product['ProductName']); ?></h1>
    <p><a href="products.php" class="btn btn-edit">&larr; Back to Products</a></p>
    <br>

    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h2>Link Material to Product</h2>
        <form method="post" action="productmaterials.php?product_id=<?php echo $product_id; ?>">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Material</label>
                <select name="MaterialID" required>
                    <option value="0">-- Select Material --</option>
                    <?php foreach ($materials_arr as $m): ?>
                    <option value="<?php echo $m['MaterialID']; ?>"><?php echo htmlspecialchars($m['MaterialName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity Needed per Unit</label>
                <input type="number" name="QuantityNeeded" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Link Material</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Material</th>
                <th>Price Per Unit</th>
                <th>Qty Needed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $linked_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['MaterialName']); ?></td>
                <td><?php echo number_format($row['PricePerUnit'], 2); ?></td>
                <td><?php echo number_format($row['QuantityNeeded'], 2); ?></td>
                <td class="action-btns">
                    <form method="post" action="productmaterials.php?product_id=<?php echo $product_id; ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="MaterialID" value="<?php echo $row['MaterialID']; ?>">
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>