<?php
require_once __DIR__ . '/bootstrap/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear customer session
unset($_SESSION['customer_id']);
unset($_SESSION['customer_email']);
unset($_SESSION['customer_name']);

// Security: fixed redirect (never use HTTP_REFERER - open redirect risk)
header('Location: ' . url('index.php'));
exit;

