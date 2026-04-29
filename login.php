<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $result = loginUser($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['ok']) {
            header('Location: dashboard.php');
            exit;
        }
        $error = $result['msg'];
    }
}

htmlHead('Login');
?>
<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-left">
      <div class="sidebar-logo" style="margin-bottom:32px;padding:0">
        <div class="logo-icon">N</div>
        <span class="logo-text" style="color:#fff">NoteOrg</span>
      </div>
      <h2>Welcome back!</h2>
      <p>Your organized notes are waiting.</p>
      <div style="margin-top:32px;display:flex;flex-direction:column;gap:14px">
        <?php foreach (['📚 All notes, perfectly organized','⭐ Quick access to favorites','🔍 Search across all subjects'] as $f): ?>
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:30px;height:30px;background:rgba(255,255,255,.15);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
            <?= mb_substr($f,0,2) ?>
          </div>
          <span style="font-size:13px;color:var(--p100)"><?= htmlspecialchars(mb_substr($f,3)) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="auth-right">
      <h3>Log in</h3>
      <p>No account? <a href="signup.php">Sign up free →</a></p>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST" action="login.php">
        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
        <div class="form-group">
          <label class="form-label">Email address</label>
          <input class="form-control" type="email" name="email" required placeholder="you@university.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-control" type="password" name="password" required placeholder="••••••••">
        </div>
        <div style="display:flex;justify-content:flex-end;margin-bottom:16px">
          <a href="forgot.php" style="font-size:12.5px">Forgot password?</a>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Log in</button>
      </form>
    </div>

  </div>
</div>
<?php htmlFoot(); ?>
