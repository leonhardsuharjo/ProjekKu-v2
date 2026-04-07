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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Materials - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Materials</h1>
    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <?php if ($edit_data): ?>
        <h2>Edit Material</h2>
        <form method="post" action="materials.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="MaterialID" value="<?php echo $edit_data['MaterialID']; ?>">
            <div class="form-group">
                <label>Material Name</label>
                <input type="text" name="MaterialName" value="<?php echo htmlspecialchars($edit_data['MaterialName']); ?>" required>
            </div>
            <div class="form-group">
                <label>Price Per Unit</label>
                <input type="number" name="PricePerUnit" step="0.01" value="<?php echo $edit_data['PricePerUnit']; ?>" required>
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
            <button type="submit" class="btn btn-primary">Update Material</button>
            <a href="materials.php" class="btn btn-edit" style="margin-left:8px;">Cancel</a>
        </form>
        <?php else: ?>
        <h2>Add New Material</h2>
        <form method="post" action="materials.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Material Name</label>
                <input type="text" name="MaterialName" required>
            </div>
            <div class="form-group">
                <label>Price Per Unit</label>
                <input type="number" name="PricePerUnit" step="0.01" required>
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
            <button type="submit" class="btn btn-primary">Add Material</button>
        </form>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Material Name</th>
                <th>Price Per Unit</th>
                <th>Supplier</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['MaterialID']; ?></td>
                <td><?php echo htmlspecialchars($row['MaterialName']); ?></td>
                <td><?php echo number_format($row['PricePerUnit'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['SupplierName'] ?? '-'); ?></td>
                <td class="action-btns">
                    <a href="materials.php?edit=<?php echo $row['MaterialID']; ?>" class="btn btn-edit">Edit</a>
                    <form method="post" action="materials.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="MaterialID" value="<?php echo $row['MaterialID']; ?>">
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
