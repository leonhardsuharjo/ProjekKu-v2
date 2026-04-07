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