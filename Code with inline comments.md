# Student Management Portal – Complete Code with Inline Explanations

The project has **three modules** that share one MySQL database:

| Module | Tech | Purpose |
|---|---|---|
| 1. Registration form | HTML, CSS, JavaScript | Client-side form validation |
| 2. Admin panel | PHP + MySQL (PDO) | Add / view / edit / delete students, upload files |
| 3. Profile portal | Java Servlet + JSP + JDBC | Login, show student profiles and files |

Folder layout:

```
registration/   index.html, style.css, script.js
php/            config.php, index.php, download.php, style.css
servlet/
  src/com/college/   DBUtil.java, Student.java, LoginServlet.java,
                     LogoutServlet.java, StudentServlet.java, FileServlet.java
  WebContent/        login.jsp, students.jsp, style.css, WEB-INF/web.xml
database/       schema.sql
```

---

# PART 0 – DATABASE

## `database/schema.sql`

```sql
-- Create the database only if it does not already exist.
-- utf8mb4 supports all characters (including emojis and Indian-language names).
CREATE DATABASE IF NOT EXISTS student_portal
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Switch to the new database so the tables below are created inside it.
USE student_portal;

-- TABLE 1: students – one row per student.
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,         -- unique id, auto-increments (1,2,3...)
  name VARCHAR(100) NOT NULL,                -- full name (required)
  roll_number VARCHAR(30) NOT NULL UNIQUE,   -- UNIQUE: no two students can share a roll number
  email VARCHAR(150) NOT NULL,               -- email address (required)
  course VARCHAR(120) NOT NULL,              -- course / department name
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- set automatically when the row is inserted
);

-- TABLE 2: student_files – metadata of files uploaded for a student.
-- (The file itself is stored on disk; only its details are stored here.)
CREATE TABLE IF NOT EXISTS student_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,                   -- which student this file belongs to
  original_name VARCHAR(255) NOT NULL,       -- name the user uploaded, e.g. "photo.png"
  stored_name VARCHAR(255) NOT NULL,         -- random name actually saved on disk
  file_path VARCHAR(500) NOT NULL,           -- relative path, e.g. "uploads/ab12....png"
  file_type VARCHAR(100) NOT NULL,           -- MIME type, e.g. "image/png"
  upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  -- FOREIGN KEY links student_id to students.id.
  -- ON DELETE CASCADE: deleting a student automatically deletes their file records.
  CONSTRAINT fk_student_files_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE
);

-- Optional demo record so the portal is not empty on first run.
-- INSERT IGNORE skips the insert if the roll number already exists.
INSERT IGNORE INTO students(name, roll_number, email, course)
VALUES ('Arun Kumar', 'DEMO001', 'arun@example.com', 'Computer Science and Engineering');
```

---

# PART 1 – STUDENT REGISTRATION FORM (HTML + CSS + JavaScript)

## `registration/index.html`

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Makes the page scale properly on mobile screens -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Registration</title>
  <!-- Link to the stylesheet (see style.css below) -->
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main class="page">
    <section class="card">
      <div class="brand">KPR <span>STUDENT PORTAL</span></div>
      <h1>Student Registration</h1>
      <p class="muted">Create a student account using the form below.</p>

      <!-- novalidate turns off the browser's built-in validation
           so our own JavaScript (script.js) handles all checks. -->
      <form id="registrationForm" novalidate>
        <div class="grid">   <!-- 2-column grid layout -->

          <!-- Each field has an <input> and a <small class="error"> where
               script.js writes the error message for that field. -->
          <label>Full name
            <input id="name" name="name" type="text" placeholder="Enter full name" required>
            <small class="error" id="nameError"></small>
          </label>

          <label>Roll number
            <input id="roll" name="roll" type="text" placeholder="e.g. 23CS101" required>
            <small class="error" id="rollError"></small>
          </label>

          <label>Email address
            <input id="email" name="email" type="email" placeholder="name@example.com" required>
            <small class="error" id="emailError"></small>
          </label>

          <label>Course
            <!-- Dropdown; the first option has an empty value = "nothing selected" -->
            <select id="course" name="course" required>
              <option value="">Select course</option>
              <option>Computer Science and Engineering</option>
              <option>Information Technology</option>
              <option>Mechanical Engineering</option>
              <option>Electronics and Communication Engineering</option>
            </select>
            <small class="error" id="courseError"></small>
          </label>

          <label>Password
            <input id="password" name="password" type="password" placeholder="Minimum 8 characters" required>
            <small class="hint">Use uppercase, lowercase, a number and a symbol.</small>
            <small class="error" id="passwordError"></small>
          </label>

          <label>Profile image / document
            <!-- accept= limits the file picker to these extensions -->
            <input id="file" name="file" type="file" accept=".jpg,.jpeg,.png,.pdf">
            <small class="hint">JPG, PNG or PDF · Maximum 2 MB</small>
            <small class="error" id="fileError"></small>
          </label>
        </div>

        <button type="submit">Register Student</button>
        <!-- Success message appears here. role/aria-live let screen readers announce it. -->
        <p id="result" role="status" aria-live="polite"></p>
      </form>
    </section>
  </main>

  <!-- Load the validation script at the end so the form already exists in the page -->
  <script src="script.js"></script>
</body>
</html>
```

## `registration/script.js`

```javascript
// Get the form element and list the ids of all fields we validate.
const form = document.getElementById('registrationForm');
const fields = ['name','roll','email','course','password','file'];

// Helper: show (or clear) the error message under a field.
// For id "name" it writes into the element with id "nameError".
function error(id, message){
  document.getElementById(id+'Error').textContent = message;
}

// Run this code when the user clicks "Register Student".
form.addEventListener('submit', function(e){
  e.preventDefault();                       // stop the page from reloading/submitting
  fields.forEach(id => error(id,''));       // clear all old error messages
  document.getElementById('result').textContent = '';  // clear old success message
  let valid = true;                         // becomes false if any check fails

  // Read the values the user typed. trim() removes spaces at both ends.
  const name = document.getElementById('name').value.trim();
  const roll = document.getElementById('roll').value.trim();
  const email = document.getElementById('email').value.trim();
  const course = document.getElementById('course').value;
  const password = document.getElementById('password').value;
  const fileInput = document.getElementById('file');

  // 1) Name: at least 2 characters.
  if(name.length < 2){ error('name','Enter a valid full name.'); valid=false; }

  // 2) Roll number: 3-20 characters, only letters, digits, "/" or "-".
  if(!/^[A-Za-z0-9/-]{3,20}$/.test(roll)){
    error('roll','Use 3–20 letters, numbers, / or -.'); valid=false;
  }

  // 3) Email: something@something.something (no spaces).
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
    error('email','Enter a valid email address.'); valid=false;
  }

  // 4) Course: must not be the empty default option.
  if(!course){ error('course','Please select a course.'); valid=false; }

  // 5) Password: 8+ chars with lowercase, uppercase, digit and symbol.
  //    Each (?=...) is a "lookahead" that requires that character type to exist.
  if(!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)){
    error('password','Use 8+ characters with upper/lowercase, number and symbol.');
    valid=false;
  }

  // 6) File (optional): only check if the user actually chose one.
  if(fileInput.files.length){
    const file = fileInput.files[0];
    // Extension must be jpg, jpeg, png or pdf (case-insensitive).
    if(!/\.(jpg|jpeg|png|pdf)$/i.test(file.name)){
      error('file','Only JPG, PNG or PDF files are allowed.'); valid=false;
    }
    // Size must be 2 MB or less (2 * 1024 * 1024 bytes).
    if(file.size > 2*1024*1024){
      error('file','File must be 2 MB or smaller.'); valid=false;
    }
  }

  // If every check passed, show a success message (demo only – nothing is sent to a server).
  if(valid) document.getElementById('result').textContent =
    'Validation successful! Demo registration is complete.';
});
```

## `registration/style.css`

```css
/* Global font, text colour and page background */
:root{font-family:Arial,Helvetica,sans-serif;color:#18243a;background:#eef3f9}

/* border-box: padding/border are included in an element's width (easier sizing) */
*{box-sizing:border-box}
body{margin:0}

/* Centre the card both vertically and horizontally */
.page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px}

/* White rounded card with soft shadow; max width 850px */
.card{background:#fff;width:min(850px,100%);padding:34px;border-radius:18px;
      box-shadow:0 16px 45px #19345b18}

/* "KPR STUDENT PORTAL" logo text */
.brand{font-weight:800;letter-spacing:2px;color:#1c5fc4}
.brand span{font-size:11px;letter-spacing:1px;color:#66748a;margin-left:8px}

h1{margin:20px 0 6px;font-size:30px}
.muted,.hint{color:#68758a}      /* grey helper text */
.muted{margin-top:0}

/* Two equal columns for the form fields */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:25px}

/* Label stacks its text above its input */
label{display:flex;flex-direction:column;gap:8px;font-size:14px;font-weight:700}

/* Inputs and dropdown share one look */
input,select{width:100%;padding:12px 13px;border:1px solid #cbd5e1;border-radius:8px;
             font:inherit;font-weight:400;background:#fff}
/* Blue highlight when a field is focused */
input:focus,select:focus{outline:3px solid #dbeafe;border-color:#2563eb}

.hint{font-size:12px;font-weight:400}
/* Red error text; min-height keeps layout from jumping when text appears */
.error{color:#c62828;font-size:12px;font-weight:400;min-height:14px}

/* Blue submit button with hover colour */
button{margin-top:24px;background:#1d5fc4;color:#fff;border:0;border-radius:9px;
       padding:13px 20px;font-size:15px;font-weight:700;cursor:pointer}
button:hover{background:#154b9d}

/* Green success message */
#result{font-weight:700;color:#187346}

/* Responsive: on screens <= 620px use a single column and smaller title */
@media(max-width:620px){
  .card{padding:22px}
  .grid{grid-template-columns:1fr}
  h1{font-size:25px}
}
```

---

# PART 2 – STUDENT ADMIN PANEL (PHP + MySQL)

## `php/config.php`

```php
<?php
// ---- Database connection settings (change to match your MySQL setup) ----
$host = 'localhost';
$db   = 'student_portal';   // database created by schema.sql
$user = 'root';             // default XAMPP user
$pass = '';                 // default XAMPP password is empty
$charset = 'utf8mb4';

// DSN = "Data Source Name": tells PDO which driver, host, database and charset to use.
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,           // throw exceptions on SQL errors
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // rows come back as ['column'=>value]
  PDO::ATTR_EMULATE_PREPARES => false,                   // use real prepared statements (safer vs SQL injection)
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);          // open the connection
} catch (PDOException $e) {
  http_response_code(500);                               // HTTP 500 = server error
  exit('Database connection failed. Check config.php and MySQL.');  // stop, hide technical details
}
?>
```

## `php/index.php`

```php
<?php
session_start();                          // start a PHP session (not heavily used here)
require __DIR__ . '/config.php';          // load the $pdo database connection

// Folder where uploaded files are saved. Create it if missing (0750 = restricted permissions).
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0750, true);

$msg = '';   // message shown to the user at the top of the page

// Helper h(): escape special HTML characters so user data cannot inject scripts (prevents XSS).
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ================= HANDLE FORM SUBMISSIONS (POST requests) =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';                                   // 'add', 'edit' or 'delete'
  $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;     // student id (must be an integer)

  // ---------- DELETE ----------
  if ($action === 'delete' && $id) {
    // Prepared statement: the "?" is filled safely, preventing SQL injection.
    $pdo->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
    $msg = 'Student record deleted.';   // linked file rows are removed by ON DELETE CASCADE

  // ---------- ADD or EDIT ----------
  } elseif (in_array($action, ['add','edit'], true)) {
    // Read and clean the submitted values.
    $name = trim($_POST['name'] ?? '');
    $roll = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    // Server-side validation (never rely only on client-side checks).
    if ($name === '' || $roll === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $course === '') {
      $msg = 'Please provide a name, roll number, valid email and course.';
    } else {
      try {
        if ($action === 'add') {
          // INSERT a new student.
          $stmt = $pdo->prepare('INSERT INTO students(name,roll_number,email,course) VALUES(?,?,?,?)');
          $stmt->execute([$name,$roll,$email,$course]);
          $id = (int)$pdo->lastInsertId();      // id of the new row (needed to link the file)
          $msg = 'Student added successfully.';
        } else {
          // UPDATE an existing student.
          $pdo->prepare('UPDATE students SET name=?,roll_number=?,email=?,course=? WHERE id=?')
              ->execute([$name,$roll,$email,$course,$id]);
          $msg = 'Student details updated.';
        }

        // ---------- FILE UPLOAD (optional) ----------
        // Only process if the user actually selected a file.
        if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
          $f = $_FILES['document'];

          // Check for upload errors and the 2 MB size limit.
          if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed.');
          if ($f['size'] > 2*1024*1024) throw new RuntimeException('File exceeds 2 MB.');

          // Detect the REAL file type from file contents (not from the user-supplied extension).
          $finfo = new finfo(FILEINFO_MIME_TYPE);
          $mime = $finfo->file($f['tmp_name']);

          // Whitelist of allowed MIME types and the extension to save with.
          $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf'];
          if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG and PDF files are allowed.');

          // Make a random unique filename so files never overwrite each other.
          $stored = bin2hex(random_bytes(16)).'.'.$allowed[$mime];

          // Move the file from PHP's temp folder to the uploads folder.
          if (!move_uploaded_file($f['tmp_name'], $uploadDir.$stored))
            throw new RuntimeException('Could not save uploaded file.');

          // Save the file's metadata in the student_files table.
          $pdo->prepare('INSERT INTO student_files(student_id,original_name,stored_name,file_path,file_type) VALUES(?,?,?,?,?)')
              ->execute([$id, basename($f['name']), $stored, 'uploads/'.$stored, $mime]);
          $msg .= ' File uploaded.';
        }
      } catch (Throwable $e) {
        // A PDOException here usually means a duplicate roll number (UNIQUE constraint).
        $msg = ($e instanceof PDOException)
          ? 'Could not save record. Check for duplicate roll number.'
          : $e->getMessage();
      }
    }
  }
}

// ================= LOAD DATA FOR DISPLAY =================

// If the URL has ?edit=ID, load that student so the form is pre-filled for editing.
$edit = null;
if (isset($_GET['edit'])) {
  $stmt = $pdo->prepare('SELECT * FROM students WHERE id=?');
  $stmt->execute([(int)$_GET['edit']]);
  $edit = $stmt->fetch();
}

// All students, newest first.
$students = $pdo->query('SELECT * FROM students ORDER BY id DESC')->fetchAll();

// All files, then group them by student id for easy lookup in the table below.
$files = $pdo->query('SELECT * FROM student_files ORDER BY upload_date DESC')->fetchAll();
$filesByStudent = [];
foreach ($files as $f) $filesByStudent[$f['student_id']][] = $f;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Admin Panel</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header><div><b>KPR</b> STUDENT ADMIN PANEL</div></header>
<main>
  <h1>Student Records</h1>

  <!-- Show the status message (if any). <?=h(...)?> prints an escaped value. -->
  <?php if($msg): ?><p class="notice"><?=h($msg)?></p><?php endif; ?>

  <!-- ============ ADD / EDIT FORM ============ -->
  <section class="panel">
    <!-- Heading changes depending on whether we are editing or adding -->
    <h2><?=$edit?'Edit Student':'Add Student'?></h2>

    <!-- enctype="multipart/form-data" is REQUIRED for file uploads -->
    <form method="post" enctype="multipart/form-data">
      <!-- Hidden fields tell the PHP code what to do and which student -->
      <input type="hidden" name="action" value="<?=$edit?'edit':'add'?>">
      <input type="hidden" name="id" value="<?=h($edit['id']??'')?>">

      <div class="fields">
        <!-- value="..." pre-fills the field when editing; ?? '' avoids errors when adding -->
        <label>Full name<input name="name" required value="<?=h($edit['name']??'')?>"></label>
        <label>Roll number<input name="roll_number" required value="<?=h($edit['roll_number']??'')?>"></label>
        <label>Email<input name="email" type="email" required value="<?=h($edit['email']??'')?>"></label>
        <label>Course<input name="course" required value="<?=h($edit['course']??'')?>"></label>
        <label class="wide">Profile image / ID proof / certificate (JPG, PNG, PDF; max 2 MB)
          <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf">
        </label>
      </div>

      <button type="submit"><?=$edit?'Update Student':'Add Student'?></button>
      <?php if($edit): ?> <a href="index.php">Cancel</a><?php endif; ?>
    </form>
  </section>

  <!-- ============ STUDENT LIST TABLE ============ -->
  <section class="panel">
    <h2>Student List</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Name</th><th>Roll No.</th><th>Email</th><th>Course</th><th>Files</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach($students as $s): ?>   <!-- one table row per student -->
          <tr>
            <td><?=h($s['name'])?></td>
            <td><?=h($s['roll_number'])?></td>
            <td><?=h($s['email'])?></td>
            <td><?=h($s['course'])?></td>
            <td>
              <!-- List this student's files; each links to download.php?id=FILE_ID -->
              <?php foreach(($filesByStudent[$s['id']]??[]) as $f): ?>
                <a href="download.php?id=<?=$f['id']?>"><?=h($f['original_name'])?></a><br>
                <small><?=h($f['file_type'])?> · <?=h($f['upload_date'])?></small><br>
              <?php endforeach; ?>
            </td>
            <td>
              <!-- Edit link reloads the page with ?edit=ID -->
              <a href="?edit=<?=$s['id']?>">Edit</a>
              <!-- Delete is a small POST form; confirm() asks "Are you sure?" first -->
              <form class="inline" method="post"
                    onsubmit="return confirm('Delete this student and linked file records?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=$s['id']?>">
                <button class="danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <!-- If there are no students, show a placeholder row -->
        <?php if(!$students): ?><tr><td colspan="6">No student records yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
</body>
</html>
```

## `php/download.php`

```php
<?php
require __DIR__ . '/config.php';   // database connection

// Read ?id= from the URL and make sure it is a valid integer.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid file ID'); }   // 400 = bad request

// Look up the file record in the database.
$stmt = $pdo->prepare('SELECT * FROM student_files WHERE id=?');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) { http_response_code(404); exit('File not found'); }  // 404 = not found

// Build the real path on disk and make sure the file actually exists.
$path = __DIR__ . '/' . $file['file_path'];
if (!is_file($path)) { http_response_code(404); exit('Stored file is missing'); }

// Only serve whitelisted file types.
$allowed = ['image/jpeg','image/png','application/pdf'];
if (!in_array($file['file_type'], $allowed, true)) { http_response_code(415); exit('Unsupported file type'); }

// ---- Send the file to the browser ----
header('Content-Type: '.$file['file_type']);        // tells the browser what the file is
header('X-Content-Type-Options: nosniff');          // stops the browser from guessing a different type
// "inline" = display in the browser; quotes are stripped from the name to prevent header tricks.
header('Content-Disposition: inline; filename="'.str_replace('"','',basename($file['original_name'])).'"');
header('Content-Length: '.filesize($path));         // size in bytes
readfile($path);                                    // output the file contents
```

## `php/style.css`

```css
/* Base: border-box sizing, page colours and font */
*{box-sizing:border-box}
body{margin:0;background:#f1f5fb;color:#1c2b43;font:15px Arial,sans-serif}

/* Dark-blue top bar */
header{background:#153e75;color:#fff;padding:18px 5%}
header b{font-size:22px;margin-right:12px}

/* Centered content area */
main{max-width:1180px;margin:28px auto;padding:0 20px}
h1{font-size:30px}
h2{margin-top:0}

/* White "panel" boxes that hold the form and the table */
.panel{background:white;padding:24px;border-radius:14px;margin:20px 0;
       box-shadow:0 5px 22px #153e7510}

/* Form fields in two columns */
.fields{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fields label{display:flex;flex-direction:column;gap:7px;font-weight:700}
.fields input{padding:11px;border:1px solid #cbd5e1;border-radius:7px;font:inherit}
.wide{grid-column:1/-1}            /* spans both columns (used by the file field) */

/* Buttons: blue normally, red for Delete */
button{background:#1e60bd;color:white;border:0;border-radius:7px;padding:10px 15px;
       cursor:pointer;margin-top:15px}
button.danger{background:#b42318;margin:0}

/* Green success / info message */
.notice{padding:12px;background:#e3f7ec;color:#17613c;border-radius:8px}

/* Table: scrolls sideways on small screens */
.table-wrap{overflow:auto}
table{border-collapse:collapse;width:100%;min-width:800px}
th,td{text-align:left;padding:12px;border-bottom:1px solid #e2e8f0;vertical-align:top}
th{background:#f7f9fc}
a{color:#1e60bd}
.inline{display:inline}            /* keeps the Delete form on the same line as "Edit" */

/* Mobile: single-column form */
@media(max-width:650px){
  .fields{grid-template-columns:1fr}
  .wide{grid-column:auto}
  .panel{padding:17px}
}
```

---

# PART 3 – STUDENT PROFILE PORTAL (Java Servlet + JSP + JDBC)

## `servlet/src/com/college/DBUtil.java`

```java
package com.college;   // all classes live in the "com.college" package

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

// Utility class that gives every servlet a database connection.
public class DBUtil {
    // JDBC URL: protocol, host, port, database name, plus options
    // (useSSL=false for local development; serverTimezone avoids timezone errors).
    private static final String URL = "jdbc:mysql://localhost:3306/student_portal?useSSL=false&serverTimezone=UTC";
    private static final String USER = "root";
    private static final String PASSWORD = ""; // Update for your MySQL setup.

    // Static block: runs ONCE when the class is first loaded.
    // It loads the MySQL driver (Connector/J) so DriverManager can use it.
    static {
        try { Class.forName("com.mysql.cj.jdbc.Driver"); }
        catch (ClassNotFoundException e) { throw new ExceptionInInitializerError(e); }
    }

    // Opens and returns a NEW connection each time it is called.
    // Callers close it using try-with-resources.
    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }
}
```

## `servlet/src/com/college/Student.java`

```java
package com.college;

// Model class (POJO): holds one student's data read from the database.
public class Student {
    // Fields are private (encapsulation); accessed only through getters.
    private int id;
    private String name, rollNumber, email, course;

    // Constructor: sets all fields when a Student object is created.
    public Student(int id, String name, String rollNumber, String email, String course) {
        this.id=id; this.name=name; this.rollNumber=rollNumber; this.email=email; this.course=course;
    }

    // Getters – the JSP uses these to display data.
    public int getId(){return id;}
    public String getName(){return name;}
    public String getRollNumber(){return rollNumber;}
    public String getEmail(){return email;}
    public String getCourse(){return course;}
}
```

## `servlet/src/com/college/LoginServlet.java`

```java
package com.college;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

// @WebServlet maps this servlet to the URL /login (no web.xml entry needed).
@WebServlet("/login")
public class LoginServlet extends HttpServlet {

    // SHA-256 hash of the demo password "ChangeMe123!".
    // Only the hash is stored in code, never the plain password.
    private static final String DEMO_HASH = "9a4aabf0e5cf71cae2cea646613ce7e2a5919fa758e56819704be25a3a2c1f0b";

    // Converts a string into its SHA-256 hash (as a 64-character hex string).
    private String hash(String value) throws NoSuchAlgorithmException {
        byte[] bytes = MessageDigest.getInstance("SHA-256")
                                    .digest(value.getBytes(StandardCharsets.UTF_8));
        StringBuilder out = new StringBuilder();
        for (byte b : bytes) out.append(String.format("%02x", b));   // each byte -> 2 hex digits
        return out.toString();
    }

    // Handles the login form (method="post" in login.jsp).
    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp)
            throws IOException, ServletException {
        String username = req.getParameter("username"), password = req.getParameter("password");
        try {
            // Correct if username matches AND the hash of the entered password matches.
            if ("studentadmin".equals(username) && DEMO_HASH.equals(hash(password == null ? "" : password))) {
                // Create a session and mark the user as logged in.
                req.getSession(true).setAttribute("loggedIn", Boolean.TRUE);
                // Redirect to the student list (handled by StudentServlet).
                resp.sendRedirect(req.getContextPath() + "/students");
            } else {
                // Wrong credentials: store an error and show login.jsp again.
                req.setAttribute("error", "Invalid username or password.");
                req.getRequestDispatcher("/login.jsp").forward(req, resp);
            }
        } catch (NoSuchAlgorithmException e) { throw new ServletException(e); }
    }
}
```

## `servlet/src/com/college/LogoutServlet.java`

```java
package com.college;

import java.io.IOException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

@WebServlet("/logout")   // reached by the "Log out" link in students.jsp
public class LogoutServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        HttpSession session = req.getSession(false);   // false = don't create a new session
        if (session != null) session.invalidate();     // destroy the session = log the user out
        resp.sendRedirect(req.getContextPath() + "/login.jsp");   // back to the login page
    }
}
```

## `servlet/src/com/college/StudentServlet.java`

```java
package com.college;

import java.io.IOException;
import java.sql.*;
import java.util.*;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

@WebServlet("/students")   // URL: /students
public class StudentServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws ServletException, IOException {

        // ---- Security: only logged-in users may continue ----
        HttpSession session = req.getSession(false);
        if (session == null || !Boolean.TRUE.equals(session.getAttribute("loggedIn"))) {
            resp.sendRedirect(req.getContextPath() + "/login.jsp");
            return;
        }

        List<Student> students = new ArrayList<>();                 // list of students for the page
        Map<Integer, List<FileInfo>> files = new HashMap<>();       // studentId -> that student's files

        // One query: LEFT JOIN returns every student, even those with no files (file columns = NULL).
        String sql = "SELECT s.id,s.name,s.roll_number,s.email,s.course,f.id AS file_id,f.original_name,f.file_type " +
                     "FROM students s LEFT JOIN student_files f ON s.id=f.student_id ORDER BY s.id DESC,f.id DESC";

        // try-with-resources automatically closes the connection, statement and result set.
        try (Connection con = DBUtil.getConnection();
             PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {

            // LinkedHashMap keeps insertion order and prevents duplicate students
            // (a student with 3 files appears in 3 rows of the result).
            Map<Integer, Student> byId = new LinkedHashMap<>();
            while (rs.next()) {
                int id = rs.getInt("id");
                // Add the student only the first time we see this id.
                byId.putIfAbsent(id, new Student(id, rs.getString("name"),
                        rs.getString("roll_number"), rs.getString("email"), rs.getString("course")));

                int fileId = rs.getInt("file_id");
                // rs.wasNull() is true when file_id was NULL (student has no file) -> skip it.
                if (!rs.wasNull())
                    files.computeIfAbsent(id, k -> new ArrayList<>())
                         .add(new FileInfo(fileId, rs.getString("original_name"), rs.getString("file_type")));
            }
            students.addAll(byId.values());
        } catch (SQLException e) {
            throw new ServletException("Could not load student records.", e);
        }

        // Pass the data to the JSP and forward to it (server-side, URL doesn't change).
        req.setAttribute("students", students);
        req.setAttribute("files", files);
        req.getRequestDispatcher("/students.jsp").forward(req, resp);
    }

    // Small helper class holding one file's id, original name and MIME type.
    public static class FileInfo {
        private final int id;
        private final String name, type;
        public FileInfo(int id, String name, String type) { this.id = id; this.name = name; this.type = type; }
        public int getId() { return id; }
        public String getName() { return name; }
        public String getType() { return type; }
    }
}
```

## `servlet/src/com/college/FileServlet.java`

```java
package com.college;

import java.io.*;
import java.sql.*;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

@WebServlet("/file")   // URL: /file?id=FILE_ID  (used by <img> and links in students.jsp)
public class FileServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp)
            throws IOException, ServletException {

        // ---- Only logged-in users may view files; otherwise 401 Unauthorized ----
        HttpSession session = req.getSession(false);
        if (session == null || !Boolean.TRUE.equals(session.getAttribute("loggedIn"))) {
            resp.sendError(401); return;
        }

        // ---- Read and validate ?id= ----
        int id;
        try { id = Integer.parseInt(req.getParameter("id")); }
        catch (Exception e) { resp.sendError(400); return; }   // 400 Bad Request

        // ---- Look up the file's details in the database ----
        String stored = null, original = null, type = null;
        try (Connection con = DBUtil.getConnection();
             PreparedStatement ps = con.prepareStatement(
                 "SELECT stored_name,original_name,file_type FROM student_files WHERE id=?")) {
            ps.setInt(1, id);   // fill the "?" safely
            try (ResultSet rs = ps.executeQuery()) {
                if (!rs.next()) { resp.sendError(404); return; }   // no such file
                stored = rs.getString("stored_name");
                original = rs.getString("original_name");
                type = rs.getString("file_type");
            }
        } catch (SQLException e) { throw new ServletException(e); }

        // ---- Allow only jpg, png and pdf ----
        String ext = stored.toLowerCase();
        if (!(ext.endsWith(".jpg") || ext.endsWith(".png") || ext.endsWith(".pdf"))) {
            resp.sendError(415); return;   // 415 Unsupported Media Type
        }

        // ---- Locate the file inside the web app's /uploads folder ----
        File file = new File(getServletContext().getRealPath("/uploads"), stored);
        if (!file.isFile()) { resp.sendError(404); return; }

        // ---- Set response headers ----
        resp.setContentType(type);                                  // tells the browser the file type
        resp.setHeader("X-Content-Type-Options", "nosniff");        // no type guessing
        resp.setHeader("Content-Disposition",
                "inline; filename=\"" + original.replace("\"", "") + "\"");   // show in browser

        // ---- Stream the file to the browser in 8 KB chunks ----
        try (InputStream in = new FileInputStream(file); OutputStream out = resp.getOutputStream()) {
            byte[] buffer = new byte[8192];
            int n;
            while ((n = in.read(buffer)) != -1) out.write(buffer, 0, n);
        }
    }
}
```

## `servlet/WebContent/login.jsp`

```jsp
<%-- Page directive: this page outputs HTML in UTF-8 --%>
<%@ page contentType="text/html;charset=UTF-8" %>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Portal Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="login">
  <div class="brand">KPR <span>STUDENT PORTAL</span></div>
  <h1>Admin Login</h1>
  <p>Sign in to view student profiles and documents.</p>

  <%-- If LoginServlet set an "error" attribute (wrong password), show it here --%>
  <% if (request.getAttribute("error") != null) { %>
    <p class="error"><%= request.getAttribute("error") %></p>
  <% } %>

  <%-- The form POSTs to the /login servlet (action="login") --%>
  <form method="post" action="login">
    <label>Username
      <input name="username" required autocomplete="username">
    </label>
    <label>Password
      <input name="password" type="password" required autocomplete="current-password">
    </label>
    <button>Sign in</button>
  </form>

  <p class="small">Demo account: studentadmin / ChangeMe123!</p>
</main>
</body>
</html>
```

## `servlet/WebContent/students.jsp`

```jsp
<%-- Output type + import the Java classes used in the scriptlets below --%>
<%@ page contentType="text/html;charset=UTF-8" %>
<%@ page import="java.util.*,com.college.Student,com.college.StudentServlet.FileInfo" %>

<%-- SECURITY: if the user is not logged in, send them to the login page and stop --%>
<% if (session == null || !Boolean.TRUE.equals(session.getAttribute("loggedIn"))) {
       response.sendRedirect("login.jsp"); return; } %>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Profiles</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header><b>KPR</b> STUDENT PROFILE PORTAL <a href="logout">Log out</a></header>
<main class="content">
  <h1>Student Profiles</h1>
  <p class="muted">Student information and linked files retrieved dynamically from MySQL.</p>

  <div class="cards">
  <%
     // Get the data that StudentServlet stored with req.setAttribute(...)
     List<Student> students = (List<Student>) request.getAttribute("students");
     Map<Integer, List<FileInfo>> files = (Map<Integer, List<FileInfo>>) request.getAttribute("files");

     // Conditional logic: show cards only if there is at least one student.
     if (students != null && !students.isEmpty()) {
         // LOOP: one card per student.
         for (Student s : students) {
  %>
    <article class="student">
      <%-- Avatar circle shows the first letter of the name --%>
      <div class="avatar"><%= s.getName().substring(0,1).toUpperCase() %></div>

      <%-- esc() escapes HTML characters to prevent XSS (defined at the bottom) --%>
      <h2><%= esc(s.getName()) %></h2>
      <p><b>Roll number:</b> <%= esc(s.getRollNumber()) %></p>
      <p><b>Email:</b> <%= esc(s.getEmail()) %></p>
      <p><b>Course:</b> <%= esc(s.getCourse()) %></p>
      <h3>Uploaded files</h3>
      <%
         // Get only this student's files from the map.
         List<FileInfo> list = files.get(s.getId());
         if (list != null && !list.isEmpty()) {
             for (FileInfo f : list) {
                 // If the file is an image, display it inline via the /file servlet.
                 if (f.getType() != null && f.getType().startsWith("image/")) {
      %>
        <img class="profile-image" src="file?id=<%= f.getId() %>"
             alt="Uploaded image for <%= esc(s.getName()) %>">
      <%     } %>
        <%-- Link to open/download the file (images and PDFs) in a new tab --%>
        <p><a href="file?id=<%= f.getId() %>" target="_blank" rel="noopener"><%= esc(f.getName()) %></a>
           <small>(<%= esc(f.getType()) %>)</small></p>
      <%   }
         } else { %>
        <p class="muted">No uploaded files.</p>
      <% } %>
    </article>
  <%   }
     } else { %>
    <p>No students found. Add a student record in the PHP admin panel first.</p>
  <% } %>
  </div>
</main>
</body>
</html>

<%-- <%! ... %> is a JSP DECLARATION: it defines a method for use anywhere in this page. --%>
<%!
// Escapes the 5 special HTML characters so user data is displayed as text, not run as HTML.
private String esc(String s){
    if (s == null) return "";
    return s.replace("&","&amp;").replace("<","&lt;").replace(">","&gt;")
            .replace("\"","&quot;").replace("'","&#39;");
}
%>
```

## `servlet/WebContent/style.css`

```css
/* Base page style */
*{box-sizing:border-box}
body{margin:0;background:#f1f5fb;color:#1c2b43;font:15px Arial,sans-serif}

/* Top bar; the Log out link floats to the right */
header{background:#153e75;color:white;padding:20px 5%;letter-spacing:.5px}
header b{font-size:22px;margin-right:10px}
header a{float:right;color:white}

/* Main content container */
.content{max-width:1150px;margin:35px auto;padding:0 20px}

/* Logo text */
.brand{color:#1d5fc4;font-weight:800;letter-spacing:2px}
.brand span{font-size:11px;color:#6a778b;margin-left:7px}
h1{font-size:30px}
.muted{color:#6b778b}

/* Responsive grid: as many 280px+ columns as fit */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}

/* White cards used for each student and for the login box */
.student,.login{background:white;border-radius:15px;padding:24px;box-shadow:0 8px 30px #1b3c6510}
.student h2{margin:10px 0}

/* Circular avatar with the student's initial */
.avatar{height:48px;width:48px;border-radius:50%;background:#dbeafe;color:#1d5fc4;
        display:grid;place-items:center;font-size:22px;font-weight:bold}
.student p{overflow-wrap:anywhere}   /* long emails wrap instead of overflowing */

/* Uploaded image preview */
.profile-image{max-width:100%;max-height:180px;object-fit:contain;border-radius:8px;
               border:1px solid #e2e8f0}

/* Login box: centred, max 460px wide */
.login{width:min(460px,calc(100% - 32px));margin:9vh auto}
.login label{display:block;font-weight:bold;margin:18px 0}
.login input{display:block;width:100%;padding:12px;margin-top:7px;border:1px solid #cbd5e1;border-radius:7px}
.login button{width:100%;background:#1d5fc4;color:white;padding:12px;border:0;
              border-radius:8px;font-weight:bold}

.small{font-size:12px;color:#6b778b}
.error{color:#b42318}   /* red error message */
```

## `servlet/WebContent/WEB-INF/web.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!-- Deployment descriptor: configuration file for the web application (Servlet 3.1). -->
<web-app xmlns="http://xmlns.jcp.org/xml/ns/javaee" version="3.1">
  <!-- Name shown in Tomcat's manager -->
  <display-name>Student Profile Portal</display-name>

  <!-- Page opened automatically when the user visits the app's root URL -->
  <welcome-file-list>
    <welcome-file>login.jsp</welcome-file>
  </welcome-file-list>

  <!-- Servlets need no <servlet> entries here because they use @WebServlet annotations. -->
</web-app>
```

---

# HOW THE PIECES CONNECT (Quick Summary)

1. **`schema.sql`** creates the `students` and `student_files` tables.
2. **PHP admin panel** (`php/index.php`) adds/edits/deletes students and uploads files into `php/uploads/`, saving file details in `student_files`.
3. **Servlet portal** logs in via `LoginServlet`, `StudentServlet` reads students + files with one JOIN query, `students.jsp` loops over them, and `FileServlet` streams the files (after a login check).
4. **Registration form** is a standalone client-side demo of validation (it does not connect to the database).

**Note:** the PHP and Servlet modules use separate upload folders by default. For files uploaded in PHP to appear in the servlet portal, point both to the same physical folder.
