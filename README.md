# Online Job Portal (PHP + MySQL Mini Project)

A beginner-friendly job portal built with **plain PHP (procedural + PDO)**, **MySQL**, **HTML**, **CSS** and **Bootstrap 5 (CDN)**.
No frameworks, no Composer. It runs on **XAMPP** or **WAMP**.

Roles: **Job Seeker**, **Employer**, **Admin**.

---

## 1. Requirements

- XAMPP (or WAMP) with **PHP 7.4 or newer** (PHP 8.x recommended) and **MySQL / MariaDB**
- A web browser
- An internet connection (Bootstrap and Bootstrap Icons load from a CDN)

---

## 2. Setup with XAMPP (step by step)

1. Install XAMPP from https://www.apachefriends.org and open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache** and **MySQL**.
3. Unzip `job-portal.zip` and copy the **`job-portal`** folder into the `htdocs` folder:
   - Windows: `C:\xampp\htdocs\job-portal`
   - Linux: `/opt/lampp/htdocs/job-portal`
   - macOS: `/Applications/XAMPP/htdocs/job-portal`
   - (WAMP: `C:\wamp64\www\job-portal`)
4. Import the database (see section 3).
5. Open **http://localhost/job-portal** in your browser.

> **Linux/macOS only:** let Apache write uploads:
> `chmod -R 777 /opt/lampp/htdocs/job-portal/uploads`

---

## 3. Import the database

1. Open **http://localhost/phpmyadmin**.
2. Click the **Import** tab at the top. (You do **not** need to create the database first.)
3. Click **Choose File** and select `job-portal/database.sql`.
4. Click **Import** (or **Go**) at the bottom.

The script creates the database **`job_portal_db`** with all 8 tables and sample data.

Command line alternative:

```bash
mysql -u root -p < database.sql
```

> Importing again **deletes and recreates** the database (`DROP DATABASE IF EXISTS`), which resets all data.

---

## 4. Database configuration

Settings are at the top of **`config/db.php`**:

| Setting    | Default value     | Notes                                          |
|------------|-------------------|------------------------------------------------|
| `DB_HOST`  | `localhost`       |                                                |
| `DB_NAME`  | `job_portal_db`   | Created by `database.sql`                      |
| `DB_USER`  | `root`            | XAMPP default                                  |
| `DB_PASS`  | *(empty)*         | XAMPP default. WAMP is also empty by default   |
| `BASE_URL` | `/job-portal`     | Change if you rename the folder                |

If MySQL runs on another port (e.g. 3307), set `DB_HOST` to `127.0.0.1;port=3307`.

---

## 5. Run URLs

| Page            | URL                                         |
|-----------------|---------------------------------------------|
| Website (home)  | http://localhost/job-portal                 |
| User login      | http://localhost/job-portal/auth/login.php  |
| Admin login     | http://localhost/job-portal/admin           |

---

## 6. Login credentials (sample data)

| Role        | Email                | Password      |
|-------------|----------------------|---------------|
| Admin       | admin@mail.com       | admin123      |
| Employer 1  | employer1@mail.com   | employer123   |
| Employer 2  | employer2@mail.com   | employer123   |
| Job Seeker 1| seeker1@mail.com     | seeker123     |
| Job Seeker 2| seeker2@mail.com     | seeker123     |

Employer 1 = *TechSoft Solutions*, Employer 2 = *BrightFuture Pvt Ltd*.
The sample job seekers have **no resume yet**. Log in, go to **My Resume** and upload any PDF before applying.

---

## 7. Features

**Public**
- Home page: hero search (keyword + location + category), latest 6 approved jobs, categories with job counts.
- Job listing with keyword / category / location / job type filters and pagination (6 per page).
- Job details with **Apply Now** (asks you to log in if you are a visitor) and **Save Job**.
- About and Contact pages.

**Auth**
- Register as Job Seeker or Employer, Login, Logout.
- Passwords stored with `password_hash()` and checked with `password_verify()`.
- Session-based access. Every dashboard checks the user's role.

**Job Seeker**
- Dashboard with stats, profile edit (name, phone, address, skills, optional photo), change password.
- Resume upload: PDF only, max 2 MB, saved under a random file name.
- Apply with resume + cover letter. Only one application per job (enforced by a UNIQUE key in the DB).
- My Applications with status (Pending / Shortlisted / Rejected / Withdrawn) and **Withdraw**. A withdrawn job can be re-applied.
- Save / unsave jobs and a Saved Jobs page.

**Employer**
- Dashboard: posted jobs, pending approval, live jobs, total applicants.
- Post, edit and delete **own** jobs. New and edited jobs become *Pending* until the admin approves them.
- View applicants per job, download resumes, mark **Shortlisted** / **Rejected**.

**Admin** (separate `admins` table and login)
- Dashboard: seekers, employers, jobs, applications, pending jobs.
- Approve / Reject / Delete jobs (pending jobs listed first, highlighted).
- View and delete job seekers and employers.
- Add / edit / delete categories and locations (regions).

---

## 8. Folder structure

```
job-portal/
├── config/db.php            PDO connection, session_start(), helper functions
├── includes/
│   ├── header.php           <head>, CSS, navbar, flash message
│   ├── footer.php           footer + Bootstrap JS
│   ├── navbar.php           menu that changes by role
│   ├── job_card.php         one job card (home + listing)
│   └── job_form.php         shared form for post/edit job
├── auth/                    login.php, register.php, logout.php
├── jobs/                    list, details, post, edit, delete, apply, save
├── seeker/                  dashboard, profile, applications, saved, upload_resume
├── employer/                dashboard, my_jobs, applicants
├── admin/                   index, login, dashboard, jobs, users, categories, regions
├── assets/css/style.css     blue/white theme
├── assets/js/main.js        small UI helpers
├── uploads/resumes/         uploaded PDFs (direct access blocked by .htaccess)
├── uploads/profile/         profile photos
├── index.php, about.php, contact.php
├── database.sql
└── README.md
```

---

## 9. Database design

| Table          | Purpose                                   | Key points |
|----------------|-------------------------------------------|------------|
| `admins`       | Administrators                            | email UNIQUE, hashed password |
| `users`        | Job seekers and employers                 | `role ENUM('seeker','employer')`, email UNIQUE |
| `categories`   | Job categories                            | name UNIQUE |
| `regions`      | Job locations                             | name UNIQUE |
| `jobs`         | Job posts                                 | `status`: 0 pending, 1 approved, 2 rejected. FKs to users, categories, regions |
| `applications` | Seeker applies to a job                   | `status`: 0 pending, 1 shortlisted, 2 rejected, 3 withdrawn. `UNIQUE(user_id, job_id)` |
| `saved_jobs`   | Bookmarked jobs                           | `UNIQUE(user_id, job_id)` |
| `resumes`      | Current resume of each seeker             | `user_id` UNIQUE |

All foreign keys use `ON DELETE CASCADE`. For example, deleting an employer also deletes their jobs and the applications for those jobs.
Every table has a `created_at` / `applied_at` / `saved_at` timestamp. `users`, `jobs` and `applications` also have `updated_at`.

---

## 10. Security measures (good viva points)

1. **SQL injection:** every query that uses user input is a PDO prepared statement with `?` placeholders.
2. **XSS:** every value printed in HTML goes through `e()` = `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
3. **Passwords:** `password_hash()` (bcrypt) and `password_verify()`. Plain passwords are never stored.
4. **CSRF:** each POST form has a hidden random token (`csrf_field()`), checked with `verify_csrf()` using `hash_equals()`.
5. **Access control:** `require_role('seeker')`, `require_role('employer')`, `require_admin()` at the top of protected pages.
6. **Ownership checks:** queries include `AND employer_id = ?` or `AND user_id = ?`, so users cannot edit, delete or view other users' data.
7. **File uploads:** extension check, real MIME type check with `finfo`, size limit, PDF signature check (`%PDF`), random new file name.
   Resumes cannot be opened by URL. They are sent through PHP only after a permission check.
8. **Session fixation:** `session_regenerate_id(true)` after login.
9. **Server-side validation** of every form, with errors shown as Bootstrap alerts.

---

## 11. How to test the full flow

1. Log in as **employer1@mail.com**, post a new job. It shows as *Pending*.
2. Open **/admin**, log in as **admin@mail.com**, go to **Jobs** and approve it.
3. Log in as **seeker1@mail.com**, upload a PDF in **My Resume**, open the job and **Apply Now**.
4. Log in again as the employer, then **My Jobs → Applicants**. Download the resume and **Shortlist** the applicant.
5. Log in as the seeker. **My Applications** now shows *Shortlisted*.

---

## 12. Troubleshooting

| Problem | Fix |
|---------|-----|
| "Database connection failed" | Start MySQL in XAMPP, import `database.sql`, check `config/db.php` |
| Page not found | Folder must be `htdocs/job-portal`. If renamed, change `BASE_URL` |
| Upload fails | Check `upload_max_filesize` (≥ 2M) in `php.ini`. On Linux/macOS, make `uploads/` writable |
| "Invalid or expired form token" | The session expired. Refresh the page and submit again |
| Styles missing | Bootstrap loads from a CDN, so an internet connection is needed |
| Sample jobs disappear after a while | Their deadlines are set 20–60 days after the import date. Re-import or edit the deadlines |
