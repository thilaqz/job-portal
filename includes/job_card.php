<?php
/**
 * includes/job_card.php
 * Shows one job as a Bootstrap card. Expects $job (array) to be set.
 * Used on the home page and the job listing page.
 */
?>
<div class="col-md-6 col-lg-4">
    <div class="card job-card h-100">
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-primary-subtle text-primary"><?= e($job['category_name']) ?></span>
                <span class="badge bg-info-subtle text-info-emphasis"><?= e($job['job_type']) ?></span>
            </div>
            <h5 class="card-title mb-1">
                <a href="<?= BASE_URL ?>/jobs/details.php?id=<?= (int) $job['id'] ?>" class="stretched-link text-decoration-none text-dark"><?= e($job['title']) ?></a>
            </h5>
            <p class="text-muted small mb-2"><i class="bi bi-building"></i> <?= e($job['company']) ?></p>
            <ul class="list-unstyled small text-muted mb-3">
                <li><i class="bi bi-geo-alt"></i> <?= e($job['region_name']) ?></li>
                <li><i class="bi bi-cash"></i> <?= e($job['salary']) ?></li>
                <li><i class="bi bi-calendar-event"></i> Apply by <?= e(date('d M Y', strtotime($job['deadline']))) ?></li>
            </ul>
            <div class="mt-auto d-flex justify-content-between align-items-center">
                <small class="text-muted">Posted <?= e(date('d M Y', strtotime($job['created_at']))) ?></small>
                <span class="btn btn-sm btn-outline-primary">View Details</span>
            </div>
        </div>
    </div>
</div>
