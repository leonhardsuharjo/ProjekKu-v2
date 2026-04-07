<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once "db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home - Enterprise Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "nav.php"; ?>
<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
    <div class="home-grid">
        <div class="home-card">
            <a href="customers.php">Customers</a>
            <p>Manage customer records</p>
        </div>
        <div class="home-card">
            <a href="suppliers.php">Suppliers</a>
            <p>Manage supplier records</p>
        </div>
        <div class="home-card">
            <a href="products.php">Products</a>
            <p>Manage products and pricing</p>
        </div>
        <div class="home-card">
            <a href="materials.php">Materials</a>
            <p>Manage raw materials</p>
        </div>
        <div class="home-card">
            <a href="jobroles.php">Job Roles</a>
            <p>Manage job types and wages</p>
        </div>
        <div class="home-card">
            <a href="projects.php">Projects</a>
            <p>Manage project records</p>
        </div>
        <div class="home-card">
            <a href="insight.php">Insight</a>
            <p>View profitability reports</p>
        </div>
    </div>
</div>
</body>
</html>
