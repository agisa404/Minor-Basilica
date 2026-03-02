<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

date_default_timezone_set('Asia/Manila');

function ensure_schema(mysqli $conn): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(120) DEFAULT NULL,
        email VARCHAR(120) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        avatar_path VARCHAR(255) DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        about TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if (!column_exists($conn, 'users', 'full_name')) {
        $conn->query('ALTER TABLE users ADD COLUMN full_name VARCHAR(120) DEFAULT NULL');
    }
    if (!column_exists($conn, 'users', 'role')) {
        $conn->query('ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT "user"');
    }
    if (!column_exists($conn, 'users', 'created_at')) {
        $conn->query('ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    }
    if (!column_exists($conn, 'users', 'avatar_path')) {
        $conn->query('ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) DEFAULT NULL');
    }
    if (!column_exists($conn, 'users', 'bio')) {
        $conn->query('ALTER TABLE users ADD COLUMN bio TEXT DEFAULT NULL');
    }
    if (!column_exists($conn, 'users', 'about')) {
        $conn->query('ALTER TABLE users ADD COLUMN about TEXT DEFAULT NULL');
    }

    $conn->query("CREATE TABLE IF NOT EXISTS service_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        form_type VARCHAR(80) NOT NULL,
        title VARCHAR(180) NOT NULL,
        details LONGTEXT NOT NULL,
        requested_date DATE DEFAULT NULL,
        requested_time TIME DEFAULT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        admin_note TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_req_dt (requested_date, requested_time),
        CONSTRAINT fk_req_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        request_id INT NOT NULL,
        event_title VARCHAR(180) NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_sched_req FOREIGN KEY (request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
        UNIQUE KEY uniq_request_schedule (request_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(180) NOT NULL,
        content LONGTEXT NOT NULL,
        is_published TINYINT(1) NOT NULL DEFAULT 1,
        expires_at DATETIME DEFAULT NULL,
        created_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_announcement_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if (!column_exists($conn, 'announcements', 'expires_at')) {
        $conn->query('ALTER TABLE announcements ADD COLUMN expires_at DATETIME DEFAULT NULL AFTER is_published');
    }

    $conn->query("CREATE TABLE IF NOT EXISTS event_schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(180) NOT NULL,
        description TEXT DEFAULT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        location VARCHAR(180) DEFAULT NULL,
        qr_token VARCHAR(64) NOT NULL UNIQUE,
        created_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_schedule_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_event_dt (event_date, event_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key VARCHAR(120) PRIMARY KEY,
        setting_value TEXT DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $conn->query("CREATE TABLE IF NOT EXISTS attendance_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        schedule_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        participant_name VARCHAR(180) NOT NULL,
        participant_email VARCHAR(180) DEFAULT NULL,
        scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        source VARCHAR(30) NOT NULL DEFAULT 'qr',
        UNIQUE KEY uniq_schedule_attendee (schedule_id, participant_email),
        CONSTRAINT fk_attendance_schedule FOREIGN KEY (schedule_id) REFERENCES event_schedules(id) ON DELETE CASCADE,
        CONSTRAINT fk_attendance_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $defaultAdmin = 'admin@basilica.local';
    $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $check->bind_param('s', $defaultAdmin);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();

    if (!$exists) {
        $name = 'System Admin';
        $passwordHash = password_hash('Admin@123', PASSWORD_DEFAULT);
        $role = 'admin';
        $insert = $conn->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
        $insert->bind_param('ssss', $name, $defaultAdmin, $passwordHash, $role);
        $insert->execute();
        $insert->close();
    }

    $initialized = true;
}

ensure_schema($conn);

function column_exists(mysqli $conn, string $table, string $column): bool
{
    $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function login_user(array $user): void
{
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_role'] = $user['role'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function current_user(): ?array
{
    global $conn;

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT id, full_name, email, role, avatar_path, bio, about, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['user_role']);
        return null;
    }

    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        set_flash('danger', 'Please login first.');
        header('Location: account_management.php');
        exit();
    }
    return $user;
}

function is_admin_or_staff(?array $user = null): bool
{
    $target = $user ?: current_user();
    if (!$target) {
        return false;
    }
    return in_array($target['role'], ['admin', 'staff'], true);
}

function require_admin_or_staff(): array
{
    $user = require_login();
    if (!is_admin_or_staff($user)) {
        set_flash('danger', 'Access denied. Admin/Staff only.');
        header('Location: account_management.php');
        exit();
    }
    return $user;
}

function has_role(array $user, array $roles): bool
{
    return in_array($user['role'] ?? '', $roles, true);
}

function require_roles(array $roles, string $message = 'Access denied.'): array
{
    $user = require_login();
    if (!has_role($user, $roles)) {
        set_flash('danger', $message);
        header('Location: index.php');
        exit();
    }
    return $user;
}

function require_admin_only(): array
{
    return require_roles(['admin'], 'Access denied. Admin only.');
}

function require_priest_only(): array
{
    return require_roles(['priest'], 'Access denied. Priest only.');
}

function get_app_setting(string $key, ?string $default = null): ?string
{
    global $conn;
    static $cache = [];

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    $stmt = $conn->prepare('SELECT setting_value FROM app_settings WHERE setting_key = ? LIMIT 1');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $value = $row['setting_value'] ?? $default;
    $cache[$key] = $value;
    return $value;
}

function set_app_setting(string $key, ?string $value): void
{
    global $conn;
    $stmt = $conn->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
    $stmt->close();
}

function clear_app_setting(string $key): void
{
    global $conn;
    $stmt = $conn->prepare('DELETE FROM app_settings WHERE setting_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $stmt->close();
}

function app_now(): DateTimeImmutable
{
    $tz = new DateTimeZone(date_default_timezone_get());
    $override = get_app_setting('app_datetime_override', null);
    if ($override !== null && trim($override) !== '') {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $override, $tz);
        if ($parsed instanceof DateTimeImmutable) {
            return $parsed;
        }
        $ts = strtotime($override);
        if ($ts !== false) {
            return (new DateTimeImmutable('@' . $ts))->setTimezone($tz);
        }
    }
    return new DateTimeImmutable('now', $tz);
}

function purge_expired_announcements(): void
{
    global $conn;
    $cutoff = app_now()->format('Y-m-d H:i:s');
    $stmt = $conn->prepare('DELETE FROM announcements WHERE expires_at IS NOT NULL AND expires_at <= ?');
    $stmt->bind_param('s', $cutoff);
    $stmt->execute();
    $stmt->close();
}

function notify_user(int $userId, string $message): void
{
    global $conn;
    $stmt = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)');
    $stmt->bind_param('is', $userId, $message);
    $stmt->execute();
    $stmt->close();
}

function generate_qr_token(int $length = 40): string
{
    return bin2hex(random_bytes(max(16, (int)ceil($length / 2))));
}
?>
