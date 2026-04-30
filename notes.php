<?php
// includes/notes.php — CRUD for notes, subjects, tags, attachments

require_once __DIR__ . '/config.php';

// ── SUBJECTS ─────────────────────────────────────────────────────────────────

function getSubjects(int $userId): array {
    $stmt = db()->prepare(
        'SELECT s.*, COUNT(n.id) AS note_count
         FROM subjects s
         LEFT JOIN notes n ON n.subject_id = s.id
         WHERE s.user_id = ?
         GROUP BY s.id
         ORDER BY s.created_at DESC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function createSubject(int $userId, string $name, string $color = 'purple', string $icon = '📚'): int {
    $stmt = db()->prepare('INSERT INTO subjects (user_id, name, color, icon) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, trim($name), $color, $icon]);
    return (int) db()->lastInsertId();
}

function deleteSubject(int $id, int $userId): bool {
    $stmt = db()->prepare('DELETE FROM subjects WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    return $stmt->rowCount() > 0;
}

// ── NOTES ─────────────────────────────────────────────────────────────────────

function getNotes(int $userId, array $filters = []): array {
    $where = ['n.user_id = ?'];
    $params = [$userId];

    if (!empty($filters['subject_id'])) {
        $where[] = 'n.subject_id = ?';
        $params[] = (int) $filters['subject_id'];
    }
    if (!empty($filters['favorite'])) {
        $where[] = 'n.is_favorite = 1';
    }
    if (!empty($filters['search'])) {
        $where[] = 'MATCH(n.title, n.content) AGAINST(? IN BOOLEAN MODE)';
        $params[] = $filters['search'] . '*';
    }
    if (!empty($filters['tag'])) {
        $where[] = 'EXISTS (SELECT 1 FROM note_tags nt JOIN tags t ON t.id=nt.tag_id WHERE nt.note_id=n.id AND t.name=?)';
        $params[] = $filters['tag'];
    }

    $sql = 'SELECT n.*, s.name AS subject_name, s.color AS subject_color
            FROM notes n
            LEFT JOIN subjects s ON s.id = n.subject_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY n.is_pinned DESC, n.updated_at DESC';

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int)$filters['limit'];
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $notes = $stmt->fetchAll();

    // attach tags to each note
    foreach ($notes as &$note) {
        $note['tags'] = getNoteTags($note['id']);
    }
    return $notes;
}

function getNote(int $id, int $userId): ?array {
    $stmt = db()->prepare(
        'SELECT n.*, s.name AS subject_name, s.color AS subject_color
         FROM notes n
         LEFT JOIN subjects s ON s.id = n.subject_id
         WHERE n.id = ? AND n.user_id = ?'
    );
    $stmt->execute([$id, $userId]);
    $note = $stmt->fetch() ?: null;
    if ($note) {
        $note['tags']        = getNoteTags($id);
        $note['attachments'] = getNoteAttachments($id);
    }
    return $note;
}

function createNote(int $userId, string $title, ?int $subjectId, string $content = ''): int {
    $stmt = db()->prepare(
        'INSERT INTO notes (user_id, subject_id, title, content) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $subjectId ?: null, trim($title), $content]);
    return (int) db()->lastInsertId();
}

function updateNote(int $id, int $userId, array $fields): bool {
    $allowed = ['title', 'content', 'subject_id', 'is_favorite', 'is_pinned'];
    $sets    = [];
    $params  = [];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $fields)) {
            $sets[]  = "$f = ?";
            $params[] = $fields[$f];
        }
    }
    if (empty($sets)) return false;
    $params[] = $id;
    $params[] = $userId;
    $stmt = db()->prepare('UPDATE notes SET ' . implode(', ', $sets) . ' WHERE id = ? AND user_id = ?');
    $stmt->execute($params);
    return $stmt->rowCount() > 0;
}

function deleteNote(int $id, int $userId): bool {
    $stmt = db()->prepare('DELETE FROM notes WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    return $stmt->rowCount() > 0;
}

function toggleFavorite(int $id, int $userId): ?bool {
    $stmt = db()->prepare('SELECT is_favorite FROM notes WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();
    if (!$row) return null;
    $newVal = $row['is_favorite'] ? 0 : 1;
    db()->prepare('UPDATE notes SET is_favorite = ? WHERE id = ? AND user_id = ?')
        ->execute([$newVal, $id, $userId]);
    return (bool) $newVal;
}

// ── TAGS ──────────────────────────────────────────────────────────────────────

function getNoteTags(int $noteId): array {
    $stmt = db()->prepare('SELECT t.name FROM tags t JOIN note_tags nt ON nt.tag_id=t.id WHERE nt.note_id=?');
    $stmt->execute([$noteId]);
    return array_column($stmt->fetchAll(), 'name');
}

function syncTags(int $noteId, int $userId, array $tagNames): void {
    db()->prepare('DELETE FROM note_tags WHERE note_id = ?')->execute([$noteId]);
    foreach (array_unique($tagNames) as $name) {
        $name = strtolower(trim($name));
        if (!$name) continue;
        $pdo = db();
        $pdo->prepare('INSERT IGNORE INTO tags (user_id, name) VALUES (?, ?)')->execute([$userId, $name]);
        $tagId = (int) $pdo->query("SELECT id FROM tags WHERE user_id=$userId AND name=" . $pdo->quote($name))->fetchColumn();
        $pdo->prepare('INSERT IGNORE INTO note_tags (note_id, tag_id) VALUES (?, ?)')->execute([$noteId, $tagId]);
    }
}

// ── ATTACHMENTS ───────────────────────────────────────────────────────────────

function getNoteAttachments(int $noteId): array {
    $stmt = db()->prepare('SELECT * FROM attachments WHERE note_id = ? ORDER BY created_at DESC');
    $stmt->execute([$noteId]);
    return $stmt->fetchAll();
}

function saveAttachment(int $noteId, int $userId, array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) return ['ok' => false, 'msg' => 'Upload error.'];
    if ($file['size'] > MAX_FILE_SIZE)     return ['ok' => false, 'msg' => 'File too large (max 10 MB).'];
    if (!in_array($file['type'], ALLOWED_TYPES)) return ['ok' => false, 'msg' => 'File type not allowed.'];

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $stored   = uniqid('att_', true) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $stored;

    if (!move_uploaded_file($file['tmp_name'], $dest)) return ['ok' => false, 'msg' => 'Could not save file.'];

    db()->prepare(
        'INSERT INTO attachments (note_id, user_id, filename, filepath, filetype, filesize) VALUES (?,?,?,?,?,?)'
    )->execute([$noteId, $userId, $file['name'], $stored, $file['type'], $file['size']]);

    return ['ok' => true, 'filename' => $file['name'], 'stored' => $stored];
}

function deleteAttachment(int $id, int $userId): bool {
    $stmt = db()->prepare('SELECT filepath FROM attachments WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();
    if (!$row) return false;
    @unlink(UPLOAD_DIR . $row['filepath']);
    db()->prepare('DELETE FROM attachments WHERE id = ?')->execute([$id]);
    return true;
}

// ── DASHBOARD STATS ───────────────────────────────────────────────────────────

function getDashboardStats(int $userId): array {
    $pdo = db();
    $stats = [
        'total_notes'     => 'SELECT COUNT(*) FROM notes WHERE user_id = ?',
        'total_subjects'  => 'SELECT COUNT(*) FROM subjects WHERE user_id = ?',
        'total_favorites' => 'SELECT COUNT(*) FROM notes WHERE user_id = ? AND is_favorite = 1',
        'total_files'     => 'SELECT COUNT(*) FROM attachments WHERE user_id = ?',
    ];

    $results = [];
    foreach ($stats as $key => $sql) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $results[$key] = (int)$stmt->fetchColumn();
    }
    return $results;
}
