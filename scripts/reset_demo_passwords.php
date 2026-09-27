<?php
/**
 * Reset the demo account passwords to a known value.
 * Local development helper only.
 *
 * Usage: php scripts/reset_demo_passwords.php
 */
require_once __DIR__ . '/../config/app.php';

$password = 'TestPass123!';
$emails = [
    'admin@harvestly.lk',
    'buyer@gmail.com',
    'farmer@harvestly.lk',
    'courier@lankaagro.lk',
    'tharushi@harvestly.lk',
];

$hash = password_hash($password, PASSWORD_BCRYPT);
foreach ($emails as $email) {
    $ok = db_execute('UPDATE users SET password_hash = ? WHERE email = ?', 'ss', [$hash, $email]);
    echo ($ok ? 'updated ' : 'skipped ') . $email . PHP_EOL;
}

// Remove smoke-test accounts from previous runs.
$deleted = db_execute(
    "DELETE FROM users WHERE email LIKE 'smoke%' OR email LIKE 'dbg%'"
);
echo 'cleaned smoke/dbg accounts: ' . ($deleted ? 'yes' : 'none') . PHP_EOL;

echo PHP_EOL . 'All demo accounts now use the password: ' . $password . PHP_EOL;
