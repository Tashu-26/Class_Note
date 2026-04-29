<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } elseif ($_POST['password'] !== $_POST['password_confirm']) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['ok']) {
            header('Location: dashboard.php');
            exit;
        }
        $error = $result['msg'];
    }
}

htmlHead('Sign Up');
?>
<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-left">
      <div class="sidebar-logo" style="margin-bottom:32px;padding:0">
        <div class="logo-icon">N</div>
        <span class="logo-text" style="color:#fff">NoteOrg</span>
      </div>
      <h2>Start organizing today</h2>
      <p>Join students who never lose a note again.</p>
      <div style="margin-top:32px;display:flex;flex-direction:column;gap:14px">
        <?php foreach (['🆓 Free forever for students','📁 Unlimited subjects & notes','🔒 Private and secure'] as $f): ?>
        <div style="display:flex;align-items:center;gap:12px">
          <div style="width:30px;height:30px;background:rgba(255,255,255,.15);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0"><?= mb_substr($f,0,2) ?></div>
          <span style="font-size:13px;color:var(--p100)"><?= htmlspecialchars(mb_substr($f,3)) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="auth-right">
      <h3>Create account</h3>
      <p>Already registered? <a href="login.php">Log in here →</a></p>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST" action="signup.php">
        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
        <div class="form-group">
          <label class="form-label">Full name</label>
          <input class="form-control" name="name" required placeholder="Aria Rahman" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email address</label>
          <input class="form-control" type="email" name="email" required placeholder="you@university.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-control" type="password" name="password" required placeholder="Min 8 characters" minlength="8">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm password</label>
          <input class="form-control" type="password" name="password_confirm" required placeholder="••••••••">
        </div>
        <p style="font-size:11.5px;color:var(--txt3);margin-bottom:14px">By signing up you agree to our <a href="#">Terms</a> & <a href="#">Privacy Policy</a>.</p>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Create account</button>
      </form>
    </div>

  </div>
</div>
<?php htmlFoot(); ?>
