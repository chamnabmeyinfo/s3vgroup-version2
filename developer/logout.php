<?php
/**
 * Developer Logout
 */

// Use developer session
if (session_status() === PHP_SESSION_NONE) {
    session_name('developer_session');
    session_start();
}

// Destroy developer session
unset($_SESSION['developer_logged_in']);
unset($_SESSION['developer_username']);
unset($_SESSION['developer_name']);
unset($_SESSION['developer_login_time']);

// Redirect to login
header('Location: ' . url('developer/login.php'));
exit;

