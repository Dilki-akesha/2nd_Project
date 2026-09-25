<?php
session_start();
$_SESSION = [];
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logged Out — Harvestly</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .logout-box {
            background: white;
            padding: 48px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            text-align: center;
            max-width: 420px;
            width: 90%;
        }
        .logout-box .icon-wrap {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #e8f5e9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logout-box i {
            font-size: 40px;
            color: #0d631b;
        }
        .logout-box h2 {
            margin: 0 0 12px;
            color: #191c1d;
            font-size: 22px;
            font-weight: 600;
        }
        .logout-box p {
            color: #6b7280;
            margin: 0 0 28px;
            font-size: 14px;
            line-height: 1.6;
        }
        .logout-box a {
            display: inline-block;
            padding: 12px 32px;
            background: #0d631b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .logout-box a:hover {
            background: #005312;
        }
    </style>
</head>
<body>
    <div class="logout-box">
        <div class="icon-wrap">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Logged Out Successfully</h2>
        <p>You have been safely logged out of your Courier Partner account.</p>
        <a href="index.php">
            <i class="fas fa-arrow-left"></i> Return to Dashboard (Demo)
        </a>
    </div>
</body>
</html>