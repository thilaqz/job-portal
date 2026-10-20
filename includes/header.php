<?php
/**
 * includes/header.php
 * Top of every page: <head>, Bootstrap CSS, navbar and flash message.
 * Set $page_title before including this file.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? 'Online Job Portal') ?> | JobPortal</title>
    <!-- Bootstrap 5 + Bootstrap Icons from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<?php include __DIR__ . '/navbar.php'; ?>

<main class="flex-grow-1">
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="container mt-3"><?php show_flash(); ?></div>
<?php endif; ?>
