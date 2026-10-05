<?php
/**
 * ═══════════════════════════════════════════════════
 * UNIVERSAL CENTRAL DEMO AUTHENTICATION SYSTEM (UCDAS)
 * Project SSO Adapter for QuickSeats (BookMyShow)
 * ═══════════════════════════════════════════════════
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = trim($_GET['role'] ?? 'admin');

if ($role === 'passenger' || $role === 'customer' || $role === 'user') {
    $_SESSION['customer_name'] = 'Demo Passenger';
    $_SESSION['customer_email'] = 'passenger@codecrush.demo';
    header('Location: home.html');
    exit;
}

// Default: Administrator
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'Admin';
$_SESSION['admin_role'] = 'Administrator';

header('Location: admin.html');
exit;
