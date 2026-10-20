<?php
/** admin/index.php - lets http://localhost/job-portal/admin open the admin login. */
require_once __DIR__ . '/../config/db.php';
redirect(is_admin() ? '/admin/dashboard.php' : '/admin/login.php');
