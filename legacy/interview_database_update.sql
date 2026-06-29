-- Database Update untuk Fitur Interview Management
-- Jalankan file ini untuk menambahkan tabel interview ke database existing

USE rekruter_db;

-- Tabel Interview Schedules
CREATE TABLE IF NOT EXISTS interview_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    interviewer_id INT NOT NULL,
    interview_type ENUM('phone', 'video', 'in-person', 'online') DEFAULT 'video',
    interview_date DATETIME NOT NULL,
    timezone VARCHAR(50) DEFAULT 'Asia/Jakarta',
    duration_minutes INT DEFAULT 60,
    meeting_link VARCHAR(500) NULL,
    meeting_room VARCHAR(100) NULL,
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    notes TEXT NULL,
    google_calendar_event_id VARCHAR(255) NULL,
    outlook_calendar_event_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (interviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Interview Feedback
CREATE TABLE IF NOT EXISTS interview_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    interview_schedule_id INT NOT NULL,
    interviewer_id INT NOT NULL,
    applicant_id INT NULL, -- NULL untuk quick apply
    -- Penilaian Interview
    communication_score INT CHECK (communication_score >= 1 AND communication_score <= 5),
    technical_score INT CHECK (technical_score >= 1 AND technical_score <= 5),
    problem_solving_score INT CHECK (problem_solving_score >= 1 AND problem_solving_score <= 5),
    cultural_fit_score INT CHECK (cultural_fit_score >= 1 AND cultural_fit_score <= 5),
    overall_score DECIMAL(3,2) GENERATED ALWAYS AS (
        (communication_score + technical_score + problem_solving_score + cultural_fit_score) / 4.0
    ) STORED,
    -- Feedback Detail
    strengths TEXT,
    weaknesses TEXT,
    areas_for_improvement TEXT,
    recommendation ENUM('hire', 'no_hire', 'maybe', 'strong_hire') DEFAULT 'maybe',
    next_steps TEXT,
    additional_notes TEXT,
    -- Status
    feedback_status ENUM('draft', 'submitted', 'reviewed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (interview_schedule_id) REFERENCES interview_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (interviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Interview Questions (Template)
CREATE TABLE IF NOT EXISTS interview_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    question_type ENUM('technical', 'behavioral', 'situational', 'cultural', 'general') DEFAULT 'general',
    difficulty_level ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    job_position VARCHAR(100) NULL, -- NULL untuk general questions
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Interview Question Responses
CREATE TABLE IF NOT EXISTS interview_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    interview_schedule_id INT NOT NULL,
    question_id INT NOT NULL,
    response_text TEXT,
    score INT CHECK (score >= 1 AND score <= 5),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (interview_schedule_id) REFERENCES interview_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES interview_questions(id) ON DELETE CASCADE
);

-- Tabel Calendar Integration Settings
CREATE TABLE IF NOT EXISTS calendar_integrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    integration_type ENUM('google', 'outlook', 'apple') NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    calendar_id VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default interview questions
INSERT INTO interview_questions (question_text, question_type, difficulty_level, job_position, created_by) VALUES
('Ceritakan tentang diri Anda dan pengalaman kerja Anda', 'general', 'easy', NULL, 1),
('Mengapa Anda tertarik dengan posisi ini?', 'general', 'easy', NULL, 1),
('Apa kelebihan dan kekurangan Anda?', 'behavioral', 'medium', NULL, 1),
('Bagaimana Anda menangani konflik di tempat kerja?', 'behavioral', 'medium', NULL, 1),
('Ceritakan tentang proyek terbesar yang pernah Anda kerjakan', 'behavioral', 'medium', NULL, 1),
('Bagaimana Anda mengatasi deadline yang ketat?', 'situational', 'medium', NULL, 1),
('Apa yang Anda ketahui tentang perusahaan kami?', 'cultural', 'easy', NULL, 1),
('Di mana Anda melihat diri Anda dalam 5 tahun ke depan?', 'general', 'medium', NULL, 1),
('Apa motivasi terbesar Anda dalam bekerja?', 'cultural', 'easy', NULL, 1),
('Bagaimana Anda bekerja dalam tim?', 'behavioral', 'medium', NULL, 1);

-- Update applications table to add interview status
ALTER TABLE applications 
ADD COLUMN IF NOT EXISTS interview_status ENUM('not_scheduled', 'scheduled', 'completed', 'cancelled') DEFAULT 'not_scheduled' AFTER status;

-- Create indexes for better performance (only if they don't exist)
CREATE INDEX IF NOT EXISTS idx_interview_schedules_date ON interview_schedules(interview_date);
CREATE INDEX IF NOT EXISTS idx_interview_schedules_status ON interview_schedules(status);
CREATE INDEX IF NOT EXISTS idx_interview_feedback_schedule ON interview_feedback(interview_schedule_id);
CREATE INDEX IF NOT EXISTS idx_interview_questions_type ON interview_questions(question_type);
CREATE INDEX IF NOT EXISTS idx_interview_questions_position ON interview_questions(job_position);
