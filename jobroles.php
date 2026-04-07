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
        $type = trim($_POST['JobType']);
        $wage = (float)$_POST['WagePerDay'];
        if ($type !== "") {
            $stmt = $conn->prepare("INSERT INTO jobrole (JobType, WagePerDay) VALUES (?, ?)");
            $stmt->bind_param("sd", $type, $wage);
            $stmt->execute();
            $message = "Job role added.";
        } else {
            $message = "Job type is required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['JobRoleID'];
        $type = trim($_POST['JobType']);
        $wage = (float)$_POST['WagePerDay'];
        if ($type !== "") {
            $stmt = $conn->prepare("UPDATE jobrole SET JobType=?, WagePerDay=? WHERE JobRoleID=?");
            $stmt->bind_param("sdi", $type, $wage, $id);
            $stmt->execute();
            $message = "Job role updated.";
        } else {
            $message = "Job type is required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['JobRoleID'];
        $stmt = $conn->prepare("DELETE FROM jobrole WHERE JobRoleID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Job role deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM jobrole WHERE JobRoleID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT * FROM jobrole ORDER BY JobRoleID ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Job Roles - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Job Roles</h1>
    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <?php if ($edit_data): ?>
        <h2>Edit Job Role</h2>
        <form method="post" action="jobroles.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="JobRoleID" value="<?php echo $edit_data['JobRoleID']; ?>">
            <div class="form-group">
                <label>Job Type</label>
                <input type="text" name="JobType" value="<?php echo htmlspecialchars($edit_data['JobType']); ?>" required>
            </div>
            <div class="form-group">
                <label>Wage Per Day</label>
                <input type="number" name="WagePerDay" step="0.01" value="<?php echo $edit_data['WagePerDay']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Job Role</button>
            <a href="jobroles.php" class="btn btn-edit" style="margin-left:8px;">Cancel</a>
        </form>
        <?php else: ?>
        <h2>Add New Job Role</h2>
        <form method="post" action="jobroles.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Job Type</label>
                <input type="text" name="JobType" required>
            </div>
            <div class="form-group">
                <label>Wage Per Day</label>
                <input type="number" name="WagePerDay" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Job Role</button>
        </form>
        <?php endif; ?>
    </div>

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
                    <form method="post" action="jobroles.php">
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
