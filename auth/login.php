<?php
/**
 * auth/login.php - Login for Job Seekers and Employers.
 * (Admins use admin/login.php)
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Login';

// Page to go back to after login (e.g. the job the user wanted to apply for)
$return_to = safe_path($_GET['redirect'] ?? ($_POST['redirect'] ?? ''), '');

if (is_logged_in()) {
    redirect(current_role() === 'employer' ? '/employer/dashboard.php' : '/seeker/dashboard.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $errors[] = 'Please enter your email and password.';
    } else {
        $st = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
        $st->execute([$email]);
        $user = $st->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            set_flash('success', 'Welcome back, ' . $user['name'] . '!');
            if ($return_to !== '') {
                redirect($return_to);
            }
            redirect($user['role'] === 'employer' ? '/employer/dashboard.php' : '/seeker/dashboard.php');
        }
        $errors[] = 'Invalid email or password.';
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 text-center mb-1">Login</h2>
                    <p class="text-center text-muted small">Job Seekers &amp; Employers</p>
                    <?php show_errors($errors); ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="redirect" value="<?= e($return_to) ?>">
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
                    <p class="text-center small mt-3 mb-1">New here? <a href="<?= BASE_URL ?>/auth/register.php">Create an account</a></p>
                    <p class="text-center small mb-0"><a href="<?= BASE_URL ?>/admin/login.php" class="text-muted">Admin login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
