<?php
// ============================================================
// products.php
// Purpose : Full CRUD management page for the 'product' table.
// Each product has a selling price and is optionally linked to
// a supplier.  A separate page (productmaterials.php) manages
// the materials required to manufacture each product.
// ============================================================

session_start(); // Resume the user's session

// Redirect guests to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db.php'; // Load MySQLi $conn

$message = ''; // Feedback message initialised to empty

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action']; // Which CRUD operation: 'add', 'edit', or 'delete'

    if ($action === 'add') {

        $name  = trim($_POST['ProductName']);  // Sanitise product name
        $price = (float)$_POST['SellingPrice']; // Cast selling price to float
        $sid   = (int)$_POST['SupplierID'];    // Cast supplier FK to integer (0 = none)

        if ($name !== '') {
            // INSERT the new product; SupplierID may be 0 (no supplier selected)
            $stmt = $conn->prepare('INSERT INTO product (ProductName, SellingPrice, SupplierID) VALUES (?, ?, ?)');
            // 's' = string, 'd' = double (float), 'i' = integer
            $stmt->bind_param('sdi', $name, $price, $sid);
            $stmt->execute();
            $message = 'Product added successfully.';
        } else {
            $message = 'Product name is required.';
        }

    } elseif ($action === 'edit') {

        $id    = (int)$_POST['ProductID'];     // Primary key of the row to update
        $name  = trim($_POST['ProductName']);
        $price = (float)$_POST['SellingPrice'];
        $sid   = (int)$_POST['SupplierID'];

        if ($name !== '') {
            // UPDATE the matching product row
            $stmt = $conn->prepare('UPDATE product SET ProductName=?, SellingPrice=?, SupplierID=? WHERE ProductID=?');
            // 's' string, 'd' double, 'i' int (SupplierID), 'i' int (ProductID)
            $stmt->bind_param('sdii', $name, $price, $sid, $id);
            $stmt->execute();
            $message = 'Product updated.';
        } else {
            $message = 'Product name is required.';
        }

    } elseif ($action === 'delete') {

        $id = (int)$_POST['ProductID']; // Safely cast the ID
        $stmt = $conn->prepare('DELETE FROM product WHERE ProductID=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $message = 'Product deleted.';
    }
}

// ---- Pre-fill form for editing ----
$edit_data = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->prepare('SELECT * FROM product WHERE ProductID=?');
    $res->bind_param('i', $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc(); // Returns the product row or null
}

// ---- Fetch all products joined with supplier name ----
// LEFT JOIN means products with no supplier still appear (SupplierName will be NULL).
$records = $conn->query('SELECT p.*, s.SupplierName FROM product p LEFT JOIN supplier s ON p.SupplierID = s.SupplierID ORDER BY p.ProductID ASC');

// ---- Fetch all suppliers for the dropdown ----
$suppliers = $conn->query('SELECT SupplierID, SupplierName FROM supplier ORDER BY SupplierName ASC');
$suppliers_arr = []; // Accumulate into array so we can loop it multiple times
while ($s = $suppliers->fetch_assoc()) {
    $suppliers_arr[] = $s; // Append each supplier row
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products – Enterprise Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once 'nav.php'; ?>
<div class="container">
    <h1>Products</h1>

    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Add / Edit form -->
    <div class="form-box">
        <h2><?php echo $edit_data ? 'Edit Product' : 'Add Product'; ?></h2>
        <form method="POST" action="products.php">
            <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
            <?php if ($edit_data): ?>
                <!-- Pass the ProductID when editing so PHP knows which row to UPDATE -->
                <input type="hidden" name="ProductID" value="<?php echo $edit_data['ProductID']; ?>">
            <?php endif; ?>

            <!-- Product Name -->
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="ProductName" required
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['ProductName']) : ''; ?>">
            </div>

            <!-- Selling Price: numeric input with 2-decimal step -->
            <div class="form-group">
                <label>Selling Price</label>
                <input type="number" step="0.01" name="SellingPrice"
                       value="<?php echo $edit_data ? $edit_data['SellingPrice'] : '0.00'; ?>">
            </div>

            <!-- Supplier dropdown: optional linkage -->
            <div class="form-group">
                <label>Supplier (optional)</label>
                <select name="SupplierID">
                    <!-- First option: no supplier selected -->
                    <option value="0">-- None --</option>
                    <?php foreach ($suppliers_arr as $s): ?>
                        <!-- Mark the option as selected if it matches the current product's supplier -->
                        <option value="<?php echo $s['SupplierID']; ?>"
                            <?php echo ($edit_data && $edit_data['SupplierID'] == $s['SupplierID']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['SupplierName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $edit_data ? 'Update Product' : 'Add Product'; ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="products.php" class="btn btn-primary" style="margin-left:8px;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products listing table -->
    <h2>All Products</h2>
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
                <!-- number_format formats the decimal to 2 places with comma thousands separator -->
                <td><?php echo number_format($row['SellingPrice'], 2); ?></td>
                <!-- Display supplier name or '—' if no supplier is linked -->
                <td><?php echo $row['SupplierName'] ? htmlspecialchars($row['SupplierName']) : '—'; ?></td>
                <td class="action-btns">
                    <!-- Edit link -->
                    <a href="products.php?edit=<?php echo $row['ProductID']; ?>" class="btn btn-edit">Edit</a>

                    <!-- Link to the product's materials sub-page -->
                    <a href="productmaterials.php?product_id=<?php echo $row['ProductID']; ?>"
                       class="btn btn-link">Materials</a>

                    <!-- Delete form -->
                    <form method="POST" action="products.php"
                          onsubmit="return confirm('Delete this product?');">
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
