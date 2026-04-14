<?php
// ============================================================
// nav.php
// Purpose : Renders the top navigation bar shown on every
//           authenticated page of the application.
// Included via require_once inside each page's HTML <body>.
// ============================================================
?>

<!-- Outer <nav> element — styled by the "nav" rule in style.css -->
<nav>
    <!-- Brand / application name displayed on the far left -->
    <span class="nav-brand">Enterprise Manager</span>

    <!-- Navigation link to the dashboard home page -->
    <a href="index.php">Home</a>

    <!-- Navigation link to the Customers management page -->
    <a href="customers.php">Customers</a>

    <!-- Navigation link to the Suppliers management page -->
    <a href="suppliers.php">Suppliers</a>

    <!-- Navigation link to the Products management page -->
    <a href="products.php">Products</a>

    <!-- Navigation link to the Materials management page -->
    <a href="materials.php">Materials</a>

    <!-- Navigation link to the Job Roles management page -->
    <a href="jobroles.php">Job Roles</a>

    <!-- Navigation link to the Projects management page -->
    <a href="projects.php">Projects</a>

    <!-- Navigation link to the Insight (profitability report) page -->
    <a href="insight.php">Insight</a>

    <!-- Right-aligned area: shows the logged-in user's name and a logout link -->
    <span class="nav-user">
        <!-- Display the session-stored name of the currently logged-in user -->
        <?php echo htmlspecialchars($_SESSION['name']); ?> |
        <!-- Logout link that triggers logout.php to destroy the session -->
        <a href="logout.php">Logout</a>
    </span>
</nav>
