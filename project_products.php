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


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Project Products - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Products for Project: <?php echo htmlspecialchars($project['ProjectName']); ?></h1>
    <p>Customer: <?php echo htmlspecialchars($project['CustomerName'] ?? '-'); ?> &nbsp;|&nbsp; Date: <?php echo $project['ProjectDate']; ?></p>
    <br>
    <p>
        <a href="projects.php" class="btn btn-edit">&larr; Back to Projects</a>
        &nbsp;
        <a href="project_labour.php?project_id=<?php echo $project_id; ?>" class="btn btn-link">Manage Labour</a>
    </p>
    <br>

    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h2>Add Product to Project</h2>
        <form method="post" action="project_products.php?project_id=<?php echo $project_id; ?>">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Product</label>
                <select name="ProductID" required>
                    <option value="0">-- Select Product --</option>
                    <?php foreach ($products_arr as $p): ?>
                    <option value="<?php echo $p['ProductID']; ?>"><?php echo htmlspecialchars($p['ProductName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="Quantity" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Selling Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $linked_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['ProductName']); ?></td>
                <td><?php echo number_format($row['SellingPrice'], 2); ?></td>
                <td><?php echo $row['Quantity']; ?></td>
                <td class="action-btns">
                    <form method="post" action="project_products.php?project_id=<?php echo $project_id; ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ProductID" value="<?php echo $row['ProductID']; ?>">
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
