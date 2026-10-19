package com.college;
import java.io.IOException;
import java.sql.*;
import java.util.*;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

@WebServlet("/students")
public class StudentServlet extends HttpServlet {
    @Override protected void doGet(HttpServletRequest req,HttpServletResponse resp) throws ServletException, IOException {
        HttpSession session=req.getSession(false);
        if(session==null || !Boolean.TRUE.equals(session.getAttribute("loggedIn"))) {
            resp.sendRedirect(req.getContextPath()+"/login.jsp"); return;
        }
        List<Student> students=new ArrayList<>();
        Map<Integer,List<FileInfo>> files=new HashMap<>();
        String sql="SELECT s.id,s.name,s.roll_number,s.email,s.course,f.id AS file_id,f.original_name,f.file_type " +
                   "FROM students s LEFT JOIN student_files f ON s.id=f.student_id ORDER BY s.id DESC,f.id DESC";
        try(Connection con=DBUtil.getConnection(); PreparedStatement ps=con.prepareStatement(sql); ResultSet rs=ps.executeQuery()) {
            Map<Integer,Student> byId=new LinkedHashMap<>();
            while(rs.next()) {
                int id=rs.getInt("id");
                byId.putIfAbsent(id,new Student(id,rs.getString("name"),rs.getString("roll_number"),rs.getString("email"),rs.getString("course")));
                int fileId=rs.getInt("file_id");
                if(!rs.wasNull()) files.computeIfAbsent(id,k->new ArrayList<>()).add(new FileInfo(fileId,rs.getString("original_name"),rs.getString("file_type")));
            }
            students.addAll(byId.values());
        } catch(SQLException e) { throw new ServletException("Could not load student records.",e); }
        req.setAttribute("students",students); req.setAttribute("files",files);
        req.getRequestDispatcher("/students.jsp").forward(req,resp);
    }
    public static class FileInfo {
        private final int id; private final String name,type;
        public FileInfo(int id,String name,String type){this.id=id;this.name=name;this.type=type;}
        public int getId(){return id;} public String getName(){return name;} public String getType(){return type;}
    }
}
