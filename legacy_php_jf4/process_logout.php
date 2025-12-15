<?php
// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear user cookies
setcookie("user_email", "", time() - 3600, "/");
setcookie("user_role", "", time() - 3600, "/");
setcookie("user_name", "", time() - 3600, "/");
setcookie("firstname", "", time() - 3600, "/");
setcookie("lastname", "", time() - 3600, "/");

// Redirect to login page
header("Location: login.php");
exit();
?>