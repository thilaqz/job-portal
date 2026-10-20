<?php
/**
 * includes/job_form.php
 * The job form fields, shared by jobs/post.php and jobs/edit.php.
 * Expects: $form (array of current values), $categories, $regions, $submit_label
 */
?>
<form method="post" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Job Title *</label>
            <input type="text" name="title" class="form-control" maxlength="150" required value="<?= e($form['title']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Job Type *</label>
            <select name="job_type" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach (job_types() as $t): ?>
                    <option value="<?= e($t) ?>" <?= $form['job_type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Category *</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $form['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Location *</label>
            <select name="region_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($regions as $r): ?>
                    <option value="<?= (int) $r['id'] ?>" <?= (int) $form['region_id'] === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Salary *</label>
            <input type="text" name="salary" class="form-control" maxlength="100" placeholder="e.g. INR 4-6 LPA" required value="<?= e($form['salary']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Vacancies *</label>
            <input type="number" name="vacancies" class="form-control" min="1" max="1000" required value="<?= e($form['vacancies']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Experience *</label>
            <input type="text" name="experience" class="form-control" maxlength="50" placeholder="e.g. 0-2 years" required value="<?= e($form['experience']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Deadline *</label>
            <input type="date" name="deadline" class="form-control" min="<?= date('Y-m-d') ?>" required value="<?= e($form['deadline']) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Job Description *</label>
            <textarea name="description" class="form-control" rows="6" required><?= e($form['description']) ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Requirements * <small class="text-muted">(one per line)</small></label>
            <textarea name="requirements" class="form-control" rows="5" required><?= e($form['requirements']) ?></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> <?= e($submit_label) ?></button>
            <a href="<?= BASE_URL ?>/employer/my_jobs.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</form>
