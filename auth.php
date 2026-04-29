<?php
// includes/auth.php — session, login, registration helpers

require_once __DIR__ . '/config.php';

session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT id, name, email, university, year, avatar FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function registerUser(string $name, string $email, string $password): array {
    $email = strtolower(trim($email));

    if (strlen($name) < 2)              return ['ok' => false, 'msg' => 'Name too short.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'msg' => 'Invalid email.'];
    if (strlen($password) < 8)          return ['ok' => false, 'msg' => 'Password must be 8+ characters.'];

    $pdo = db();
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) return ['ok' => false, 'msg' => 'Email already registered.'];

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hash]);
    $id = (int) $pdo->lastInsertId();

    // seed a default subject
    $pdo->prepare('INSERT INTO subjects (user_id, name, color, icon) VALUES (?, "General", "purple", "📓")')
        ->execute([$id]);

    $_SESSION['user_id'] = $id;
    return ['ok' => true, 'user_id' => $id];
}

function loginUser(string $email, string $password): array {
    $email = strtolower(trim($email));
    $stmt = db()->prepare('SELECT id, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($password, $row['password'])) {
        return ['ok' => false, 'msg' => 'Invalid email or password.'];
    }
    $_SESSION['user_id'] = $row['id'];
    return ['ok' => true];
}

function logoutUser(): void {
    session_destroy();
    header('Location: login.php');
    exit;
}

// CSRF token helpers
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
