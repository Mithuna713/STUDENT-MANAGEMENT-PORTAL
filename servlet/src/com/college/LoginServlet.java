package com.college;
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

@WebServlet("/login")
public class LoginServlet extends HttpServlet {
    // SHA-256 hash of demo password "ChangeMe123!".
    private static final String DEMO_HASH = "9a4aabf0e5cf71cae2cea646613ce7e2a5919fa758e56819704be25a3a2c1f0b";
    private String hash(String value) throws NoSuchAlgorithmException {
        byte[] bytes=MessageDigest.getInstance("SHA-256").digest(value.getBytes(StandardCharsets.UTF_8));
        StringBuilder out=new StringBuilder();
        for(byte b:bytes) out.append(String.format("%02x",b));
        return out.toString();
    }
    @Override protected void doPost(HttpServletRequest req,HttpServletResponse resp) throws IOException, ServletException {
        String username=req.getParameter("username"), password=req.getParameter("password");
        try {
            if ("studentadmin".equals(username) && DEMO_HASH.equals(hash(password==null?"":password))) {
                req.getSession(true).setAttribute("loggedIn", Boolean.TRUE);
                resp.sendRedirect(req.getContextPath()+"/students");
            } else {
                req.setAttribute("error","Invalid username or password.");
                req.getRequestDispatcher("/login.jsp").forward(req,resp);
            }
        } catch(NoSuchAlgorithmException e) { throw new ServletException(e); }
    }
}
