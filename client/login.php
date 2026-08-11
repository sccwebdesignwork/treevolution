<?php
require __DIR__ . '/../client-update/bootstrap.php';
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
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow,noarchive">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#082a1c">
<title>Client login | Treevolution Website Manager</title>
<link rel="icon" href="../assets/icons/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/apple-touch-icon.png">
<link rel="manifest" href="manifest.webmanifest">
<link rel="stylesheet" href="../assets/css/treevolution-v7.css">
<link rel="stylesheet" href="../assets/css/client-portal.css">
</head>
<body class="portal-body portal-login-page">
<header class="portal-header"><div class="portal-shell portal-header-inner">
<a class="portal-brand" href="../" aria-label="Treevolution website"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"></a>
<span class="portal-eyebrow">Website Manager</span>
</div></header>
<main class="portal-shell portal-login-main">
<section class="portal-login-card" aria-labelledby="login-title">
<div class="portal-login-mark"><img src="../assets/branding/treevolution-tree-mark.svg" alt="" aria-hidden="true"></div>
<p class="portal-kicker">Private client portal</p>
<h1 id="login-title">Welcome back</h1>
<p class="portal-lead">Sign in to preview your website, manage completed projects and view website performance.</p>
<?php if ($error): ?><div class="portal-notice portal-notice-error" role="alert"><?=e($error)?></div><?php endif; ?>
<form method="post" class="portal-form">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label class="portal-field" for="username"><span>Username</span><input id="username" type="text" name="username" required autocomplete="username" autofocus></label>
<label class="portal-field" for="password"><span>Password</span><span class="portal-password-wrap"><input id="password" type="password" name="password" required autocomplete="current-password"><button type="button" class="portal-password-toggle" data-password-toggle aria-label="Show password">Show</button></span></label>
<button class="portal-button portal-button-primary portal-button-full" type="submit">Sign in <span aria-hidden="true">→</span></button>
</form>
<p class="portal-security"><span aria-hidden="true">●</span> Secure client area · excluded from search indexing</p>
</section>
</main>
<footer class="portal-footer"><div class="portal-shell"><p>Treevolution Website Manager · Website by <a href="https://sccwebdesign.co.uk/" target="_blank" rel="noopener">SCC Webdesign</a></p></div></footer>
<script>const b=document.querySelector('[data-password-toggle]'),p=document.getElementById('password');if(b&&p){b.addEventListener('click',()=>{const s=p.type==='password';p.type=s?'text':'password';b.textContent=s?'Hide':'Show';b.setAttribute('aria-label',s?'Hide password':'Show password');});}</script>
</body></html>
