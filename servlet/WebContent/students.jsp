<%@ page contentType="text/html;charset=UTF-8" %><%@ page import="java.util.*,com.college.Student,com.college.StudentServlet.FileInfo" %>
<% if(session==null || !Boolean.TRUE.equals(session.getAttribute("loggedIn"))){response.sendRedirect("login.jsp");return;} %>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Student Profiles</title><link rel="stylesheet" href="style.css"></head><body>
<header><b>KPR</b> STUDENT PROFILE PORTAL <a href="logout">Log out</a></header><main class="content"><h1>Student Profiles</h1><p class="muted">Student information and linked files retrieved dynamically from MySQL.</p>
<div class="cards"><% List<Student> students=(List<Student>)request.getAttribute("students"); Map<Integer,List<FileInfo>> files=(Map<Integer,List<FileInfo>>)request.getAttribute("files"); if(students!=null && !students.isEmpty()){ for(Student s:students){ %>
<article class="student"><div class="avatar"><%=s.getName().substring(0,1).toUpperCase()%></div><h2><%=esc(s.getName())%></h2><p><b>Roll number:</b> <%=esc(s.getRollNumber())%></p><p><b>Email:</b> <%=esc(s.getEmail())%></p><p><b>Course:</b> <%=esc(s.getCourse())%></p><h3>Uploaded files</h3>
<% List<FileInfo> list=files.get(s.getId()); if(list!=null && !list.isEmpty()){ for(FileInfo f:list){ if(f.getType()!=null && f.getType().startsWith("image/")){ %><img class="profile-image" src="file?id=<%=f.getId()%>" alt="Uploaded image for <%=esc(s.getName())%>"><% } %><p><a href="file?id=<%=f.getId()%>" target="_blank" rel="noopener"><%=esc(f.getName())%></a> <small>(<%=esc(f.getType())%>)</small></p><% }}else{%><p class="muted">No uploaded files.</p><%}%>
</article><% }}else{%><p>No students found. Add a student record in the PHP admin panel first.</p><%}%></div></main></body></html>
<%!
private String esc(String s){ if(s==null)return ""; return s.replace("&","&amp;").replace("<","&lt;").replace(">","&gt;").replace("\"","&quot;").replace("'","&#39;");}
%>
