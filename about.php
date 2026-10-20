<?php
/**
 * about.php - Static "About Us" page
 */
require_once __DIR__ . '/config/db.php';
$page_title = 'About Us';
include __DIR__ . '/includes/header.php';
?>
<div class="page-banner text-white">
    <div class="container py-4"><h1 class="h2 mb-0">About Us</h1></div>
</div>

<div class="container mt-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-7">
            <h2 class="h4 section-title">Connecting Talent with Opportunity</h2>
            <p>The <strong>Online Job Portal</strong> is a web application where job seekers can search and apply for jobs,
                and employers can post vacancies and manage applicants. An administrator approves every job before it
                goes live, so the listings stay genuine and useful.</p>
            <p>This project was built as a mini project using <strong>PHP (PDO)</strong>, <strong>MySQL</strong>,
                <strong>HTML</strong>, <strong>CSS</strong> and <strong>Bootstrap 5</strong>.</p>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Key Features</h5>
                    <ul class="mb-0">
                        <li>Search jobs by keyword, category, location and type</li>
                        <li>Resume upload and one-click apply</li>
                        <li>Application status tracking</li>
                        <li>Employer job posting and applicant shortlisting</li>
                        <li>Admin approval of jobs and user management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2 text-center">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                <i class="bi bi-person-check fs-1 text-primary"></i>
                <h5 class="mt-2">Job Seekers</h5>
                <p class="small text-muted mb-0">Build a profile, upload a resume, save jobs and track applications.</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                <i class="bi bi-building fs-1 text-primary"></i>
                <h5 class="mt-2">Employers</h5>
                <p class="small text-muted mb-0">Post jobs, view applicants, download resumes and shortlist candidates.</p>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm"><div class="card-body">
                <i class="bi bi-shield-check fs-1 text-primary"></i>
                <h5 class="mt-2">Admin</h5>
                <p class="small text-muted mb-0">Approve jobs, manage users, categories and locations.</p>
            </div></div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
