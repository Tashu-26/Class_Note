<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notes.php';
require_once __DIR__ . '/includes/layout.php';
requireLogin();

$user = currentUser();
$uid  = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    if (isset($_POST['new_subject'])) {
        $name  = trim($_POST['name'] ?? '');
        $color = $_POST['color'] ?? 'purple';
        $icon  = $_POST['icon']  ?? '📚';
        if ($name) { createSubject($uid, $name, $color, $icon); setFlash('Subject created!'); }
    } elseif (isset($_POST['delete_subject'])) {
        deleteSubject((int)$_POST['delete_subject'], $uid);
        setFlash('Subject deleted.');
    }
    header('Location: subjects.php'); exit;
}

$subjects = getSubjects($uid);
$colors   = ['purple','teal','amber','coral'];
$icons    = ['📚','🧮','🌐','🗄️','🔌','🤖','📐','🔬','📖','🎯'];

htmlHead('Subjects');
?>
<div class="app-layout">
  <?php sidebar('subjects'); ?>
  <main class="main">
    <?php flashSession(); ?>
    <div class="page-header">
      <div>
        <div class="page-title">Subjects</div>
        <div class="page-sub"><?= count($subjects) ?> subject<?= count($subjects) !== 1 ? 's' : '' ?> this semester</div>
      </div>
    </div>

    <div class="subjects-grid" style="margin-bottom:28px">
      <?php foreach ($subjects as $s): ?>
      <div class="subject-card subject-<?= htmlspecialchars($s['color']) ?>" style="position:relative">
        <div class="subject-icon"><?= htmlspecialchars($s['icon']) ?></div>
        <div class="subject-name"><?= htmlspecialchars($s['name']) ?></div>
        <div class="subject-count"><?= $s['note_count'] ?> notes</div>
        <div class="subject-bar"></div>
        <div style="position:absolute;top:10px;right:10px;display:flex;gap:6px">
          <a href="notes.php?subject=<?= $s['id'] ?>" class="btn btn-sm" style="padding:3px 8px;font-size:11px">View</a>
          <form method="POST" style="display:inline">
            <input type="hidden" name="csrf"           value="<?= csrfToken() ?>">
            <input type="hidden" name="delete_subject" value="<?= $s['id'] ?>">
            <button type="submit" class="btn btn-sm delete-note-btn" style="padding:3px 8px;font-size:11px;color:var(--c400);border-color:var(--c400)" title="Delete subject">✕</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Add new subject -->
    <div class="card" id="new" style="max-width:520px">
      <div style="font-size:16px;font-weight:600;color:var(--txt);margin-bottom:16px">Add new subject</div>
      <form method="POST">
        <input type="hidden" name="csrf"        value="<?= csrfToken() ?>">
        <input type="hidden" name="new_subject" value="1">
        <div class="form-group">
          <label class="form-label">Subject name</label>
          <input class="form-control" name="name" required placeholder="e.g. Data Structures">
        </div>
        <div class="form-group">
          <label class="form-label">Icon</label>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <?php foreach ($icons as $i => $ico): ?>
            <label style="cursor:pointer;font-size:22px" title="<?= $ico ?>">
              <input type="radio" name="icon" value="<?= $ico ?>" <?= $i === 0 ? 'checked' : '' ?> style="display:none">
              <span style="padding:4px;border-radius:6px;border:1.5px solid transparent;display:inline-block" class="icon-opt"><?= $ico ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Color</label>
          <div style="display:flex;gap:10px">
            <?php $colorMap = ['purple'=>'#7F77DD','teal'=>'#1D9E75','amber'=>'#BA7517','coral'=>'#D85A30'];
            foreach ($colorMap as $key => $hex): ?>
            <label style="cursor:pointer;display:flex;align-items:center;gap:6px;font-size:13px;color:var(--txt2)">
              <input type="radio" name="color" value="<?= $key ?>" <?= $key === 'purple' ? 'checked' : '' ?>>
              <span style="width:16px;height:16px;border-radius:50%;background:<?= $hex ?>;display:inline-block"></span>
              <?= ucfirst($key) ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="btn btn-primary" type="submit">Create subject</button>
      </form>
    </div>
  </main>
</div>
<script>
// highlight selected icon
document.querySelectorAll('input[name="icon"]').forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.icon-opt').forEach(s => s.style.borderColor = 'transparent');
    r.nextElementSibling.style.borderColor = 'var(--p400)';
  });
});
if (document.querySelector('input[name="icon"]:checked'))
  document.querySelector('input[name="icon"]:checked').nextElementSibling.style.borderColor = 'var(--p400)';
</script>
<?php htmlFoot(); ?>
