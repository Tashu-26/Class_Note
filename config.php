<?php
// includes/config.php — database & app settings

define('DB_HOST', 'localhost');
define('DB_NAME', 'noteorg');
define('DB_USER', 'root');   // XAMPP default
define('DB_PASS', '');       // leave empty for XAMPP
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'NoteOrg');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024);  // 10 MB
define('ALLOWED_TYPES', ['image/jpeg','image/png','image/gif','application/pdf',
                          'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// PDO connection (singleton)
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
