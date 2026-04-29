<?php
// includes/layout.php — shared header, sidebar, footer helpers

function htmlHead(string $title = 'NoteOrg'): void { ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?> — NoteOrg</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script>
    (function(){ var t=localStorage.getItem('theme')||'light';
      document.documentElement.setAttribute('data-theme',t); })();
  </script>
</head>
<body>
<?php } ?>

<?php
function sidebar(string $active = 'dashboard'): void {
    $user = currentUser();
    $nameParts = array_filter(explode(' ', trim($user['name'])));
    $initials = '';
    foreach (array_slice($nameParts, 0, 2) as $part) {
        $initials .= strtoupper($part[0]);
    }
    $nav = [
        ['href' => 'dashboard.php', 'key' => 'dashboard', 'label' => 'Dashboard',
         'icon' => '<rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/>'],
        ['href' => 'notes.php',     'key' => 'notes',     'label' => 'My Notes',
         'icon' => '<path d="M3 2h8l3 3v9H3V2z"/><path d="M11 2v4h4"/>'],
        ['href' => 'subjects.php',  'key' => 'subjects',  'label' => 'Subjects',
         'icon' => '<rect x="1" y="1" width="14" height="4" rx="1"/><rect x="1" y="7" width="14" height="4" rx="1"/><rect x="1" y="13" width="8" height="2" rx="1"/>'],
        ['href' => 'notes.php?filter=favorites', 'key' => 'favorites', 'label' => 'Favorites',
         'icon' => '<path d="M8 2l1.5 4.5H14l-3.8 2.8 1.5 4.5L8 11l-3.7 2.8 1.5-4.5L2 6.5h4.5z"/>'],
    ]; ?>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">N</div>
      <span class="logo-text">NoteOrg</span>
    </div>
    <span class="nav-section">Menu</span>
    <?php foreach ($nav as $item): ?>
    <a href="<?= $item['href'] ?>" class="nav-item <?= $active === $item['key'] ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="currentColor"><?= $item['icon'] ?></svg>
      <?= $item['label'] ?>
    </a>
    <?php endforeach; ?>
    <span class="nav-section">Account</span>
    <a href="profile.php"  class="nav-item <?= $active === 'profile' ? 'active' : '' ?>">
      <svg viewBox="0 0 16 16" fill="currentColor"><circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.3 2.7-6 6-6s6 2.7 6 6"/></svg>
      Profile
    </a>
    <a href="logout.php" class="nav-item" style="color:var(--c400)">
      <svg viewBox="0 0 16 16" fill="currentColor"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l4-4-4-4M14 7H6"/></svg>
      Logout
    </a>
    <div class="sidebar-user">
      <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      <div>
        <div class="avatar-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="avatar-email"><?= htmlspecialchars($user['email']) ?></div>
      </div>
    </div>
  </aside>
<?php } ?>

<?php
function htmlFoot(): void { ?>
  <script src="assets/js/app.js"></script>
</body>
</html>
<?php } ?>

<?php
function flashSession(): void {
    if (!empty($_SESSION['flash'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash'], $_SESSION['flash_type']);
    }
}

function setFlash(string $msg, string $type = 'success'): void {
    $_SESSION['flash']      = $msg;
    $_SESSION['flash_type'] = $type;
}
