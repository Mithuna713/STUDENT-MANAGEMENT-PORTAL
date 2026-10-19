package com.college;
import java.io.IOException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;
@WebServlet("/logout")
public class LogoutServlet extends HttpServlet {
    @Override protected void doGet(HttpServletRequest req,HttpServletResponse resp) throws IOException {
        HttpSession session=req.getSession(false);
        if(session!=null) session.invalidate();
        resp.sendRedirect(req.getContextPath()+"/login.jsp");
    }
}
