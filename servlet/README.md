# Servlet module setup
- This module uses `javax.servlet` APIs and is intended for Apache Tomcat 9.
- Add MySQL Connector/J to `WEB-INF/lib/`.
- Run `database/schema.sql` first.
- Update DB credentials in `src/com/college/DBUtil.java`.
- Build/deploy as a web application. In an IDE, create a Dynamic Web Project and copy the `src` and `WebContent` contents into it if needed.
- Demo login: username `studentadmin`, password `ChangeMe123!`. The password is hashed in `LoginServlet.java`; change it before real use.
