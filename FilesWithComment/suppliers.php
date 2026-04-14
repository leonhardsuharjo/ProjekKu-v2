<?php
// ============================================================
// suppliers.php
// Purpose : Full CRUD management page for the 'supplier' table.
// Suppliers can be marked Active or Inactive and are referenced
// by the 'product' and 'material' tables via foreign keys.
// ============================================================

// Start the session so login status can be verified.
session_start();

// Redirect unauthenticated users to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Load the database connection object $conn.
require_once 'db.php';

// Initialise the user-facing feedback message.
$message = '';

// Process any submitted form (POST request).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Determine which action was triggered: 'add', 'edit', or 'delete'.
    $action = $_POST['action'];

    // ---- ADD a new supplier ----
    if ($action === 'add') {

        // Trim whitespace from all text inputs.
        $name    = trim($_POST['SupplierName']);
        $address = trim($_POST['Address']);
        $contact = trim($_POST['ContactNo']);

        // Read the status dropdown value ('Active' or 'Inactive').
        $status  = $_POST['Status'];

        // Validate that the supplier name is not empty.
        if ($name !== '') {

            // Prepare INSERT with four ? placeholders.
            $stmt = $conn->prepare('INSERT INTO supplier (SupplierName, Address, ContactNo, Status) VALUES (?, ?, ?, ?)');

            // Bind four string values ('ssss').
            $stmt->bind_param('ssss', $name, $address, $contact, $status);
            $stmt->execute(); // Run the INSERT
            $message = 'Supplier added successfully.';
        } else {
            $message = 'Supplier name is required.';
        }

    // ---- EDIT an existing supplier ----
    } elseif ($action === 'edit') {

        $id      = (int)$_POST['SupplierID']; // Cast ID to integer for safety
        $name    = trim($_POST['SupplierName']);
        $address = trim($_POST['Address']);
        $contact = trim($_POST['ContactNo']);
        $status  = $_POST['Status'];           // 'Active' or 'Inactive'

        if ($name !== '') {

            // UPDATE the row matching the given SupplierID.
            $stmt = $conn->prepare('UPDATE supplier SET SupplierName=?, Address=?, ContactNo=?, Status=? WHERE SupplierID=?');

            // Bind four strings and one integer ('ssssi').
            $stmt->bind_param('ssssi', $name, $address, $contact, $status, $id);
            $stmt->execute();
            $message = 'Supplier updated.';
        } else {
            $message = 'Supplier name is required.';
        }

    // ---- DELETE a supplier ----
    } elseif ($action === 'delete') {

        $id = (int)$_POST['SupplierID']; // Integer-cast for safety

        // Prepare and execute the DELETE.
        $stmt = $conn->prepare('DELETE FROM supplier WHERE SupplierID=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $message = 'Supplier deleted.';
    }
}

// ---- Pre-load data for the edit form (GET ?edit=ID) ----
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare('SELECT * FROM supplier WHERE SupplierID=?');
    $res->bind_param('i', $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc(); // Fetch the row to pre-fill the form
}

// ---- Fetch all suppliers for the listing table ----
$records = $conn->query('SELECT * FROM supplier ORDER BY SupplierID ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers – Enterprise Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once 'nav.php'; ?> <!-- Shared navigation bar -->

<div class="container">
    <h1>Suppliers</h1>

    <!-- Feedback message (success or error) -->
    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Add / Edit form -->
    <div class="form-box">
        <h2><?php echo $edit_data ? 'Edit Supplier' : 'Add Supplier'; ?></h2>
        <form method="POST" action="suppliers.php">

            <!-- Hidden action field: 'add' or 'edit' -->
            <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">

            <!-- Carry the ID when editing so the UPDATE knows which row -->
            <?php if ($edit_data): ?>
                <input type="hidden" name="SupplierID" value="<?php echo $edit_data['SupplierID']; ?>">
            <?php endif; ?>

            <!-- Supplier Name field -->
            <div class="form-group">
                <label>Supplier Name *</label>
                <input type="text" name="SupplierName" required
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['SupplierName']) : ''; ?>">
            </div>

            <!-- Address field (optional) -->
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="Address"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['Address']) : ''; ?>">
            </div>

            <!-- Contact Number field (optional) -->
            <div class="form-group">
                <label>Contact No</label>
                <input type="text" name="ContactNo"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['ContactNo']) : ''; ?>">
            </div>

            <!-- Status dropdown: Active (default) or Inactive -->
            <div class="form-group">
                <label>Status</label>
                <select name="Status">
                    <!-- Mark the option as selected if it matches the current value -->
                    <option value="Active"   <?php echo (!$edit_data || $edit_data['Status']==='Active')   ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($edit_data && $edit_data['Status']==='Inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>

            <!-- Submit and optional Cancel buttons -->
            <button type="submit" class="btn btn-primary">
                <?php echo $edit_data ? 'Update Supplier' : 'Add Supplier'; ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="suppliers.php" class="btn btn-primary" style="margin-left:8px;">Cancel</a>
            <?php endif; ?>

        </form>
    </div>

    <!-- All Suppliers listing table -->
    <h2>All Suppliers</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Supplier Name</th>
                <th>Address</th>
                <th>Contact No</th>
                <th>Status</th>   <!-- Active or Inactive badge -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $records->fetch_assoc()): ?> <!-- Loop through each supplier row -->
            <tr>
                <td><?php echo $row['SupplierID']; ?></td>
                <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                <td><?php echo htmlspecialchars($row['Address']); ?></td>
                <td><?php echo htmlspecialchars($row['ContactNo']); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
                <td class="action-btns">
                    <!-- Edit link pre-fills the form -->
                    <a href="suppliers.php?edit=<?php echo $row['SupplierID']; ?>" class="btn btn-edit">Edit</a>

                    <!-- Delete form with confirmation prompt -->
                    <form method="POST" action="suppliers.php"
                          onsubmit="return confirm('Delete this supplier?');">
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
