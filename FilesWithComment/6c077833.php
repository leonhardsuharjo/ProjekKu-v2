<?php
// ============================================================
// db.php
// Purpose : Establishes a MySQLi connection to the database.
// This file is included (require_once) at the top of every
// other PHP page that needs database access.
// ============================================================

// Create a new MySQLi connection object.
// Arguments: hostname, MySQL username, MySQL password, database name.
$conn = new mysqli('localhost', 'root', '', 'enterprise_manager');

// Check whether the connection attempt produced an error.
// $conn->connect_error is NULL on success, or a string message on failure.
if ($conn->connect_error) {
    // Stop execution immediately and display the connection error.
    // die() terminates the script so no further code runs without a DB.
    die('Connection failed: ' . $conn->connect_error);
}
?>
