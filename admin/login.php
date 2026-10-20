<?php
/**
 * admin/login.php - Separate login for administrators (admins table).
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Admin Login';
$admin_area = true;

if (is_admin()) {
    redirect('/admin/dashboard.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $st = $pdo->prepare('SELECT id, name, password FROM admins WHERE email = ?');
    $st->execute([$email]);
    $admin = $st->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        set_flash('success', 'Welcome, ' . $admin['name'] . '!');
        redirect('/admin/dashboard.php');
    }
    $errors[] = 'Invalid admin email or password.';
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-3"><i class="bi bi-shield-lock display-4 text-primary"></i></div>
                    <h2 class="h4 text-center mb-3">Admin Login</h2>
                    <?php show_errors($errors); ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required autofocus value="<?= e($email) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Login</button>
                    </form>
                    <p class="text-center small mt-3 mb-0"><a href="<?= BASE_URL ?>/index.php">&larr; Back to site</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
