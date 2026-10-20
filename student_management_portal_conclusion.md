# Conclusion – Student Management Portal

The Student Management Portal was built in three connected modules, each covering a different layer of a web application.

1. **Registration form (HTML, CSS, JavaScript):** collects student details and validates them in the browser. It checks required fields, email format, roll number pattern, password strength, and file type and size (JPG, PNG or PDF, up to 2 MB). This gives users instant feedback before any data reaches a server.

2. **Admin panel (PHP and MySQL):** supports add, view, edit and delete operations on student records. It uses PDO prepared statements to prevent SQL injection and escapes output to prevent XSS. File uploads are checked for real MIME type and size, then saved under a random unique name. Their metadata is stored in the database.

3. **Profile portal (Java Servlet, JSP and JDBC):** provides a session-based login, reads student records and linked files from MySQL, and displays them as profile cards using JSP loops and conditions. A file servlet streams images and documents only to logged-in users.

All three modules share one MySQL database (`students` and `student_files` tables linked by a foreign key with cascade delete), which shows how different technologies can work with the same data.

**Limitations and future improvements**
- The registration form validates only on the client side and does not yet save to the database. Server-side validation is needed before real use.
- The servlet login uses a single demo account with a hashed password. A real system should use per-user accounts with salted hashing (such as bcrypt).
- The PHP and servlet modules use separate upload folders. A shared storage location would let files uploaded in the admin panel appear in the profile portal.

Overall, the project demonstrates form validation, CRUD operations, file handling, session management and database integration across client-side, PHP and Java web technologies.
