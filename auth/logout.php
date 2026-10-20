<?php
/**
 * auth/logout.php
 *   logout.php          -> logs out job seeker / employer
 *   logout.php?admin=1  -> logs out admin
 */
require_once __DIR__ . '/../config/db.php';

if (isset($_GET['admin'])) {
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
    session_regenerate_id(true);
    set_flash('info', 'Admin logged out.');
    redirect('/admin/login.php');
}

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_role']);
session_regenerate_id(true);
set_flash('info', 'You have been logged out.');
redirect('/auth/login.php');
