<?php
// ============================================================
// jobroles.php
// Purpose : Full CRUD management page for the 'jobrole' table.
// A job role defines a type of labour (e.g. 'Electrician') and
// its daily wage.  Job roles are assigned to projects through
// the project_labour.php page (projectlabour junction table).
// ============================================================

session_start(); // Start/resume session for authentication check

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect unauthenticated users
    exit();
}

require_once 'db.php'; // Obtain $conn database connection

$message = ''; // Initialise the user-facing feedback message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action']; // 'add', 'edit', or 'delete'

    if ($action === 'add') {

        $type = trim($_POST['JobType']);       // Name of the job role (e.g. 'Plumber')
        $wage = (float)$_POST['WagePerDay'];   // Daily pay rate, cast to float

        if ($type !== '') {
            // INSERT a new job role with its wage
            $stmt = $conn->prepare('INSERT INTO jobrole (JobType, WagePerDay) VALUES (?, ?)');
            $stmt->bind_param('sd', $type, $wage); // 's'=string, 'd'=double
            $stmt->execute();
            $message = 'Job role added.';
        } else {
            $message = 'Job type is required.';
        }

    } elseif ($action === 'edit') {

        $id   = (int)$_POST['JobRoleID'];      // PK of the row to update
        $type = trim($_POST['JobType']);
        $wage = (float)$_POST['WagePerDay'];

        if ($type !== '') {
            $stmt = $conn->prepare('UPDATE jobrole SET JobType=?, WagePerDay=? WHERE JobRoleID=?');
            $stmt->bind_param('sdi', $type, $wage, $id); // 's' string, 'd' double, 'i' int
            $stmt->execute();
            $message = 'Job role updated.';
        } else {
            $message = 'Job type is required.';
        }

    } elseif ($action === 'delete') {

        $id = (int)$_POST['JobRoleID'];
        $stmt = $conn->prepare('DELETE FROM jobrole WHERE JobRoleID=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $message = 'Job role deleted.';
    }
}

// ---- Pre-fill form for edit (GET ?edit=ID) ----
$edit_data = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->prepare('SELECT * FROM jobrole WHERE JobRoleID=?');
    $res->bind_param('i', $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

// ---- Fetch all job roles for the listing table ----
$records = $conn->query('SELECT * FROM jobrole ORDER BY JobRoleID ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Roles – Enterprise Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once 'nav.php'; ?>
<div class="container">
    <h1>Job Roles</h1>

    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Add / Edit form -->
    <div class="form-box">
        <h2><?php echo $edit_data ? 'Edit Job Role' : 'Add Job Role'; ?></h2>
        <form method="POST" action="jobroles.php">
            <input type="hidden" name="action" value="<?php echo $edit_data ? 'edit' : 'add'; ?>">
            <?php if ($edit_data): ?>
                <!-- Pass the primary key when editing -->
                <input type="hidden" name="JobRoleID" value="<?php echo $edit_data['JobRoleID']; ?>">
            <?php endif; ?>

            <!-- Job Type name -->
            <div class="form-group">
                <label>Job Type *</label>
                <input type="text" name="JobType" required
                       value="<?php echo $edit_data ? htmlspecialchars($edit_data['JobType']) : ''; ?>">
            </div>

            <!-- Wage per day: step allows decimal values -->
            <div class="form-group">
                <label>Wage Per Day</label>
                <input type="number" step="0.01" name="WagePerDay"
                       value="<?php echo $edit_data ? $edit_data['WagePerDay'] : '0.00'; ?>">
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $edit_data ? 'Update Job Role' : 'Add Job Role'; ?>
            </button>
            <?php if ($edit_data): ?>
                <a href="jobroles.php" class="btn btn-primary" style="margin-left:8px;">Cancel</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- All Job Roles table -->
    <h2>All Job Roles</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Job Type</th>
                <th>Wage Per Day</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['JobRoleID']; ?></td>
                <td><?php echo htmlspecialchars($row['JobType']); ?></td>
                <td><?php echo number_format($row['WagePerDay'], 2); ?></td>
                <td class="action-btns">
                    <a href="jobroles.php?edit=<?php echo $row['JobRoleID']; ?>" class="btn btn-edit">Edit</a>
                    <form method="POST" action="jobroles.php"
                          onsubmit="return confirm('Delete this job role?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="JobRoleID" value="<?php echo $row['JobRoleID']; ?>">
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
