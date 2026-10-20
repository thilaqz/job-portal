<?php
/**
 * index.php - Home page
 * Hero search, latest 6 approved jobs and category list.
 */
require_once __DIR__ . '/config/db.php';

$page_title = 'Home';
$categories = get_categories($pdo);
$regions    = get_regions($pdo);

// Latest 6 approved jobs
$latest_jobs = $pdo->query(
    'SELECT j.*, c.name AS category_name, r.name AS region_name,
            COALESCE(u.company_name, u.name) AS company
     FROM jobs j
     JOIN categories c ON c.id = j.category_id
     JOIN regions r    ON r.id = j.region_id
     JOIN users u      ON u.id = j.employer_id
     WHERE j.status = 1
     ORDER BY j.created_at DESC, j.id DESC
     LIMIT 6'
)->fetchAll();

// Categories with number of approved jobs in each
$cat_counts = $pdo->query(
    'SELECT c.id, c.name, COUNT(j.id) AS total
     FROM categories c
     LEFT JOIN jobs j ON j.category_id = c.id AND j.status = 1
     GROUP BY c.id, c.name
     ORDER BY c.name'
)->fetchAll();

// Small stats for the hero
$total_jobs      = (int) $pdo->query('SELECT COUNT(*) FROM jobs WHERE status = 1')->fetchColumn();
$total_employers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<!-- ===== Hero with search ===== -->
<section class="hero text-white">
    <div class="container py-5">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9">
                <h1 class="display-5 fw-bold">Find Your Dream Job Today</h1>
                <p class="lead mb-4"><?= $total_jobs ?> live jobs from <?= $total_employers ?> employers. Search by keyword, location and category.</p>

                <form action="<?= BASE_URL ?>/jobs/list.php" method="get" class="search-box bg-white p-3 rounded-3 shadow">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="keyword" class="form-control form-control-lg" placeholder="Job title or keyword">
                        </div>
                        <div class="col-md-3">
                            <select name="region" class="form-select form-select-lg">
                                <option value="">All Locations</option>
                                <?php foreach ($regions as $r): ?>
                                    <option value="<?= (int) $r['id'] ?>"><?= e($r['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-lg">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-search"></i> Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ===== Latest jobs ===== -->
<section class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h3 section-title mb-0">Latest Jobs</h2>
        <a href="<?= BASE_URL ?>/jobs/list.php" class="btn btn-outline-primary btn-sm">View All Jobs</a>
    </div>
    <div class="row g-4">
        <?php if (empty($latest_jobs)): ?>
            <div class="col-12"><div class="alert alert-info">No jobs posted yet. Please check back later.</div></div>
        <?php endif; ?>
        <?php foreach ($latest_jobs as $job): ?>
            <?php include __DIR__ . '/includes/job_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== Categories ===== -->
<section class="container mt-5">
    <h2 class="h3 section-title mb-3">Browse by Category</h2>
    <div class="row g-3">
        <?php foreach ($cat_counts as $c): ?>
            <div class="col-6 col-md-4 col-lg">
                <a href="<?= BASE_URL ?>/jobs/list.php?category=<?= (int) $c['id'] ?>" class="card category-card text-center h-100 text-decoration-none">
                    <div class="card-body">
                        <i class="bi bi-grid-3x3-gap-fill fs-2 text-primary"></i>
                        <h6 class="mt-2 mb-1 text-dark"><?= e($c['name']) ?></h6>
                        <small class="text-muted"><?= (int) $c['total'] ?> job(s)</small>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== Call to action ===== -->
<section class="container mt-5">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card cta-card h-100 border-0 bg-primary-subtle">
                <div class="card-body p-4">
                    <h4><i class="bi bi-person-workspace"></i> Looking for a job?</h4>
                    <p>Create a free account, upload your resume and apply to jobs in one click.</p>
                    <a href="<?= BASE_URL ?>/auth/register.php?role=seeker" class="btn btn-primary">Register as Job Seeker</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card cta-card h-100 border-0 bg-info-subtle">
                <div class="card-body p-4">
                    <h4><i class="bi bi-building-add"></i> Hiring?</h4>
                    <p>Post jobs, review applicants and shortlist the best candidates.</p>
                    <a href="<?= BASE_URL ?>/auth/register.php?role=employer" class="btn btn-outline-primary">Register as Employer</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
