<?php
// ============================================================
// logout.php
// Purpose : Destroys the current user session and redirects
//           back to the login page.
// Called when the user clicks the "Logout" link in the nav bar.
// ============================================================

// Start (or resume) the session so we can access and destroy it.
session_start();

// Erase all session variables and delete the session data on the server.
session_destroy();

// Send an HTTP redirect header pointing the browser to the login page.
header('Location: login.php');

// Stop all further PHP execution after the redirect header is sent.
exit();
?>
