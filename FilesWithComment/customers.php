<?php
// ============================================================
// customers.php
// Purpose : Full CRUD (Create, Read, Update, Delete) management
//           page for the 'customer' table.
// Handles three POST actions: 'add', 'edit', 'delete'.
// Handles one GET parameter: 'edit' (pre-populates the form).
// ============================================================

// Start the session so we can check login status and show the user's name.
session_start();

// Redirect unauthenticated visitors to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // HTTP redirect to login
    exit();                        // Stop any further code execution
}

// Load the database connection; $conn is a MySQLi object.
require_once 'db.php';

// Initialise feedback message shown to the user after an action.
$message = '';

// ---- Handle POST requests (form submissions) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read which action button was pressed: 'add', 'edit', or 'delete'.
    $action = $_POST['action'];

    // ---- ADD a new customer ----
    if ($action === 'add') {

        // Sanitise the customer name by removing leading/trailing whitespace.
        $name    = trim($_POST['CustomerName']);

        // Sanitise the address field.
        $address = trim($_POST['Address']);

        // Sanitise the contact number field.
        $contact = trim($_POST['ContactNumber']);

        // Validate that at least the customer name was provided.
        if ($name !== '') {

            // Prepare an INSERT statement with ? placeholders to prevent SQL injection.
            $stmt = $conn->prepare('INSERT INTO customer (CustomerName, Address, ContactNumber) VALUES (?, ?, ?)');

            // Bind three string values ('sss') to the placeholders.
            $stmt->bind_param('sss', $name, $address, $contact);

            // Execute the INSERT to save the new customer.
            $stmt->execute();

            // Set the success feedback message.
            $message = 'Customer added successfully.';
        } else {
            // Name field was empty — inform the user.
            $message = 'Customer name is required.';
        }

    // ---- EDIT (update) an existing customer ----
    } elseif ($action === 'edit') {

        // Cast the posted CustomerID to an integer to prevent injection.
        $id      = (int)$_POST['CustomerID'];

        // Sanitise the updated name.
        $name    = trim($_POST['CustomerName']);

        // Sanitise the updated address.
        $address = trim($_POST['Address']);

        // Sanitise the updated contact number.
        $contact = trim($_POST['ContactNumber']);

        // Only perform the update if a name was provided.
        if ($name !== '') {

            // Prepare an UPDATE statement targeting the specific CustomerID.
            $stmt = $conn->prepare('UPDATE customer SET CustomerName=?, Address=?, ContactNumber=? WHERE CustomerID=?');

            // Bind three strings and one integer ('sssi') to the placeholders.
            $stmt->bind_param('sssi', $name, $address, $contact, $id);

            // Execute the UPDATE query.
            $stmt->execute();

            $message = 'Customer updated successfully.';
        } else {
            $message = 'Customer name is required.';
        }

    // ---- DELETE a customer ----
    } elseif ($action === 'delete') {

        // Safely cast the CustomerID to integer before using it in the query.
        $id = (int)$_POST['CustomerID'];

        // Prepare a DELETE statement for the specified customer.
        $stmt = $conn->prepare('DELETE FROM customer WHERE CustomerID=?');

        // Bind the integer ID to the placeholder.
        $stmt->bind_param('i', $id);

        // Execute the DELETE.
        $stmt->execute();

        $message = 'Customer deleted.';
    }
}

// ---- Pre-load data for the EDIT form ----
// $edit_data is null by default; it gets populated if ?edit=ID is in the URL.
$edit_data = null;

// Check if the URL contains an 'edit' query parameter (e.g. customers.php?edit=3).
if (isset($_GET['edit'])) {

    // Cast the GET parameter to integer for safety.
    $id = (int)$_GET['edit'];

    // Fetch the specific customer row so the form can be pre-filled.
    $res = $conn->prepare('SELECT * FROM customer WHERE CustomerID=?');
    $res->bind_param('i', $id);
    $res->execute();

    // fetch_assoc() returns the row as an associative array, or false if not found.
    $edit_data = $res->get_result()->fetch_assoc();
}

// ---- Fetch all customers for the listing table ----
// Orders by CustomerID ascending so the list is stable.
$records = $conn->query('SELECT * FROM customer ORDER BY CustomerID ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers – Enterprise Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Shared navigation bar -->
<?php require_once 'nav.php'; ?>

<div class="container">
    <h1>Customers</h1>

    <!-- Show the feedback message if one exists -->
    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- ---- ADD / EDIT Form ---- -->
    <!-- If $edit_data is set, the form becomes an edit form; otherwise it is an add form -->
    <div class="form-box">
        <!-- Dynamic heading: 'Edit Customer' when editing, 'Add Customer' otherwise -->
        <h2><?php echo $edit_data ? 'Edit Customer' : 'Add Customer'; ?></h2>

        <!-- POST to this same page; action hidden input tells the PHP which branch to run -->
        <form method="POST" action="customers.php">

            <!-- Hidden field: determines whether PHP runs the 'add' or 'edit' branch -->
            <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">

            <!-- When editing, send the CustomerID so the UPDATE knows which row to change -->
            <?php if ($edit_data): ?>
                <input type="hidden" name="CustomerID" value="<?php echo $edit_data['CustomerID']; ?>">
            <?php endif; ?>

            <!-- Customer Name field; pre-filled with existing value when editing -->
            <div class="form-group">
                <label>Customer Name *</label>
                <input type="text" name="CustomerName" required
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['CustomerName']) : ''; ?>">
            </div>

            <!-- Address field; optional -->
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="Address"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['Address']) : ''; ?>">
            </div>

            <!-- Contact Number field; optional -->
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="ContactNumber"
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['ContactNumber']) : ''; ?>">
            </div>

            <!-- Submit button label changes based on add vs. edit mode -->
            <button type="submit" class="btn btn-primary">
                <?php echo $edit_data ? 'Update Customer' : 'Add Customer'; ?>
            </button>

            <!-- Cancel button: visible only in edit mode; goes back to the plain list -->
            <?php if ($edit_data): ?>
                <a href="customers.php" class="btn btn-primary" style="margin-left:8px;">Cancel</a>
            <?php endif; ?>

        </form>
    </div><!-- /.form-box -->

    <!-- ---- Records Listing Table ---- -->
    <h2>All Customers</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>             <!-- Customer primary key -->
                <th>Customer Name</th>  <!-- Full name or company -->
                <th>Address</th>        <!-- Physical/postal address -->
                <th>Contact Number</th> <!-- Phone number -->
                <th>Actions</th>        <!-- Edit and Delete buttons -->
            </tr>
        </thead>
        <tbody>
            <!-- Loop through every customer row returned by the SELECT query -->
            <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <!-- Display the auto-increment customer ID -->
                <td><?php echo $row['CustomerID']; ?></td>

                <!-- Display the customer name; htmlspecialchars prevents XSS -->
                <td><?php echo htmlspecialchars($row['CustomerName']); ?></td>

                <!-- Display the address -->
                <td><?php echo htmlspecialchars($row['Address']); ?></td>

                <!-- Display the contact number -->
                <td><?php echo htmlspecialchars($row['ContactNumber']); ?></td>

                <!-- Action buttons cell -->
                <td class="action-btns">

                    <!-- Edit link: adds ?edit=ID to the URL, which pre-fills the form above -->
                    <a href="customers.php?edit=<?php echo $row['CustomerID']; ?>"
                       class="btn btn-edit">Edit</a>

                    <!-- Delete form: POSTs the CustomerID with action=delete -->
                    <form method="POST" action="customers.php"
                          onsubmit="return confirm('Delete this customer?');">
                        <!-- Hidden field tells PHP this is a delete request -->
                        <input type="hidden" name="action" value="delete">
                        <!-- Hidden field carries the ID of the row to delete -->
                        <input type="hidden" name="CustomerID" value="<?php echo $row['CustomerID']; ?>">
                        <!-- Red delete button; confirm dialog prevents accidental deletion -->
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>

                </td>
            </tr>
            <?php endwhile; ?> <!-- End of customer loop -->
        </tbody>
    </table>

</div><!-- /.container -->
</body>
</html>
