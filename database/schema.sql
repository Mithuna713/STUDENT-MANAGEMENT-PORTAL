CREATE DATABASE IF NOT EXISTS student_portal
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_portal;

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  roll_number VARCHAR(30) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL,
  course VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS student_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_type VARCHAR(100) NOT NULL,
  upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_student_files_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE
);

-- Optional test record:
INSERT IGNORE INTO students(name, roll_number, email, course)
VALUES ('Arun Kumar', 'DEMO001', 'arun@example.com', 'Computer Science and Engineering');
