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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customers - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Customers</h1>
    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <?php if ($edit_data): ?>
        <h2>Edit Customer</h2>
        <form method="post" action="customers.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="CustomerID" value="<?php echo $edit_data['CustomerID']; ?>">
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="CustomerName" value="<?php echo htmlspecialchars($edit_data['CustomerName']); ?>" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="Address" value="<?php echo htmlspecialchars($edit_data['Address']); ?>">
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="ContactNumber" value="<?php echo htmlspecialchars($edit_data['ContactNumber']); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Update Customer</button>
            <a href="customers.php" class="btn btn-edit" style="margin-left:8px;">Cancel</a>
        </form>
        <?php else: ?>
        <h2>Add New Customer</h2>
        <form method="post" action="customers.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Customer Name</label>
                <input type="text" name="CustomerName" required>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="Address">
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="ContactNumber">
            </div>
            <button type="submit" class="btn btn-primary">Add Customer</button>
        </form>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer Name</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['CustomerID']; ?></td>
                <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>
                <td><?php echo htmlspecialchars($row['Address']); ?></td>
                <td><?php echo htmlspecialchars($row['ContactNumber']); ?></td>
                <td class="action-btns">
                    <a href="customers.php?edit=<?php echo $row['CustomerID']; ?>" class="btn btn-edit">Edit</a>
                    <form method="post" action="customers.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="CustomerID" value="<?php echo $row['CustomerID']; ?>">
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
