const form = document.getElementById('registrationForm');
const fields = ['name','roll','email','course','password','file'];
function error(id, message){ document.getElementById(id+'Error').textContent = message; }
form.addEventListener('submit', function(e){
  e.preventDefault();
  fields.forEach(id => error(id,''));
  document.getElementById('result').textContent = '';
  let valid = true;
  const name = document.getElementById('name').value.trim();
  const roll = document.getElementById('roll').value.trim();
  const email = document.getElementById('email').value.trim();
  const course = document.getElementById('course').value;
  const password = document.getElementById('password').value;
  const fileInput = document.getElementById('file');
  if(name.length < 2){error('name','Enter a valid full name.');valid=false;}
  if(!/^[A-Za-z0-9/-]{3,20}$/.test(roll)){error('roll','Use 3–20 letters, numbers, / or -.');valid=false;}
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){error('email','Enter a valid email address.');valid=false;}
  if(!course){error('course','Please select a course.');valid=false;}
  if(!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password)){
    error('password','Use 8+ characters with upper/lowercase, number and symbol.');valid=false;
  }
  if(fileInput.files.length){
    const file = fileInput.files[0];
    if(!/\.(jpg|jpeg|png|pdf)$/i.test(file.name)){error('file','Only JPG, PNG or PDF files are allowed.');valid=false;}
    if(file.size > 2*1024*1024){error('file','File must be 2 MB or smaller.');valid=false;}
  }
  if(valid) document.getElementById('result').textContent = 'Validation successful! Demo registration is complete.';
});
