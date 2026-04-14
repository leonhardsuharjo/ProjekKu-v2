<?php
// ============================================================
// index.php
// Purpose : Dashboard / home page of the Enterprise Manager.
// Displays a grid of cards, each linking to a management module.
// Only accessible to authenticated (logged-in) users.
// ============================================================

// Start the PHP session so $_SESSION variables are available.
session_start();

// If the user is not logged in (no 'user_id' in session),
// redirect them to the login page and stop execution.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Send the redirect header
    exit();                        // Halt further script execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Set the character encoding to UTF-8 for full unicode support -->
    <meta charset="UTF-8">
    <!-- Make the page responsive on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Page title shown in the browser tab -->
    <title>Dashboard – Enterprise Manager</title>
    <!-- Link to the shared stylesheet for all pages -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Include the shared navigation bar (nav.php outputs the <nav> HTML) -->
<?php require_once 'nav.php'; ?>

<!-- Main content wrapper — constrained width, centred by .container in style.css -->
<div class="container">

    <!-- Page heading -->
    <h1>Dashboard</h1>

    <!-- Responsive card grid — defined by .home-grid in style.css -->
    <div class="home-grid">

        <!-- Card: Customers module -->
        <div class="home-card">
            <!-- Link to the customers management page -->
            <a href="customers.php">Customers</a>
            <!-- Short description shown below the link -->
            <p>Manage customer records</p>
        </div>

        <!-- Card: Suppliers module -->
        <div class="home-card">
            <a href="suppliers.php">Suppliers</a>
            <p>Manage supplier records</p>
        </div>

        <!-- Card: Products module -->
        <div class="home-card">
            <a href="products.php">Products</a>
            <p>Manage products and pricing</p>
        </div>

        <!-- Card: Materials module -->
        <div class="home-card">
            <a href="materials.php">Materials</a>
            <p>Manage raw materials</p>
        </div>

        <!-- Card: Job Roles module -->
        <div class="home-card">
            <a href="jobroles.php">Job Roles</a>
            <p>Manage job types and wages</p>
        </div>

        <!-- Card: Projects module -->
        <div class="home-card">
            <a href="projects.php">Projects</a>
            <p>Manage project records</p>
        </div>

        <!-- Card: Insight / reports module -->
        <div class="home-card">
            <a href="insight.php">Insight</a>
            <p>View profitability reports</p>
        </div>

    </div><!-- /.home-grid -->
</div><!-- /.container -->

</body>
</html>
