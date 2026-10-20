<?php
/**
 * contact.php - Static contact page.
 * The form is validated on the server and shows a thank-you message.
 * (Demo only: the message is not stored or emailed.)
 */
require_once __DIR__ . '/config/db.php';
$page_title = 'Contact Us';

$errors  = [];
$success = false;
$form    = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['name']    = trim($_POST['name'] ?? '');
    $form['email']   = trim($_POST['email'] ?? '');
    $form['message'] = trim($_POST['message'] ?? '');

    if ($form['name'] === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if (strlen($form['message']) < 10) {
        $errors[] = 'Message must be at least 10 characters.';
    }
    if (empty($errors)) {
        $success = true;
        $form    = ['name' => '', 'email' => '', 'message' => ''];
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="page-banner text-white">
    <div class="container py-4"><h1 class="h2 mb-0">Contact Us</h1></div>
</div>

<div class="container mt-4">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Get in Touch</h5>
                    <p class="text-muted">Have a question about the portal? We would love to hear from you.</p>
                    <p><i class="bi bi-geo-alt-fill text-primary"></i> 123 Anna Salai, Chennai, Tamil Nadu</p>
                    <p><i class="bi bi-telephone-fill text-primary"></i> +91 98765 43210</p>
                    <p><i class="bi bi-envelope-fill text-primary"></i> support@jobportal.local</p>
                    <p class="mb-0"><i class="bi bi-clock-fill text-primary"></i> Mon - Sat, 9:00 AM - 6:00 PM</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Send a Message</h5>
                    <?php if ($success): ?>
                        <div class="alert alert-success">Thank you! Your message has been received.</div>
                    <?php endif; ?>
                    <?php show_errors($errors); ?>
                    <form method="post" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= e($form['name']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= e($form['email']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" rows="5" class="form-control" required><?= e($form['message']) ?></textarea>
                        </div>
                        <button class="btn btn-primary"><i class="bi bi-send"></i> Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
