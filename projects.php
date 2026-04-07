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
        $name = trim($_POST['ProjectName']);
        $date = $_POST['ProjectDate'];
        $transport = (float)$_POST['TransportCost'];
        $value = (float)$_POST['ProjectValue'];
        $cid = (int)$_POST['CustomerID'];
        if ($name !== "" && $date !== "") {
            $stmt = $conn->prepare("INSERT INTO project (ProjectName, ProjectDate, TransportCost, ProjectValue, CustomerID) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddi", $name, $date, $transport, $value, $cid);
            $stmt->execute();
            $message = "Project added successfully.";
        } else {
            $message = "Project name and date are required.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['ProjectID'];
        $name = trim($_POST['ProjectName']);
        $date = $_POST['ProjectDate'];
        $transport = (float)$_POST['TransportCost'];
        $value = (float)$_POST['ProjectValue'];
        $cid = (int)$_POST['CustomerID'];
        if ($name !== "" && $date !== "") {
            $stmt = $conn->prepare("UPDATE project SET ProjectName=?, ProjectDate=?, TransportCost=?, ProjectValue=?, CustomerID=? WHERE ProjectID=?");
            $stmt->bind_param("ssddii", $name, $date, $transport, $value, $cid, $id);
            $stmt->execute();
            $message = "Project updated.";
        } else {
            $message = "Project name and date are required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['ProjectID'];
        $stmt = $conn->prepare("DELETE FROM project WHERE ProjectID=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message = "Project deleted.";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM project WHERE ProjectID=?");
    $res->bind_param("i", $id);
    $res->execute();
    $edit_data = $res->get_result()->fetch_assoc();
}

$records = $conn->query("SELECT p.*, c.CustomerName FROM project p LEFT JOIN customer c ON p.CustomerID = c.CustomerID ORDER BY p.ProjectDate DESC");
$customers = $conn->query("SELECT CustomerID, CustomerName FROM customer ORDER BY CustomerName ASC");
$customers_arr = [];
while ($c = $customers->fetch_assoc()) {
    $customers_arr[] = $c;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Projects - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Projects</h1>
    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <?php if ($edit_data): ?>
        <h2>Edit Project</h2>
        <form method="post" action="projects.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="ProjectID" value="<?php echo $edit_data['ProjectID']; ?>">
            <div class="form-group">
                <label>Project Name</label>
                <input type="text" name="ProjectName" value="<?php echo htmlspecialchars($edit_data['ProjectName']); ?>" required>
            </div>
            <div class="form-group">
                <label>Project Date</label>
                <input type="date" name="ProjectDate" value="<?php echo $edit_data['ProjectDate']; ?>" required>
            </div>
            <div class="form-group">
                <label>Project Value</label>
                <input type="number" name="ProjectValue" step="0.01" value="<?php echo $edit_data['ProjectValue']; ?>" required>
            </div>
            <div class="form-group">
                <label>Transport Cost</label>
                <input type="number" name="TransportCost" step="0.01" value="<?php echo $edit_data['TransportCost']; ?>">
            </div>
            <div class="form-group">
                <label>Customer</label>
                <select name="CustomerID">
                    <option value="0">-- None --</option>
                    <?php foreach ($customers_arr as $c): ?>
                    <option value="<?php echo $c['CustomerID']; ?>" <?php if ($edit_data['CustomerID'] == $c['CustomerID']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['CustomerName']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Project</button>
            <a href="projects.php" class="btn btn-edit" style="margin-left:8px;">Cancel</a>
        </form>
        <?php else: ?>
        <h2>Add New Project</h2>
        <form method="post" action="projects.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Project Name</label>
                <input type="text" name="ProjectName" required>
            </div>
            <div class="form-group">
                <label>Project Date</label>
                <input type="date" name="ProjectDate" required>
            </div>
            <div class="form-group">
                <label>Project Value</label>
                <input type="number" name="ProjectValue" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Transport Cost</label>
                <input type="number" name="TransportCost" step="0.01" value="0">
            </div>
            <div class="form-group">
                <label>Customer</label>
                <select name="CustomerID">
                    <option value="0">-- None --</option>
                    <?php foreach ($customers_arr as $c): ?>
                    <option value="<?php echo $c['CustomerID']; ?>"><?php echo htmlspecialchars($c['CustomerName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Project</button>
        </form>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Project Name</th>
                <th>Date</th>
                <th>Value</th>
                <th>Transport</th>
                <th>Customer</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $records->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['ProjectID']; ?></td>
                <td><?php echo htmlspecialchars($row['ProjectName']); ?></td>
                <td><?php echo $row['ProjectDate']; ?></td>
                <td><?php echo number_format($row['ProjectValue'], 2); ?></td>
                <td><?php echo number_format($row['TransportCost'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['CustomerName'] ?? '-'); ?></td>
                <td class="action-btns">
                    <a href="projects.php?edit=<?php echo $row['ProjectID']; ?>" class="btn btn-edit">Edit</a>
                    <a href="project_products.php?project_id=<?php echo $row['ProjectID']; ?>" class="btn btn-link">Products</a>
                    <a href="project_labour.php?project_id=<?php echo $row['ProjectID']; ?>" class="btn btn-link">Labour</a>
                    <form method="post" action="projects.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ProjectID" value="<?php echo $row['ProjectID']; ?>">
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
