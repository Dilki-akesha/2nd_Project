<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../Model/Auth/AuthModel.php';
if (empty($argv[1]) || !filter_var($argv[1],FILTER_VALIDATE_EMAIL)) exit("Usage: php scripts/issue_password_reset.php verified-account-email\nOnly issue after verifying the account owner's identity.\n");
$token=(new AuthModel())->createPasswordResetToken($argv[1]);
echo $token ? "Open the site's index.php?page=reset_password&token=".$token."\nThis single-use link expires in one hour. Provide it only to the verified account owner.\n" : "Account not found.\n";
