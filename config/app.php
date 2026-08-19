<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/database.php';

function db(): PDO { return Database::connection(); }
function currentBuyerId(): int {
    if (!empty($_SESSION['buyer_id'])) return (int)$_SESSION['buyer_id'];
    $email = trim((string)($_SESSION['buyer_email'] ?? 'demo@harvestly.local'));
    $stmt = db()->prepare('SELECT id FROM users WHERE email=?');
    $stmt->execute([$email]);
    $id = (int)$stmt->fetchColumn();
    if (!$id) {
        $stmt=db()->prepare('INSERT INTO users(name,email) VALUES(?,?)');
        $stmt->execute([$_SESSION['buyer_name'] ?? 'Guest Buyer', $email]);
        $id=(int)db()->lastInsertId();
    }
    $_SESSION['buyer_id']=$id;
    return $id;
}

const APP_NAME = 'Harvestly';
const BASE_URL = '/Harvestly';

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function post_string(string $key, string $default = ''): string
{
    return trim((string)($_POST[$key] ?? $default));
}


function buyerRoute(string $controller, string $query = ''): string
{
    $path = 'Controller/Buyer/' . ltrim($controller, '/');
    return url($path . ($query !== '' ? ('?' . ltrim($query, '?')) : ''));
}
