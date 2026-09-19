<?php
/**
 * Harvestly Core Database Connection Config
 * Uses PDO with Prepared Statements for maximum security.
 */

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'harvestly_db');
define('DB_PORT', 3306);

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // For development fallback or initial setup notice
            die("Database Connection Error: " . $e->getMessage() . "<br>Please ensure MySQL is running and <code>harvestly_db</code> is imported from <code>database/schema.sql</code>.");
        }
    }
    return $pdo;
}
