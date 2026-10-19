<?php
require __DIR__ . '/config.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid file ID'); }
$stmt = $pdo->prepare('SELECT * FROM student_files WHERE id=?'); $stmt->execute([$id]); $file=$stmt->fetch();
if (!$file) { http_response_code(404); exit('File not found'); }
$path = __DIR__ . '/' . $file['file_path'];
if (!is_file($path)) { http_response_code(404); exit('Stored file is missing'); }
$allowed = ['image/jpeg','image/png','application/pdf'];
if (!in_array($file['file_type'], $allowed, true)) { http_response_code(415); exit('Unsupported file type'); }
header('Content-Type: '.$file['file_type']);
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="'.str_replace('"','',basename($file['original_name'])).'"');
header('Content-Length: '.filesize($path));
readfile($path);
