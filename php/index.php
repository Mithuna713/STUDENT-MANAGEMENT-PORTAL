<?php
session_start();
require __DIR__ . '/config.php';
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0750, true);
$msg = '';
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
  if ($action === 'delete' && $id) {
    $pdo->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
    $msg = 'Student record deleted.';
  } elseif (in_array($action, ['add','edit'], true)) {
    $name = trim($_POST['name'] ?? '');
    $roll = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    if ($name === '' || $roll === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $course === '') {
      $msg = 'Please provide a name, roll number, valid email and course.';
    } else {
      try {
        if ($action === 'add') {
          $stmt = $pdo->prepare('INSERT INTO students(name,roll_number,email,course) VALUES(?,?,?,?)');
          $stmt->execute([$name,$roll,$email,$course]); $id = (int)$pdo->lastInsertId();
          $msg = 'Student added successfully.';
        } else {
          $pdo->prepare('UPDATE students SET name=?,roll_number=?,email=?,course=? WHERE id=?')->execute([$name,$roll,$email,$course,$id]);
          $msg = 'Student details updated.';
        }
        if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
          $f = $_FILES['document'];
          if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed.');
          if ($f['size'] > 2*1024*1024) throw new RuntimeException('File exceeds 2 MB.');
          $finfo = new finfo(FILEINFO_MIME_TYPE); $mime = $finfo->file($f['tmp_name']);
          $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf'];
          if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG and PDF files are allowed.');
          $stored = bin2hex(random_bytes(16)).'.'.$allowed[$mime];
          if (!move_uploaded_file($f['tmp_name'], $uploadDir.$stored)) throw new RuntimeException('Could not save uploaded file.');
          $pdo->prepare('INSERT INTO student_files(student_id,original_name,stored_name,file_path,file_type) VALUES(?,?,?,?,?)')
              ->execute([$id, basename($f['name']), $stored, 'uploads/'.$stored, $mime]);
          $msg .= ' File uploaded.';
        }
      } catch (Throwable $e) {
        $msg = ($e instanceof PDOException) ? 'Could not save record. Check for duplicate roll number.' : $e->getMessage();
      }
    }
  }
}
$edit = null;
if (isset($_GET['edit'])) {
  $stmt=$pdo->prepare('SELECT * FROM students WHERE id=?'); $stmt->execute([(int)$_GET['edit']]); $edit=$stmt->fetch();
}
$students=$pdo->query('SELECT * FROM students ORDER BY id DESC')->fetchAll();
$files=$pdo->query('SELECT * FROM student_files ORDER BY upload_date DESC')->fetchAll();
$filesByStudent=[]; foreach($files as $f) $filesByStudent[$f['student_id']][]=$f;
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Student Admin Panel</title><link rel="stylesheet" href="style.css"></head><body>
<header><div><b>KPR</b> STUDENT ADMIN PANEL</div></header><main>
<h1>Student Records</h1><?php if($msg): ?><p class="notice"><?=h($msg)?></p><?php endif; ?>
<section class="panel"><h2><?=$edit?'Edit Student':'Add Student'?></h2>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="action" value="<?=$edit?'edit':'add'?>"><input type="hidden" name="id" value="<?=h($edit['id']??'')?>">
<div class="fields">
<label>Full name<input name="name" required value="<?=h($edit['name']??'')?>"></label>
<label>Roll number<input name="roll_number" required value="<?=h($edit['roll_number']??'')?>"></label>
<label>Email<input name="email" type="email" required value="<?=h($edit['email']??'')?>"></label>
<label>Course<input name="course" required value="<?=h($edit['course']??'')?>"></label>
<label class="wide">Profile image / ID proof / certificate (JPG, PNG, PDF; max 2 MB)<input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf"></label>
</div><button type="submit"><?=$edit?'Update Student':'Add Student'?></button><?php if($edit): ?> <a href="index.php">Cancel</a><?php endif; ?>
</form></section>
<section class="panel"><h2>Student List</h2><div class="table-wrap"><table><thead><tr><th>Name</th><th>Roll No.</th><th>Email</th><th>Course</th><th>Files</th><th>Actions</th></tr></thead><tbody>
<?php foreach($students as $s): ?><tr><td><?=h($s['name'])?></td><td><?=h($s['roll_number'])?></td><td><?=h($s['email'])?></td><td><?=h($s['course'])?></td><td>
<?php foreach(($filesByStudent[$s['id']]??[]) as $f): ?><a href="download.php?id=<?=$f['id']?>"><?=h($f['original_name'])?></a><br><small><?=h($f['file_type'])?> · <?=h($f['upload_date'])?></small><br><?php endforeach; ?>
</td><td><a href="?edit=<?=$s['id']?>">Edit</a><form class="inline" method="post" onsubmit="return confirm('Delete this student and linked file records?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$s['id']?>"><button class="danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$students): ?><tr><td colspan="6">No student records yet.</td></tr><?php endif; ?>
</tbody></table></div></section></main></body></html>
