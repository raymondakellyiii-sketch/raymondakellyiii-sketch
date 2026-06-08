<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: game.php');
    exit;
}

// Login information
$username = 'User';
$password = 'Passw0rd';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u === $username && $p === $password) {
        $_SESSION['user'] = $u;
        header('Location: game.php');
        exit;
    } else {
        $error = 'Invalid credentials. Try User / Passw0rd.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="utf-8">
    <title>Craps Login</title>
    <link rel="stylesheet" href="stylesheet.css">
    </head>
    <body>
        <div class="container">
            <h1>Craps</h1>
            <p>Login to play</p>

            <?php if ($error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <label>Username
                    <input type="text" name="username" required>
                </label>
                <label>Password
                    <input type="password" name="password" required>
                </label>
                <button type="submit">Sign in</button>
            </form>
            <p>Use username <strong style="color: red">User</strong> and password <strong style="color: red">Passw0rd</strong></p>
        </div>
    </body>
</html>