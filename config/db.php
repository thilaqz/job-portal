<?php
/**
 * config/db.php
 * ------------------------------------------------------------
 * 1. Database settings + PDO connection
 * 2. Session start
 * 3. Small helper functions used by every page
 *
 * Every page includes this file first:
 *     require_once __DIR__ . '/../config/db.php';
 */

// ============================================================
// 1. SETTINGS (change these if your setup is different)
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'job_portal_db');
define('DB_USER', 'root');
define('DB_PASS', '');            // XAMPP default MySQL password is empty

// URL path of the project folder inside htdocs (http://localhost/job-portal)
define('BASE_URL', '/job-portal');

// Full path of the project folder on disk (used for file uploads)
define('ROOT_PATH', dirname(__DIR__));
define('RESUME_DIR', ROOT_PATH . '/uploads/resumes/');
define('PHOTO_DIR', ROOT_PATH . '/uploads/profile/');

define('JOBS_PER_PAGE', 6);

// ============================================================
// 2. SESSION
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// 3. DATABASE CONNECTION (PDO)
// ============================================================
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows as arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statements
        ]
    );
} catch (PDOException $ex) {
    die('<h3 style="font-family:sans-serif;color:#b00">Database connection failed.</h3>'
        . '<p style="font-family:sans-serif">Check that MySQL is running in XAMPP and that '
        . 'the database <b>job_portal_db</b> was imported. Settings are in config/db.php.</p>');
}

// ============================================================
// 4. HELPER FUNCTIONS
// ============================================================

/** Escape output for HTML (prevents XSS). Use on EVERY value you print. */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Redirect to a page inside the project, e.g. redirect('/index.php') */
function redirect($path)
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/** Only allow "return to" paths that are local to this project. */
function safe_path($path, $default = '/index.php')
{
    $path = (string) $path;
    if (preg_match('#^/[A-Za-z0-9_\-/\.\?=&%]*$#', $path) && strpos($path, '//') === false) {
        return $path;
    }
    return $default;
}

// ---------- Flash messages (shown once on the next page) ----------
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible fade show" role="alert">'
            . e($f['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

/** Print a list of validation errors as a red Bootstrap alert. */
function show_errors(array $errors)
{
    if (empty($errors)) {
        return;
    }
    echo '<div class="alert alert-danger"><ul class="mb-0">';
    foreach ($errors as $err) {
        echo '<li>' . e($err) . '</li>';
    }
    echo '</ul></div>';
}

// ---------- CSRF protection ----------
/** Create (once per session) and return the CSRF token. */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to put inside every POST form. */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Call at the start of every POST handler. Stops the script if token is wrong. */
function verify_csrf()
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        die('Invalid or expired form token (CSRF). Please go back, refresh the page and try again.');
    }
}

// ---------- Login / role checks ----------
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function current_user_id()
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function current_role()
{
    return $_SESSION['user_role'] ?? '';
}

function is_admin()
{
    return isset($_SESSION['admin_id']);
}

/**
 * Protect a page. Example: require_role('seeker');
 * Not logged in -> login page.  Wrong role -> home page.
 */
function require_role($role)
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please login to continue.');
        $here = substr($_SERVER['REQUEST_URI'] ?? '', strlen(BASE_URL));
        redirect('/auth/login.php?redirect=' . urlencode(safe_path($here)));
    }
    if (current_role() !== $role) {
        set_flash('danger', 'Access denied. This page is only for ' . ($role === 'seeker' ? 'job seekers' : 'employers') . '.');
        redirect('/index.php');
    }
}

/** Protect admin pages. */
function require_admin()
{
    if (!is_admin()) {
        set_flash('warning', 'Please login as admin.');
        redirect('/admin/login.php');
    }
}

// ---------- Lists used in forms ----------
function job_types()
{
    return ['Full-time', 'Part-time', 'Internship', 'Remote'];
}

function get_categories($pdo)
{
    return $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
}

function get_regions($pdo)
{
    return $pdo->query('SELECT id, name FROM regions ORDER BY name')->fetchAll();
}

// ---------- Status badges ----------
/** Job status: 0 pending, 1 approved, 2 rejected */
function job_status_badge($status)
{
    $map = [0 => ['warning text-dark', 'Pending'], 1 => ['success', 'Approved'], 2 => ['danger', 'Rejected']];
    $b = $map[(int) $status] ?? ['secondary', 'Unknown'];
    return '<span class="badge bg-' . $b[0] . '">' . $b[1] . '</span>';
}

/** Application status: 0 pending, 1 shortlisted, 2 rejected, 3 withdrawn */
function app_status_badge($status)
{
    $map = [
        0 => ['warning text-dark', 'Pending'],
        1 => ['success', 'Shortlisted'],
        2 => ['danger', 'Rejected'],
        3 => ['secondary', 'Withdrawn'],
    ];
    $b = $map[(int) $status] ?? ['secondary', 'Unknown'];
    return '<span class="badge bg-' . $b[0] . '">' . $b[1] . '</span>';
}

// ---------- File upload ----------
/**
 * Safely upload a file.
 *  $file     = $_FILES['field']
 *  $allowed  = ['pdf' => ['application/pdf']]   (extension => allowed MIME types)
 *  $max_size = bytes
 *  $dest_dir = folder to save in
 *  $prefix   = start of the new file name
 * Returns ['ok' => true, 'name' => 'newname.pdf'] or ['ok' => false, 'error' => '...']
 */
function upload_file($file, array $allowed, $max_size, $dest_dir, $prefix)
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['ok' => false, 'error' => 'Invalid upload.'];
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'Please choose a file to upload.'];
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['ok' => false, 'error' => 'File is too large.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'error' => 'Upload failed. Please try again.'];
    }

    // 1. Size check
    if ($file['size'] > $max_size) {
        return ['ok' => false, 'error' => 'File is too large. Maximum size is ' . round($max_size / 1048576, 1) . ' MB.'];
    }

    // 2. Extension check
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, $allowed)) {
        return ['ok' => false, 'error' => 'Invalid file type. Allowed: ' . strtoupper(implode(', ', array_keys($allowed))) . '.'];
    }

    // 3. Real MIME type check (reads the file content, not the browser's claim)
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
    } else {
        $mime = $file['type']; // fallback if fileinfo extension is disabled
    }
    if (!in_array($mime, $allowed[$ext], true)) {
        return ['ok' => false, 'error' => 'File content does not match its extension.'];
    }

    // 4. Extra content checks
    if ($ext === 'pdf') {
        $fh    = fopen($file['tmp_name'], 'rb');
        $magic = fread($fh, 4);
        fclose($fh);
        if ($magic !== '%PDF') {
            return ['ok' => false, 'error' => 'This is not a valid PDF file.'];
        }
    } elseif (@getimagesize($file['tmp_name']) === false) {
        return ['ok' => false, 'error' => 'This is not a valid image.'];
    }

    // 5. Unique, safe new file name (never trust the original name)
    $new_name = $prefix . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!is_dir($dest_dir)) {
        mkdir($dest_dir, 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $dest_dir . $new_name)) {
        return ['ok' => false, 'error' => 'Could not save the file. Check folder permissions.'];
    }
    return ['ok' => true, 'name' => $new_name];
}

/** Send a stored PDF to the browser (used for resume view/download). */
function send_pdf($full_path, $download_name, $inline = false)
{
    if (!is_file($full_path)) {
        set_flash('danger', 'File not found on server.');
        redirect('/index.php');
    }
    $download_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $download_name);
    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $download_name . '"');
    header('Content-Length: ' . filesize($full_path));
    header('X-Content-Type-Options: nosniff');
    readfile($full_path);
    exit;
}

// ---------- Job form validation (used by jobs/post.php and jobs/edit.php) ----------
/**
 * Read and validate job form fields from $_POST.
 * Returns [$data, $errors].
 */
function validate_job_form($pdo, array $src)
{
    $data = [
        'title'        => trim($src['title'] ?? ''),
        'category_id'  => (int) ($src['category_id'] ?? 0),
        'region_id'    => (int) ($src['region_id'] ?? 0),
        'job_type'     => trim($src['job_type'] ?? ''),
        'salary'       => trim($src['salary'] ?? ''),
        'vacancies'    => trim($src['vacancies'] ?? ''),
        'experience'   => trim($src['experience'] ?? ''),
        'deadline'     => trim($src['deadline'] ?? ''),
        'description'  => trim($src['description'] ?? ''),
        'requirements' => trim($src['requirements'] ?? ''),
    ];
    $errors = [];

    if (strlen($data['title']) < 3 || strlen($data['title']) > 150) {
        $errors[] = 'Job title must be 3 to 150 characters.';
    }

    $st = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
    $st->execute([$data['category_id']]);
    if (!$st->fetchColumn()) {
        $errors[] = 'Please select a valid category.';
    }

    $st = $pdo->prepare('SELECT COUNT(*) FROM regions WHERE id = ?');
    $st->execute([$data['region_id']]);
    if (!$st->fetchColumn()) {
        $errors[] = 'Please select a valid location.';
    }

    if (!in_array($data['job_type'], job_types(), true)) {
        $errors[] = 'Please select a valid job type.';
    }
    if ($data['salary'] === '' || strlen($data['salary']) > 100) {
        $errors[] = 'Salary is required (max 100 characters).';
    }
    if (!ctype_digit($data['vacancies']) || (int) $data['vacancies'] < 1 || (int) $data['vacancies'] > 1000) {
        $errors[] = 'Vacancies must be a number between 1 and 1000.';
    }
    if ($data['experience'] === '' || strlen($data['experience']) > 50) {
        $errors[] = 'Experience is required (e.g. "0-2 years", max 50 characters).';
    }

    $d = DateTime::createFromFormat('Y-m-d', $data['deadline']);
    if (!$d || $d->format('Y-m-d') !== $data['deadline']) {
        $errors[] = 'Please enter a valid deadline date.';
    } elseif ($data['deadline'] < date('Y-m-d')) {
        $errors[] = 'Deadline cannot be in the past.';
    }

    if (strlen($data['description']) < 20) {
        $errors[] = 'Description must be at least 20 characters.';
    }
    if (strlen($data['requirements']) < 10) {
        $errors[] = 'Requirements must be at least 10 characters.';
    }

    return [$data, $errors];
}
