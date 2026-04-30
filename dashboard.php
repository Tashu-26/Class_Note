<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/notes.php';
require_once __DIR__ . '/includes/layout.php';
requireLogin();

$user    = currentUser();
$stats   = getDashboardStats($user['id']);
$recent  = getNotes($user['id'], ['limit' => 6]);   // latest 6
$subjects = getSubjects($user['id']);

$colors = ['purple'=>'var(--p600)','teal'=>'var(--t400)','amber'=>'var(--a400)','coral'=>'var(--c400)'];

htmlHead('Dashboard');
?>
<div class="app-layout">
  <?php sidebar('dashboard'); ?>
  <main class="main">
    <?php flashSession(); ?>
    <div class="page-header">
      <div>
        <div class="page-title">Good day, <?= htmlspecialchars(explode(' ', trim($user['name']))[0]) ?> 👋</div>
        <div class="page-sub">Here's what's happening with your notes</div>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <form action="notes.php" method="GET" class="search-wrap">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="var(--txt3)"><circle cx="6.5" cy="6.5" r="5"/><path d="M10.5 10.5l4 4" stroke="var(--txt3)" stroke-width="1.5" stroke-linecap="round"/></svg>
          <input id="search-input" name="search" placeholder="Search notes…" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </form>
        <button class="theme-toggle" onclick="toggleTheme()">☾ Dark</button>
      </div>
    </div>

    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-label">Total Notes</div>
        <div class="stat-num"><?= $stats['total_notes'] ?></div>
        <span class="stat-tag" style="background:var(--t50);color:var(--t600)">All time</span>
      </div>
      <div class="stat-card">
        <div class="stat-label">Subjects</div>
        <div class="stat-num"><?= $stats['total_subjects'] ?></div>
        <span class="stat-tag" style="background:var(--p100);color:var(--p800)">Active</span>
      </div>
      <div class="stat-card">
        <div class="stat-label">Favorites</div>
        <div class="stat-num"><?= $stats['total_favorites'] ?></div>
        <span class="stat-tag" style="background:var(--a50);color:var(--a400)">★ Starred</span>
      </div>
      <div class="stat-card">
        <div class="stat-label">Files</div>
        <div class="stat-num"><?= $stats['total_files'] ?></div>
        <span class="stat-tag" style="background:var(--c50);color:var(--c400)">PDF & IMG</span>
      </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
      <h2 style="font-size:16px;font-weight:600;color:var(--txt)">Recent notes</h2>
      <a href="notes.php" class="btn btn-sm">View all →</a>
    </div>

    <?php if (empty($recent)): ?>
      <div class="card" style="text-align:center;padding:40px">
        <div style="font-size:40px;margin-bottom:12px">📝</div>
        <p style="color:var(--txt2);margin-bottom:16px">No notes yet. Create your first one!</p>
        <a href="/note_edit.php" class="btn btn-primary">+ New note</a>
      </div>
    <?php else: ?>
    <div class="notes-grid">
      <?php foreach ($recent as $note):
        $tagColors = ['teal','amber','coral',''];
        $subColor  = $colors[$note['subject_color'] ?? 'purple'] ?? 'var(--p600)';
      ?>
      <a href="/note_edit.php?id=<?= $note['id'] ?>" class="card note-card" style="text-decoration:none">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px">
          <div class="note-title"><?= htmlspecialchars($note['title']) ?></div>
          <button class="star-btn <?= $note['is_favorite'] ? 'active' : '' ?>"
                  data-note-id="<?= $note['id'] ?>"
                  onclick="event.preventDefault();event.stopPropagation()">
            <?= $note['is_favorite'] ? '★' : '☆' ?>
          </button>
        </div>
        <div class="note-meta">
          <?= htmlspecialchars($note['subject_name'] ?? 'Uncategorized') ?>
          · <?= date('M j', strtotime($note['updated_at'])) ?>
        </div>
        <?php if ($note['content']): ?>
        <div class="note-body"><?= htmlspecialchars(substr(strip_tags($note['content']), 0, 160)) ?></div>
        <?php endif; ?>
        <div class="note-footer">
          <div class="tags">
            <?php foreach (array_slice($note['tags'], 0, 3) as $i => $tag): ?>
            <span class="tag <?= $tagColors[$i % count($tagColors)] !== '' ? 'tag-'.$tagColors[$i] : '' ?>"><?= htmlspecialchars($tag) ?></span>
            <?php endforeach; ?>
          </div>
          <?php if ($note['is_favorite']): ?>
          <span class="stat-tag" style="background:#FEF3C7;color:#B45309;font-size:10px">★ Fav</span>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($subjects)): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin:28px 0 14px">
      <h2 style="font-size:16px;font-weight:600;color:var(--txt)">Your subjects</h2>
      <a href="subjects.php" class="btn btn-sm">Manage →</a>
    </div>
    <div class="subjects-grid">
      <?php foreach ($subjects as $s): ?>
      <a href="notes.php?subject=<?= $s['id'] ?>" class="subject-card subject-<?= htmlspecialchars($s['color']) ?>" style="text-decoration:none">
        <div class="subject-icon"><?= htmlspecialchars($s['icon']) ?></div>
        <div class="subject-name"><?= htmlspecialchars($s['name']) ?></div>
        <div class="subject-count"><?= $s['note_count'] ?> notes</div>
        <div class="subject-bar"></div>
      </a>
      <?php endforeach; ?>
      <a href="subjects.php#new" class="subject-card" style="background:var(--bg3);border:1px dashed var(--border2);text-decoration:none;display:flex;flex-direction:column;align-items:flex-start;justify-content:center">
        <div class="subject-icon" style="font-size:22px;color:var(--txt3)">+</div>
        <div class="subject-name" style="color:var(--txt3)">Add subject</div>
      </a>
    </div>
    <?php endif; ?>
  </main>
</div>
<script>window._csrf = "<?= csrfToken() ?>";</script>
<?php htmlFoot(); ?>
