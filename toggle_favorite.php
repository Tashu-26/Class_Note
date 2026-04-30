<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notes.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['ok'=>false,'msg'=>'Unauthorized']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false]); exit; }
if (!verifyCsrf($_POST['csrf'] ?? '')) { echo json_encode(['ok'=>false,'msg'=>'Invalid CSRF']); exit; }
$id     = (int)($_POST['note_id'] ?? 0);
$result = toggleFavorite($id, $_SESSION['user_id']);
if ($result === null) { echo json_encode(['ok'=>false,'msg'=>'Note not found']); exit; }
echo json_encode(['ok'=>true,'is_favorite'=>$result]);
