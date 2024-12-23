<?php
require_once 'cookie_management.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_cookie'])) {
        $cookie_name = $_POST['cookie_name'] ?? 'default_name';
        $cookie_value = $_POST['cookie_value'] ?? 'default_value';
        set_cookie($cookie_name, $cookie_value, 3600); // Cookie berlaku 1 jam
        $message = "Cookie '$cookie_name' telah diset dengan nilai: $cookie_value.";
    } elseif (isset($_POST['get_cookie'])) {
        $cookie_name = $_POST['cookie_name'] ?? 'default_name';
        $cookie_value = get_cookie($cookie_name);
        $message = $cookie_value ? "Cookie '$cookie_name': $cookie_value" : "Cookie '$cookie_name' tidak ditemukan.";
    } elseif (isset($_POST['delete_cookie'])) {
        $cookie_name = $_POST['cookie_name'] ?? 'default_name';
        delete_cookie($cookie_name);
        $message = "Cookie '$cookie_name' telah dihapus.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Cookie Management</title>
</head>
<body>
    <h1>Pengelolaan Cookie</h1>
    <form method="post">
        <label for="cookie_name">Nama Cookie:</label>
        <input type="text" id="cookie_name" name="cookie_name" placeholder="Masukkan nama cookie" required>
        <br>
        <label for="cookie_value">Nilai Cookie:</label>
        <input type="text" id="cookie_value" name="cookie_value" placeholder="Masukkan nilai cookie">
        <br>
        <button type="submit" name="set_cookie">Set Cookie</button>
        <button type="submit" name="get_cookie">Get Cookie</button>
        <button type="submit" name="delete_cookie">Delete Cookie</button>
    </form>
    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
</body>
</html>
