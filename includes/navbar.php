<?php
/**
 * includes/navbar.php
 * Navigation bar. Links change depending on who is logged in.
 * Admin pages set $admin_area = true to show the admin menu.
 */
$admin_area = $admin_area ?? false;

/** Returns "active" if the current page path contains $part */
if (!function_exists('nav_active')) {
function nav_active($part)
{
    return strpos($_SERVER['SCRIPT_NAME'] ?? '', $part) !== false ? ' active' : '';
}
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?><?= $admin_area ? '/admin/dashboard.php' : '/index.php' ?>">
            <i class="bi bi-briefcase-fill"></i> JobPortal<?= $admin_area ? ' <small class="fw-normal">Admin</small>' : '' ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
        <?php if ($admin_area && is_admin()): ?>
            <!-- ===== Admin menu ===== -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link<?= nav_active('/admin/dashboard.php') ?>" href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('/admin/jobs.php') ?>" href="<?= BASE_URL ?>/admin/jobs.php">Jobs</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('/admin/users.php') ?>" href="<?= BASE_URL ?>/admin/users.php">Users</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('/admin/categories.php') ?>" href="<?= BASE_URL ?>/admin/categories.php">Categories</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('/admin/regions.php') ?>" href="<?= BASE_URL ?>/admin/regions.php">Locations</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Site</a></li>
                <li class="nav-item"><span class="nav-link text-white-50"><i class="bi bi-person-badge"></i> <?= e($_SESSION['admin_name']) ?></span></li>
                <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2 mt-1" href="<?= BASE_URL ?>/auth/logout.php?admin=1">Logout</a></li>
            </ul>
        <?php else: ?>
            <!-- ===== Public menu ===== -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link<?= nav_active('/index.php') ?>" href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('/jobs/list.php') ?>" href="<?= BASE_URL ?>/jobs/list.php">Jobs</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('/about.php') ?>" href="<?= BASE_URL ?>/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('/contact.php') ?>" href="<?= BASE_URL ?>/contact.php">Contact</a></li>
            </ul>
            <ul class="navbar-nav">
            <?php if (is_logged_in() && current_role() === 'seeker'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= e($_SESSION['user_name']) ?></a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/seeker/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/seeker/profile.php"><i class="bi bi-person"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/seeker/upload_resume.php"><i class="bi bi-file-earmark-pdf"></i> My Resume</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/seeker/applications.php"><i class="bi bi-send"></i> My Applications</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/seeker/saved.php"><i class="bi bi-bookmark"></i> Saved Jobs</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            <?php elseif (is_logged_in() && current_role() === 'employer'): ?>
                <li class="nav-item"><a class="nav-link<?= nav_active('/jobs/post.php') ?>" href="<?= BASE_URL ?>/jobs/post.php"><i class="bi bi-plus-circle"></i> Post Job</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-building"></i> <?= e($_SESSION['user_name']) ?></a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/employer/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/employer/my_jobs.php"><i class="bi bi-list-task"></i> My Jobs</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link<?= nav_active('/auth/login.php') ?>" href="<?= BASE_URL ?>/auth/login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2 mt-1" href="<?= BASE_URL ?>/auth/register.php">Register</a></li>
            <?php endif; ?>
            </ul>
        <?php endif; ?>
        </div>
    </div>
</nav>
