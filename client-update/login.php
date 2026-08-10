<?php
require __DIR__ . '/bootstrap.php';
if (!empty($_SESSION['treevolution_admin'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (hash_equals((string)$config['upload_username'], $username) && hash_equals((string)$config['upload_password'], $password)) {
        session_regenerate_id(true);
        $_SESSION['treevolution_admin'] = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: index.php'); exit;
    }
    usleep(500000);
    $error = 'Incorrect username or password.';
}
?><!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#082a1c">
<link rel="stylesheet" href="../assets/css/treevolution-v6-4.css">
<link rel="stylesheet" href="../assets/css/client-update.css">
<title>Client login | Treevolution</title>
</head>
<body class="client-admin client-login-page">
<header class="client-header"><div class="client-shell client-header-inner">
<a class="client-brand" href="../" aria-label="Treevolution website"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"></a>
<span class="client-area-label">Client update area</span>
</div></header>
<main class="client-shell client-login-main">
<section class="client-login-card" aria-labelledby="login-title">
<p class="client-kicker">Website updates</p>
<h1 id="login-title">Welcome back</h1>
<p class="client-lead">Sign in to add a completed job and photograph to the Treevolution website.</p>
<?php if ($error): ?><div class="client-notice client-notice-error" role="alert"><?=e($error)?></div><?php endif; ?>
<form method="post" class="client-form client-login-form">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="client-field"><label for="username">Username</label><input id="username" type="text" name="username" required autocomplete="username" autofocus></div>
<div class="client-field"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
<button class="client-button client-button-primary client-button-full" type="submit">Sign in <span aria-hidden="true">→</span></button>
</form>
<p class="client-security-note"><span aria-hidden="true">●</span> Secure client area · Not indexed by search engines</p>
</section>
</main>
<footer class="client-footer"><div class="client-shell"><p>Treevolution · Professional tree care across Lewes and Sussex</p></div></footer>
</body></html>
