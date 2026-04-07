<nav>
    <span class="nav-brand">Enterprise Manager</span>
    <a href="index.php">Home</a>
    <a href="customers.php">Customers</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="products.php">Products</a>
    <a href="materials.php">Materials</a>
    <a href="jobroles.php">Job Roles</a>
    <a href="projects.php">Projects</a>
    <a href="insight.php">Insight</a>
    <span class="nav-user">Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?> &nbsp;|&nbsp; <a href="logout.php" style="color:#e74c3c;">Logout</a></span>
</nav>
