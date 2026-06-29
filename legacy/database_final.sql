-- Database untuk Website Rekrutmen dengan Fitur Penilaian SAW
CREATE DATABASE IF NOT EXISTS rekruter_db;
USE rekruter_db;

-- Tabel Users untuk Admin, HRD, dan Applicants
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hrd', 'applicant') NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Job Listings
CREATE TABLE job_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position VARCHAR(100) NOT NULL,
    company VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    salary_range VARCHAR(50),
    employment_type ENUM('full-time', 'part-time', 'contract', 'internship') DEFAULT 'full-time',
    status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Applications (GABUNGAN - mendukung registered user DAN quick apply)
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,  -- NULL untuk quick apply, NOT NULL untuk registered user
    job_id INT NOT NULL,
    -- Data untuk quick apply (jika user_id NULL)
    applicant_name VARCHAR(100) NULL,
    applicant_email VARCHAR(100) NULL,
    applicant_phone VARCHAR(20) NULL,
    applicant_age INT NULL,
    applicant_gender ENUM('Laki-laki', 'Perempuan') DEFAULT NULL,
    work_experience VARCHAR(50) NULL,
    -- Data aplikasi
    cover_letter TEXT,
    resume_path VARCHAR(255),
    status ENUM('pending', 'reviewed', 'accepted', 'rejected', 'interview_scheduled') DEFAULT 'pending',
    application_type ENUM('registered', 'quick_apply') DEFAULT 'registered',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registered_application (user_id, job_id)
);

-- Tabel untuk menyimpan informasi tambahan applicant
CREATE TABLE applicant_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    education TEXT,
    experience TEXT,
    skills TEXT,
    portfolio_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Kriteria untuk Penilaian SAW
CREATE TABLE evaluation_criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    weight DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    type ENUM('benefit', 'cost') DEFAULT 'benefit',
    min_value INT DEFAULT 1,
    max_value INT DEFAULT 5,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Penilaian Aplikasi
CREATE TABLE application_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    criteria_id INT NOT NULL,
    score INT NOT NULL,
    evaluator_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (criteria_id) REFERENCES evaluation_criteria(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_evaluation (application_id, criteria_id, evaluator_id)
);

-- Tabel Hasil Ranking SAW
CREATE TABLE application_rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    job_id INT NOT NULL,
    saw_score DECIMAL(10,6) NOT NULL,
    rank_position INT NOT NULL,
    evaluation_status ENUM('draft', 'final') DEFAULT 'draft',
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_ranking (application_id, job_id)
);

-- Insert data admin default
INSERT INTO users (name, email, password, role) VALUES 
('Administrator', 'admin@clarajob.co.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('HR Manager', 'hr@clarajob.co.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hrd');

-- Insert kriteria penilaian default
INSERT INTO evaluation_criteria (name, description, weight, type, min_value, max_value) VALUES 
('Pendidikan', 'Tingkat pendidikan dan relevansi dengan posisi', 20.00, 'benefit', 1, 5),
('Pengalaman Kerja', 'Lama dan relevansi pengalaman kerja', 25.00, 'benefit', 1, 5),
('Keterampilan Teknis', 'Kemampuan teknis sesuai dengan kebutuhan posisi', 30.00, 'benefit', 1, 5),
('Komunikasi', 'Kemampuan komunikasi dan presentasi', 15.00, 'benefit', 1, 5),
('Kepribadian', 'Kesesuaian kepribadian dengan budaya perusahaan', 10.00, 'benefit', 1, 5);

-- Insert sample job listings
INSERT INTO job_listings (position, company, location, description, requirements, salary_range, employment_type, created_by) VALUES 
('Software Engineer', 'PT. Clarajob', 'Jakarta', 'Kami mencari Software Engineer yang berpengalaman untuk mengembangkan aplikasi web dan mobile yang inovatif.', 'Minimal 2 tahun pengalaman, menguasai PHP, JavaScript, MySQL, Git', 'Rp 8.000.000 - Rp 15.000.000', 'full-time', 1),
('Marketing Specialist', 'PT. Clarajob', 'Jakarta', 'Bergabunglah dengan tim marketing kami untuk mengembangkan strategi pemasaran digital yang efektif.', 'S1 Marketing/Komunikasi, pengalaman 1-2 tahun, menguasai Google Analytics, Social Media Marketing', 'Rp 6.000.000 - Rp 10.000.000', 'full-time', 2),
('Data Analyst', 'PT. Clarajob', 'Jakarta', 'Kami mencari Data Analyst untuk menganalisis data bisnis dan memberikan insights yang actionable.', 'S1 Statistika/Matematika, menguasai Python/R, SQL, Excel, pengalaman 1-3 tahun', 'Rp 7.000.000 - Rp 12.000.000', 'full-time', 1),
('UI/UX Designer', 'PT. Clarajob', 'Jakarta', 'Bergabunglah dengan tim kreatif kami untuk mendesain user experience yang luar biasa.', 'Portfolio yang kuat, menguasai Figma, Adobe Creative Suite, pengalaman 2-4 tahun', 'Rp 6.500.000 - Rp 11.000.000', 'full-time', 2),
('Content Writer', 'PT. Clarajob', 'Jakarta', 'Kami mencari Content Writer yang kreatif untuk membuat konten yang engaging dan informatif.', 'S1 Bahasa/Komunikasi, pengalaman menulis 1-2 tahun, menguasai SEO, Social Media', 'Rp 4.500.000 - Rp 7.500.000', 'full-time', 1);
