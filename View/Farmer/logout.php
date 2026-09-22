<?php 
session_start(); 
$_SESSION=[]; 
session_destroy(); 
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" href="style.css">
        <title>Logout | Harvestly</title>
    </head>
    
    <body>
        
        <main class="main" style="margin-left:0">
            <section class="content">
                <div class="card">
                    <h1>Logged out</h1>
                    <p>Your Farmer session has ended.</p>
                    <p class="muted">Connect this button to your group's main login page when merging the full project.</p>
                </div>
            </section>
        </main>
    </body>
</html>