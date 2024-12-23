<?php
session_start();

// Ambil data session untuk jumlah klik
$login_count = $_SESSION['login_count'] ?? 0;
$register_count = $_SESSION['register_count'] ?? 0;

if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: state_management.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Page Counts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .counter {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            margin-left: auto;
            margin-right: auto;
        }
        h1, p {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Page Visit Counts</h1>
    <div class="counter">
        <p><strong>Login Page Click Count:</strong> <?= $login_count ?></p>
        <p><strong>Register Page Click Count:</strong> <?= $register_count ?></p>
    </div>
    <p style="text-align: center;"><a href="login.php">Back to Login</a> | <a href="register.php">Back to Register</a></p>
    <p style="text-align: center;"><a href="state_management.php?reset=true">Reset Counts</a></p>
</body>
</html>
