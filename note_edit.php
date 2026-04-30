<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notes.php';
require_once __DIR__ . '/includes/layout.php';
requireLogin();

$user = currentUser();
$uid  = $user['id'];
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$note = $id ? getNote($id, $uid) : null;

if ($id && !$note) { header('Location: /notes.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $title     = trim($_POST['title'] ?? '');
        $content   = trim($_POST['content'] ?? '');
        $subjectId = (int)($_POST['subject_id'] ?? 0) ?: null;
        $tagInput  = trim($_POST['tags'] ?? '');
        $tagList   = $tagInput ? array_map('trim', explode(',', $tagInput)) : [];

        if (!$title) { $error = 'Title is required.'; }
        else {
            if ($note) {
                updateNote($id, $uid, ['title'=>$title,'content'=>$content,'subject_id'=>$subjectId]);
                syncTags($id, $uid, $tagList);
                $noteId = $id;
            } else {
                $noteId = createNote($uid, $title, $subjectId, $content);
                syncTags($noteId, $uid, $tagList);
            }
            // handle file upload
            if (!empty($_FILES['attachment']['name'])) {
                $res = saveAttachment($noteId, $uid, $_FILES['attachment']);
                if (!$res['ok']) $error = $res['msg'];
            }
            if (!$error) {
                setFlash('Note ' . ($note ? 'updated' : 'created') . ' successfully!');
                header('Location: /note_edit.php?id=' . $noteId);
                exit;
            }
        }
    }
}

$subjects    = getSubjects($uid);
$attachments = $note ? getNoteAttachments($note['id']) : [];
$tagString   = $note ? implode(', ', $note['tags']) : '';

htmlHead($note ? 'Edit Note' : 'New Note');
?>
<div class="app-layout">
  <?php sidebar('notes'); ?>
  <main class="main">
    <?php flashSession(); ?>
    <div class="page-header">
      <div>
        <div class="page-title"><?= $note ? 'Edit note' : 'New note' ?></div>
        <div class="page-sub"><?= $note ? htmlspecialchars($note['title']) : 'Create a new note' ?></div>
      </div>
      <div style="display:flex;gap:10px">
        <a href="notes.php" class="btn">← Back</a>
        <?php if ($note): ?>
        <button class="star-btn <?= $note['is_favorite'] ? 'active' : '' ?>" data-note-id="<?= $note['id'] ?>" style="font-size:20px;padding:4px 8px" title="Toggle favorite">
          <?= $note['is_favorite'] ? '★' : '☆' ?>
        </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
      <div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:flex-start">

        <div>
          <div class="form-group">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" required placeholder="Note title…"
                   value="<?= htmlspecialchars($_POST['title'] ?? $note['title'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Content</label>
            <textarea class="form-control" name="content" style="min-height:280px" placeholder="Write your notes here…"><?= htmlspecialchars($_POST['content'] ?? $note['content'] ?? '') ?></textarea>
          </div>
          <button class="btn btn-primary" type="submit"><?= $note ? 'Save changes' : 'Create note' ?></button>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px">
          <!-- Subject -->
          <div class="card" style="padding:16px">
            <div style="font-size:13px;font-weight:600;color:var(--txt);margin-bottom:10px">Subject</div>
            <select class="form-control" name="subject_id">
              <option value="">— Uncategorized —</option>
              <?php foreach ($subjects as $s): ?>
              <option value="<?= $s['id'] ?>" <?= ($note['subject_id'] ?? 0) == $s['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['icon'] . ' ' . $s['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Tags -->
          <div class="card" style="padding:16px">
            <div style="font-size:13px;font-weight:600;color:var(--txt);margin-bottom:10px">Tags</div>
            <input class="form-control" name="tags" placeholder="dsa, trees, exam…"
                   value="<?= htmlspecialchars($_POST['tags'] ?? $tagString) ?>">
            <div style="font-size:11px;color:var(--txt3);margin-top:5px">Comma-separated tags</div>
          </div>

          <!-- File upload -->
          <div class="card" style="padding:16px">
            <div style="font-size:13px;font-weight:600;color:var(--txt);margin-bottom:10px">Attach file</div>
            <div class="upload-zone" style="padding:16px;margin-bottom:0">
              <div style="font-size:22px;color:var(--p400)">📎</div>
              <p style="font-size:12px">Click or drag file here</p>
              <small>PDF, PNG, JPG, DOCX</small>
              <input type="file" id="file-input" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.docx" style="display:none">
            </div>
            <?php if (!empty($attachments)): ?>
            <div style="margin-top:12px;display:flex;flex-direction:column;gap:10px">
              <?php foreach ($attachments as $att):
                $isImg = strpos($att['filetype'], 'image/') === 0;
              ?>
              <div style="display:flex;flex-direction:column;gap:6px;padding:10px;background:var(--bg2);border-radius:var(--r);border:1px solid var(--border)">
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px">
                  <span style="display:flex;align-items:center;gap:6px;min-width:0">
                    <span style="font-size:16px"><?= $isImg ? '🖼️' : '📄' ?></span>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($att['filename']) ?></span>
                  </span>
                  <a href="delete_attachment.php?id=<?= $att['id'] ?>&csrf=<?= csrfToken() ?>" style="color:var(--c400);padding:2px 6px" title="Remove">✕</a>
                </div>
                <?php if ($isImg): ?>
                <a href="uploads/<?= $att['filepath'] ?>" target="_blank">
                  <img src="uploads/<?= $att['filepath'] ?>" style="width:100%;height:100px;object-fit:cover;border-radius:4px;border:1px solid var(--border2);background:#fff">
                </a>
                <?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>

          <?php if ($note): ?>
          <div style="font-size:11px;color:var(--txt3);padding:0 4px">
            Created <?= date('M j, Y', strtotime($note['created_at'])) ?><br>
            Updated <?= date('M j, Y g:ia', strtotime($note['updated_at'])) ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </main>
</div>
<script>window._csrf = "<?= csrfToken() ?>";</script>
<?php htmlFoot(); ?>
