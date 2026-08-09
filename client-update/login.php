<?php
require __DIR__ . '/bootstrap.php';
if (!empty($_SESSION['treevolution_admin'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $validUser = hash_equals((string)$config['upload_username'], $username);
    $validPass = hash_equals((string)$config['upload_password'], $password);
    if ($validUser && $validPass) {
        session_regenerate_id(true);
        $_SESSION['treevolution_admin'] = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: index.php');
        exit;
    }
    usleep(500000);
    $error = 'Incorrect username or password.';
}
?><!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/treevolution-v6-3.css"><title>Client update | Treevolution</title></head><body class="admin"><main class="container formcard"><h1>Treevolution client update</h1><p>Add completed work and a photograph to the website repository.</p><?php if($error):?><div class="notice"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label for="username">Username</label><input id="username" type="text" name="username" required autocomplete="username"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password"><p><button class="btn btn-primary" type="submit">Sign in</button></p></form></main></body></html>
