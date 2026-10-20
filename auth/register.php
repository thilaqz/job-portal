<?php
/**
 * auth/register.php - Create a Job Seeker or Employer account.
 */
require_once __DIR__ . '/../config/db.php';
$page_title = 'Register';

// Already logged in? Go to dashboard.
if (is_logged_in()) {
    redirect(current_role() === 'employer' ? '/employer/dashboard.php' : '/seeker/dashboard.php');
}

$errors = [];
$form = [
    'name'         => '',
    'email'        => '',
    'role'         => in_array($_GET['role'] ?? '', ['seeker', 'employer'], true) ? $_GET['role'] : 'seeker',
    'company_name' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $form['name']         = trim($_POST['name'] ?? '');
    $form['email']        = strtolower(trim($_POST['email'] ?? ''));
    $form['role']         = $_POST['role'] ?? '';
    $form['company_name'] = trim($_POST['company_name'] ?? '');
    $password             = $_POST['password'] ?? '';
    $confirm              = $_POST['confirm_password'] ?? '';

    // ---- Validation ----
    if (strlen($form['name']) < 2 || strlen($form['name']) > 100) {
        $errors[] = 'Name must be 2 to 100 characters.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!in_array($form['role'], ['seeker', 'employer'], true)) {
        $errors[] = 'Please choose an account type.';
    }
    if ($form['role'] === 'employer' && $form['company_name'] === '') {
        $errors[] = 'Company name is required for employers.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Email must be unique
    if (empty($errors)) {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$form['email']]);
        if ($st->fetch()) {
            $errors[] = 'This email is already registered. Please login.';
        }
    }

    // ---- Save ----
    if (empty($errors)) {
        $st = $pdo->prepare('INSERT INTO users (name, email, password, role, company_name) VALUES (?, ?, ?, ?, ?)');
        $st->execute([
            $form['name'],
            $form['email'],
            password_hash($password, PASSWORD_DEFAULT),   // never store plain passwords
            $form['role'],
            $form['role'] === 'employer' ? $form['company_name'] : null,
        ]);
        set_flash('success', 'Registration successful! Please login.');
        redirect('/auth/login.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card auth-card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h4 text-center mb-1">Create Account</h2>
                    <p class="text-center text-muted small">Join JobPortal for free</p>
                    <?php show_errors($errors); ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <label class="form-label">I am a</label>
                        <div class="btn-group w-100 mb-3" role="group">
                            <input type="radio" class="btn-check" name="role" id="roleSeeker" value="seeker" <?= $form['role'] === 'seeker' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="roleSeeker"><i class="bi bi-person"></i> Job Seeker</label>
                            <input type="radio" class="btn-check" name="role" id="roleEmployer" value="employer" <?= $form['role'] === 'employer' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="roleEmployer"><i class="bi bi-building"></i> Employer</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= e($form['name']) ?>">
                        </div>
                        <div class="mb-3" id="companyField">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" maxlength="150" value="<?= e($form['company_name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($form['email']) ?>">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" minlength="6" required>
                            </div>
                            <div class="col">
                                <label class="form-label">Confirm</label>
                                <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                            </div>
                        </div>
                        <button class="btn btn-primary w-100">Register</button>
                    </form>
                    <p class="text-center small mt-3 mb-0">Already have an account? <a href="<?= BASE_URL ?>/auth/login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
