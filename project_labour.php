<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once "db.php";

if (!isset($_GET['project_id'])) {
    header("Location: projects.php");
    exit();
}

$project_id = (int)$_GET['project_id'];

$pres = $conn->prepare("SELECT p.*, c.CustomerName FROM project p LEFT JOIN customer c ON p.CustomerID = c.CustomerID WHERE p.ProjectID=?");
$pres->bind_param("i", $project_id);
$pres->execute();
$project = $pres->get_result()->fetch_assoc();

if (!$project) {
    header("Location: projects.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $jid = (int)$_POST['JobRoleID'];
        $workers = (int)$_POST['NumWorkers'];
        $days = (int)$_POST['NumDays'];
        if ($jid > 0 && $workers > 0 && $days > 0) {
            $stmt = $conn->prepare("INSERT INTO projectlabour (ProjectID, JobRoleID, NumWorkers, NumDays) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE NumWorkers=?, NumDays=?");
            $stmt->bind_param("iiiiii", $project_id, $jid, $workers, $days, $workers, $days);
            $stmt->execute();
            $message = "Labour assigned to project.";
        } else {
            $message = "Please fill all fields.";
        }
    } elseif ($action === 'delete') {
        $jid = (int)$_POST['JobRoleID'];
        $stmt = $conn->prepare("DELETE FROM projectlabour WHERE ProjectID=? AND JobRoleID=?");
        $stmt->bind_param("ii", $project_id, $jid);
        $stmt->execute();
        $message = "Labour removed from project.";
    }
}

$linked = $conn->prepare("SELECT pl.*, jr.JobType, jr.WagePerDay FROM projectlabour pl JOIN jobrole jr ON pl.JobRoleID = jr.JobRoleID WHERE pl.ProjectID=?");
$linked->bind_param("i", $project_id);
$linked->execute();
$linked_result = $linked->get_result();

$all_roles = $conn->query("SELECT JobRoleID, JobType, WagePerDay FROM jobrole ORDER BY JobType ASC");
$roles_arr = [];
while ($r = $all_roles->fetch_assoc()) {
    $roles_arr[] = $r;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Project Labour - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Labour for Project: <?php echo htmlspecialchars($project['ProjectName']); ?></h1>
    <p>Customer: <?php echo htmlspecialchars($project['CustomerName'] ?? '-'); ?> &nbsp;|&nbsp; Date: <?php echo $project['ProjectDate']; ?></p>
    <br>
    <p>
        <a href="projects.php" class="btn btn-edit">&larr; Back to Projects</a>
        &nbsp;
        <a href="project_products.php?project_id=<?php echo $project_id; ?>" class="btn btn-link">Manage Products</a>
    </p>
    <br>

    <?php if ($message !== ""): ?>
    <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h2>Assign Labour to Project</h2>
        <form method="post" action="project_labour.php?project_id=<?php echo $project_id; ?>">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Job Role</label>
                <select name="JobRoleID" required>
                    <option value="0">-- Select Job Role --</option>
                    <?php foreach ($roles_arr as $r): ?>
                    <option value="<?php echo $r['JobRoleID']; ?>">
                        <?php echo htmlspecialchars($r['JobType']); ?> (<?php echo number_format($r['WagePerDay'], 2); ?>/day)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Number of Workers</label>
                <input type="number" name="NumWorkers" min="1" required>
            </div>
            <div class="form-group">
                <label>Number of Days</label>
                <input type="number" name="NumDays" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Assign Labour</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Job Type</th>
                <th>Wage/Day</th>
                <th>Workers</th>
                <th>Days</th>
                <th>Total Cost</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $linked_result->fetch_assoc()):
            $total = $row['WagePerDay'] * $row['NumWorkers'] * $row['NumDays'];
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['JobType']); ?></td>
                <td><?php echo number_format($row['WagePerDay'], 2); ?></td>
                <td><?php echo $row['NumWorkers']; ?></td>
                <td><?php echo $row['NumDays']; ?></td>
                <td><?php echo number_format($total, 2); ?></td>
                <td class="action-btns">
                    <form method="post" action="project_labour.php?project_id=<?php echo $project_id; ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="JobRoleID" value="<?php echo $row['JobRoleID']; ?>">
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
