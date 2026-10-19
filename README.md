 ## Student Management Portal



This repository contains source code and setup notes 

## Folder structure
-  `registration/` — HTML, CSS and JavaScript registration form.
- `-php/` — PHP student CRUD admin panel and file upload handling.
- `-servlet/` — Java Servlet/JSP/JDBC student profile portal.
- `database/schema.sql` — MySQL database schema and optional demo student.
- `outputs/` — illustrative UI previews, not live browser screenshots.
- `student portal.pdf` — report containing aim, procedures, output previews and conclusion.
- `PROJECT_CONTENTS.md` — map of project files to assignment requirements.

## Requirements
-  Any modern web browser.
- PHP 8+, MySQL 8+, Apache; XAMPP is a convenient local option.
- JDK 11+, Apache Tomcat 9, and MySQL Connector/J. The code uses `javax.servlet`, so Tomcat 9 is intended.

## Run
Open `registration/index.html` in a browser. Try missing fields, an invalid email, a weak password, and a file larger than 2 MB to test validation.

The form demonstrates client-side validation. Client-side checks alone are not sufficient for a production system; validate all fields again on the server.

## Run (PHP + MySQL)
1. Start Apache and MySQL.
2. Import `database/schema.sql` using phpMyAdmin or the MySQL client.
3. Copy `php/` into the Apache document root, for example `C:/xampp/htdocs/php/`.
4. Update database credentials in `php/config.php`.
5. Ensure `php/uploads/` is writable by the web server.
6. Visit `http://localhost/php/`.

The PHP panel supports add, view, edit and delete operations. It validates required student details, checks uploaded MIME type and size, creates a unique stored filename to avoid overwriting, and stores metadata in MySQL.

## Run  (Servlet + JSP + JDBC)
1. Create the database using `database/schema.sql`.
2. Add MySQL Connector/J to the application's `WEB-INF/lib/`.
3. Update database credentials in `servlet/src/com/college/DBUtil.java`.
4. Deploy the web application to Tomcat 9.
5. Open `/login.jsp`.

**Demo login:** `studentadmin` / `ChangeMe123!`  
Change the demo credentials before any real deployment. Do not use this demo login for a public production site.

The servlet code retrieves student records and linked file metadata from MySQL, uses JSP looping/conditional logic to display multiple records, checks the login session, and streams supported files through a servlet.

**File-sharing note:** The PHP and Servlet modules must be configured to read the same physical upload directory if you want files uploaded in Q2 to display in Q3. The included folders are separate by default; configure a shared storage location and update the file handling path before testing the complete cross-module workflow.

## Output screenshots
The `outputs/` images are taken from the live browser.
1.  registration form and validation result.
2.  admin panel with records and CRUD actions.
3.  logged-in student profile portal and file links.
