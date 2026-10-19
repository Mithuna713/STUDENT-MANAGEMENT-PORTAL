package com.college;
import java.io.*;
import java.sql.*;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

@WebServlet("/file")
public class FileServlet extends HttpServlet {
    @Override protected void doGet(HttpServletRequest req,HttpServletResponse resp) throws IOException, ServletException {
        HttpSession session=req.getSession(false);
        if(session==null || !Boolean.TRUE.equals(session.getAttribute("loggedIn"))) { resp.sendError(401); return; }
        int id;
        try { id=Integer.parseInt(req.getParameter("id")); } catch(Exception e) { resp.sendError(400); return; }
        String stored=null, original=null, type=null;
        try(Connection con=DBUtil.getConnection(); PreparedStatement ps=con.prepareStatement(
            "SELECT stored_name,original_name,file_type FROM student_files WHERE id=?")) {
            ps.setInt(1,id);
            try(ResultSet rs=ps.executeQuery()) {
                if(!rs.next()){resp.sendError(404);return;}
                stored=rs.getString("stored_name"); original=rs.getString("original_name"); type=rs.getString("file_type");
            }
        } catch(SQLException e) { throw new ServletException(e); }
        String ext=stored.toLowerCase();
        if(!(ext.endsWith(".jpg")||ext.endsWith(".png")||ext.endsWith(".pdf"))) { resp.sendError(415); return; }
        File file=new File(getServletContext().getRealPath("/uploads"),stored);
        if(!file.isFile()){resp.sendError(404);return;}
        resp.setContentType(type); resp.setHeader("X-Content-Type-Options","nosniff");
        resp.setHeader("Content-Disposition","inline; filename=\""+original.replace("\"","")+"\"");
        try(InputStream in=new FileInputStream(file); OutputStream out=resp.getOutputStream()) {
            byte[] buffer=new byte[8192]; int n; while((n=in.read(buffer))!=-1) out.write(buffer,0,n);
        }
    }
}
