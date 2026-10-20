-- ============================================================
--  Online Job Portal - database.sql
--  Import this file in phpMyAdmin (Import tab) or run:
--      mysql -u root < database.sql
--  It creates the database "job_portal_db", all tables and sample data.
-- ============================================================

DROP DATABASE IF EXISTS job_portal_db;
CREATE DATABASE job_portal_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE job_portal_db;

-- ------------------------------------------------------------
-- 1. admins : separate table for site administrators
-- ------------------------------------------------------------
CREATE TABLE admins (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,              -- password_hash()
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. users : job seekers and employers
-- ------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('seeker','employer') NOT NULL,
    company_name  VARCHAR(150) DEFAULT NULL,        -- employers only
    phone         VARCHAR(20)  DEFAULT NULL,
    address       VARCHAR(255) DEFAULT NULL,
    skills        TEXT         DEFAULT NULL,        -- seekers only
    photo         VARCHAR(255) DEFAULT NULL,        -- file name in uploads/profile/
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. categories : job categories (IT, Marketing, ...)
-- ------------------------------------------------------------
CREATE TABLE categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. regions : job locations
-- ------------------------------------------------------------
CREATE TABLE regions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. jobs : posted by employers
--    status: 0 = pending, 1 = approved, 2 = rejected
-- ------------------------------------------------------------
CREATE TABLE jobs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    employer_id   INT NOT NULL,
    category_id   INT NOT NULL,
    region_id     INT NOT NULL,
    title         VARCHAR(150) NOT NULL,
    job_type      ENUM('Full-time','Part-time','Internship','Remote') NOT NULL,
    salary        VARCHAR(100) NOT NULL,
    vacancies     INT NOT NULL DEFAULT 1,
    experience    VARCHAR(50)  NOT NULL,
    deadline      DATE NOT NULL,
    description   TEXT NOT NULL,
    requirements  TEXT NOT NULL,
    status        TINYINT NOT NULL DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_jobs_employer FOREIGN KEY (employer_id) REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_jobs_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_jobs_region   FOREIGN KEY (region_id)   REFERENCES regions(id)    ON DELETE CASCADE,
    INDEX idx_jobs_status (status)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. resumes : one current resume (PDF) per job seeker
-- ------------------------------------------------------------
CREATE TABLE resumes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL UNIQUE,
    file_name      VARCHAR(255) NOT NULL,           -- safe generated name in uploads/resumes/
    original_name  VARCHAR(255) NOT NULL,           -- name of the file on the user's PC
    uploaded_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_resumes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. applications : job seeker applies to a job
--    status: 0 = pending, 1 = shortlisted, 2 = rejected, 3 = withdrawn
--    UNIQUE(user_id, job_id) = one application per job per user
-- ------------------------------------------------------------
CREATE TABLE applications (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    job_id        INT NOT NULL,
    resume_file   VARCHAR(255) NOT NULL,            -- copy of resume file name at time of applying
    cover_letter  TEXT NOT NULL,
    status        TINYINT NOT NULL DEFAULT 0,
    applied_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_job (user_id, job_id),
    CONSTRAINT fk_app_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_app_job  FOREIGN KEY (job_id)  REFERENCES jobs(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. saved_jobs : bookmarks
-- ------------------------------------------------------------
CREATE TABLE saved_jobs (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id   INT NOT NULL,
    job_id    INT NOT NULL,
    saved_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_saved (user_id, job_id),
    CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_job  FOREIGN KEY (job_id)  REFERENCES jobs(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
--  SAMPLE DATA
--  Passwords (hashed with PHP password_hash):
--    admin@mail.com      / admin123
--    employer1@mail.com  / employer123   employer2@mail.com / employer123
--    seeker1@mail.com    / seeker123     seeker2@mail.com   / seeker123
-- ============================================================

INSERT INTO admins (id, name, email, password) VALUES
(1, 'Site Admin', 'admin@mail.com', '$2y$10$oFhqr7PaBCPRstmuZl/BieCCAE7hAI.sy3stNjYWqah19aqnLGdGm');

INSERT INTO categories (id, name) VALUES
(1, 'IT & Software'),
(2, 'Marketing & Sales'),
(3, 'Finance & Accounting'),
(4, 'Design & Creative'),
(5, 'Education & Training');

INSERT INTO regions (id, name) VALUES
(1, 'Chennai'),
(2, 'Bangalore'),
(3, 'Mumbai'),
(4, 'Delhi'),
(5, 'Hyderabad');

INSERT INTO users (id, name, email, password, role, company_name, phone, address, skills) VALUES
(1, 'Arun Mehta',  'employer1@mail.com', '$2y$10$JAVN02a3.OcyBKWsy2UxT.s9OTg94TjG0PEhH9mMN6W9Le40s4P9W', 'employer', 'TechSoft Solutions',   '9876500001', 'OMR, Chennai', NULL),
(2, 'Neha Kapoor', 'employer2@mail.com', '$2y$10$JAVN02a3.OcyBKWsy2UxT.s9OTg94TjG0PEhH9mMN6W9Le40s4P9W', 'employer', 'BrightFuture Pvt Ltd', '9876500002', 'Andheri, Mumbai', NULL),
(3, 'Rahul Kumar', 'seeker1@mail.com',   '$2y$10$K.7Nq/Y3p03.tddAt4tk0OMDFw6g311w0wfSydqZviJM2LDDGaSO2', 'seeker',   NULL, '9876500003', 'Anna Nagar, Chennai', 'PHP, MySQL, HTML, CSS, JavaScript'),
(4, 'Priya Sharma','seeker2@mail.com',   '$2y$10$K.7Nq/Y3p03.tddAt4tk0OMDFw6g311w0wfSydqZviJM2LDDGaSO2', 'seeker',   NULL, '9876500004', 'Koramangala, Bangalore', 'Digital Marketing, SEO, Canva');

-- 8 approved jobs (deadline = 30-60 days from the day you import)
INSERT INTO jobs (id, employer_id, category_id, region_id, title, job_type, salary, vacancies, experience, deadline, description, requirements, status) VALUES
(1, 1, 1, 1, 'Junior PHP Developer', 'Full-time', 'INR 3 - 4.5 LPA', 3, '0-1 years', DATE_ADD(CURDATE(), INTERVAL 30 DAY),
 'We are looking for a passionate Junior PHP Developer to build and maintain web applications using PHP and MySQL. You will work with senior developers on real client projects.',
 'Good knowledge of PHP and MySQL\nBasic HTML, CSS and JavaScript\nUnderstanding of OOP concepts\nGood communication skills', 1),
(2, 1, 1, 2, 'Frontend Developer (React)', 'Full-time', 'INR 6 - 9 LPA', 2, '2-4 years', DATE_ADD(CURDATE(), INTERVAL 45 DAY),
 'Join our product team to create fast, responsive user interfaces for our SaaS platform used by thousands of customers.',
 'Strong JavaScript (ES6+)\nExperience with React\nREST API integration\nGit version control', 1),
(3, 1, 1, 5, 'Software Testing Intern', 'Internship', 'INR 10,000 / month', 5, 'Fresher', DATE_ADD(CURDATE(), INTERVAL 20 DAY),
 'A 6-month internship to learn manual and automation testing of web and mobile applications.',
 'Final year or recently graduated students\nBasic knowledge of SDLC\nEager to learn Selenium', 1),
(4, 1, 1, 2, 'Python Backend Developer', 'Remote', 'INR 8 - 12 LPA', 1, '3-5 years', DATE_ADD(CURDATE(), INTERVAL 60 DAY),
 'Work from anywhere and build scalable APIs and microservices for our cloud products.',
 'Python with Django or Flask\nPostgreSQL / MySQL\nDocker basics\nGood problem-solving skills', 1),
(5, 2, 2, 3, 'Digital Marketing Executive', 'Full-time', 'INR 3 - 5 LPA', 2, '1-2 years', DATE_ADD(CURDATE(), INTERVAL 30 DAY),
 'Plan and run online marketing campaigns across Google, Facebook and Instagram for our education brand.',
 'Knowledge of SEO and SEM\nSocial media marketing\nGoogle Analytics\nCreative thinking', 1),
(6, 2, 3, 4, 'Accounts Assistant', 'Part-time', 'INR 15,000 / month', 1, '0-2 years', DATE_ADD(CURDATE(), INTERVAL 25 DAY),
 'Assist the finance team with daily bookkeeping, invoices and GST filing. Flexible 4 hours per day.',
 'B.Com or equivalent\nTally ERP knowledge\nMS Excel', 1),
(7, 2, 4, 3, 'Graphic Designer', 'Full-time', 'INR 3.5 - 5 LPA', 1, '1-3 years', DATE_ADD(CURDATE(), INTERVAL 40 DAY),
 'Create posters, social media creatives, brochures and branding material for our clients.',
 'Adobe Photoshop and Illustrator\nCanva / Figma\nStrong portfolio', 1),
(8, 2, 5, 1, 'Online Maths Tutor', 'Remote', 'INR 500 / hour', 4, '1+ years', DATE_ADD(CURDATE(), INTERVAL 35 DAY),
 'Teach mathematics to school students (Class 6-12) through live online classes in the evenings.',
 'Graduate in Mathematics or related field\nGood spoken English\nLaptop with stable internet', 1);

-- One sample saved job for seeker 1
INSERT INTO saved_jobs (user_id, job_id) VALUES (3, 1);
