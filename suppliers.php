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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Suppliers - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Suppliers</h1>
    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <?php if ($edit_data): ?>
        <h2>Edit Supplier</h2>
        <form method="post" action="suppliers.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="SupplierID" value="<?php echo $edit_data['SupplierID']; ?>">
            <div class="form-group">
                <label>Supplier Name</label>
                <input type="text" name="SupplierName" value="<?php echo htmlspecialchars($edit_data['SupplierName']); ?>" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="Address" value="<?php echo htmlspecialchars($edit_data['Address']); ?>">
            </div>
            <div class="form-group">
                <label>Contact No</label>
                <input type="text" name="ContactNo" value="<?php echo htmlspecialchars($edit_data['ContactNo']); ?>">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="Status">
                    <option value="Active" <?php if ($edit_data['Status'] === 'Active') echo 'selected'; ?>>Active</option>
                    <option value="Inactive" <?php if ($edit_data['Status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Supplier</button>
            <a href="suppliers.php" class="btn btn-edit" style="margin-left:8px;">Cancel</a>
        </form>
        <?php else: ?>
        <h2>Add New Supplier</h2>
        <form method="post" action="suppliers.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Supplier Name</label>
                <input type="text" name="SupplierName" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="Address">
            </div>
            <div class="form-group">
                <label>Contact No</label>
                <input type="text" name="ContactNo">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="Status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Supplier</button>
        </form>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Supplier Name</th>
                <th>Address</th>
                <th>Contact No</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['SupplierID']; ?></td>
                <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                <td><?php echo htmlspecialchars($row['Address']); ?></td>
                <td><?php echo htmlspecialchars($row['ContactNo']); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                <td class="action-btns">
                    <a href="suppliers.php?edit=<?php echo $row['SupplierID']; ?>" class="btn btn-edit">Edit</a>
                    <form method="post" action="suppliers.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="SupplierID" value="<?php echo $row['SupplierID']; ?>">
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