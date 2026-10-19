# Student Management Portal

## STUDENT REGISTRATION FORM USING HTML, CSS AND JAVASCRIPT
- `q1-registration/index.html`: registration form for name, roll number, email, course, password and optional file upload.
- `q1-registration/style.css`: layout, colors, input styling and responsive design.
- `q1-registration/script.js`: required-field, email, roll-number, password-strength, file-type and file-size validation.

## STUDENT MANAGEMENT SYSTEM USING PHP AND MYSQL
- `database/schema.sql`: creates `student_portal`, `students`, and `student_files` tables.
- `q2-php/config.php`: PDO database connection.
- `q2-php/index.php`: add, view, edit and delete student records; uploads files and records metadata.
- `q2-php/download.php`: serves stored files after checking the database record and allowed MIME types.
- `q2-php/style.css`: responsive admin panel styling.
- File metadata includes student link, original name, stored name/path, MIME type and upload date.

##  STUDENT PORTAL USING JAVA SERVLETS AND JDBC
- `q3-servlet/src/com/college/DBUtil.java`: JDBC connection.
- `Student.java`: student model.
- `LoginServlet.java`, `LogoutServlet.java`: demo login/session handling.
- `StudentServlet.java`: reads student records and linked file metadata using JDBC.
- `FileServlet.java`: checks session and streams supported files.
- `q3-servlet/WebContent/login.jsp`: login page.
- `q3-servlet/WebContent/students.jsp`: loops over student records and displays profiles and file links.
- `q3-servlet/WebContent/WEB-INF/web.xml`: web app configuration.

## Documentation and outputs
- `README.md`: setup instructions.
- `Student Management Portal`: report with aim, procedures, output preview figures and conclusion.
- `outputs/`: outputs taken from live browser.
