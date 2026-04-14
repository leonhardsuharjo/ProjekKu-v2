<?php
// ============================================================
// materials.php
// Purpose : Full CRUD management page for the 'material' table.
// Raw materials have a cost per unit and are linked to a supplier.
// Materials are referenced by productmaterials.php to define
// the bill-of-materials for each product.
// ============================================================

session_start(); // Resume the user's session

// Guard: redirect unauthenticated users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db.php'; // Opens the MySQLi $conn

$message = ''; // Feedback string shown after form submission

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action']; // 'add', 'edit', or 'delete'

    if ($action === 'add') {

        $name  = trim($_POST['MaterialName']);  // Sanitise material name
        $price = (float)$_POST['PricePerUnit']; // Cost of one unit of this material
        $sid   = (int)$_POST['SupplierID'];     // FK to supplier (0 = none)

        if ($name !== '') {
            $stmt = $conn->prepare('INSERT INTO material (MaterialName, PricePerUnit, SupplierID) VALUES (?, ?, ?)');
            $stmt->bind_param('sdi', $name, $price, $sid); // 's'=string, 'd'=double, 'i'=int
            $stmt->execute();
            $message = 'Material added successfully.';
        } else {
            $message = 'Material name is required.';
        }

    } elseif ($action === 'edit') {

        $id    = (int)$_POST['MaterialID'];     // Primary key of the row to update
        $name  = trim($_POST['MaterialName']);
        $price = (float)$_POST['PricePerUnit'];
        $sid   = (int)$_POST['SupplierID'];

        if ($name !== '') {
            $stmt = $conn->prepare('UPDATE material SET MaterialName=?, PricePerUnit=?, SupplierID=? WHERE MaterialID=?');
            $stmt->bind_param('sdii', $name, $price, $sid, $id); // 's', 'd', 'i'(supplier), 'i'(material)
            $stmt->execute();
            $message = 'Material updated.';
        } else {
            $message = 'Material name is required.';
        }

    } elseif ($action === 'delete') {

        $id = (int)$_POST['MaterialID'];
        $stmt = $conn->prepare('DELETE FROM material WHERE MaterialID=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $message = 'Material deleted.';
    }
}

// ---- Pre-fill form for editing (GET ?edit=ID) ----
$edit_data = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->prepare('SELECT * FROM material WHERE MaterialID=?');
    $res->bind_param('i', $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

// ---- Fetch all materials joined with their supplier name ----
// LEFT JOIN ensures materials without a supplier still appear in the list.
$records = $conn->query('SELECT m.*, s.SupplierName FROM material m LEFT JOIN supplier s ON m.SupplierID = s.SupplierID ORDER BY m.MaterialID ASC');

// ---- Fetch all suppliers for the add/edit dropdown ----
$suppliers = $conn->query('SELECT SupplierID, SupplierName FROM supplier ORDER BY SupplierName ASC');
$suppliers_arr = [];
while ($s = $suppliers->fetch_assoc()) {
    $suppliers_arr[] = $s; // Build a reusable array of supplier options
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materials – Enterprise Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once 'nav.php'; ?>
<div class="container">
    <h1>Materials</h1>

    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Add / Edit form -->
    <div class="form-box">
        <h2><?php echo $edit_data ? 'Edit Material' : 'Add Material'; ?></h2>
        <form method="POST" action="materials.php">
            <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
            <?php if ($edit_data): ?>
                <!-- MaterialID is required so the UPDATE targets the correct row -->
                <input type="hidden" name="MaterialID" value="<?php echo $edit_data['MaterialID']; ?>">
            <?php endif; ?>

            <!-- Material Name -->
            <div class="form-group">
                <label>Material Name *</label>
                <input type="text" name="MaterialName" required
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['MaterialName']) : ''; ?>">
            </div>

            <!-- Price Per Unit: step="0.01" allows cents/pence precision -->
            <div class="form-group">
                <label>Price Per Unit</label>
                <input type="number" step="0.01" name="PricePerUnit"
                       value="<?php echo $edit_data ? $edit_data['PricePerUnit'] : '0.00'; ?>">
            </div>

            <!-- Supplier dropdown (optional) -->
            <div class="form-group">
                <label>Supplier (optional)</label>
                <select name="SupplierID">
                    <option value="0">-- None --</option>
                    <?php foreach ($suppliers_arr as $s): ?>
                        <option value="<?php echo $s['SupplierID']; ?>"
                            <?php echo ($edit_data && $edit_data['SupplierID'] == $s['SupplierID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['SupplierName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $edit_data ? 'Update Material' : 'Add Material'; ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="materials.php" class="btn btn-primary" style="margin-left:8px;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- All Materials table -->
    <h2>All Materials</h2>
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
                <td><?php echo $row['SupplierName'] ? htmlspecialchars($row['SupplierName']) : '—'; ?></td>
                <td class="action-btns">
                    <a href="materials.php?edit=<?php echo $row['MaterialID']; ?>" class="btn btn-edit">Edit</a>
                    <form method="POST" action="materials.php"
                          onsubmit="return confirm('Delete this material?');">
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
