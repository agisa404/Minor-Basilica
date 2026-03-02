<?php
require_once __DIR__ . '/core.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: account_management.php?auth=register');
    exit();
}

$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$role = trim($_POST['role'] ?? 'user');

$allowedRoles = ['user', 'minister', 'priest', 'staff'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'user';
}

if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
    set_flash('danger', 'All fields are required.');
    header('Location: account_management.php?auth=register');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('danger', 'Please enter a valid email address.');
    header('Location: account_management.php?auth=register');
    exit();
}

if ($password !== $confirmPassword) {
    set_flash('danger', 'Passwords do not match.');
    header('Location: account_management.php?auth=register');
    exit();
}

if (strlen($password) < 6) {
    set_flash('danger', 'Password must be at least 6 characters.');
    header('Location: account_management.php?auth=register');
    exit();
}

$check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$check->bind_param('s', $email);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

if ($exists) {
    set_flash('danger', 'Email is already registered.');
    header('Location: account_management.php?auth=register');
    exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $fullName, $email, $hash, $role);
$stmt->execute();
$userId = (int)$stmt->insert_id;
$stmt->close();

notify_user($userId, 'Welcome to the Minor Basilica system. Your account has been created.');
set_flash('success', 'Registration successful. You can now login.');
header('Location: account_management.php?auth=login');
exit();
?>
