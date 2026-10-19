<%@ page contentType="text/html;charset=UTF-8" %>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Portal Login</title><link rel="stylesheet" href="style.css"></head><body><main class="login">
<div class="brand">KPR <span>STUDENT PORTAL</span></div><h1>Admin Login</h1><p>Sign in to view student profiles and documents.</p>
<% if(request.getAttribute("error")!=null){ %><p class="error"><%=request.getAttribute("error")%></p><% } %>
<form method="post" action="login"><label>Username<input name="username" required autocomplete="username"></label><label>Password<input name="password" type="password" required autocomplete="current-password"></label><button>Sign in</button></form>
<p class="small">Demo account: studentadmin / ChangeMe123!</p></main></body></html>
