<?php
require __DIR__ . '/../client-update/bootstrap.php';
auth_required();
?><!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow,noarchive">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#082a1c">
<title>Dashboard | Treevolution Website Manager</title>
<link rel="icon" href="../assets/icons/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/apple-touch-icon.png">
<link rel="manifest" href="manifest.webmanifest">
<link rel="stylesheet" href="../assets/css/treevolution-v6-4.css">
<link rel="stylesheet" href="../assets/css/client-portal.css">
</head>
<body class="portal-body">
<header class="portal-header"><div class="portal-shell portal-header-inner">
<a class="portal-brand" href="index.php"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"></a>
<nav class="portal-nav" aria-label="Client portal"><a aria-current="page" href="index.php">Dashboard</a><a href="../client-update/manage.php">Updates</a><a href="performance.php">Performance</a><a href="logout.php">Sign out</a></nav>
</div></header>
<main class="portal-shell portal-main">
<section class="portal-hero">
<div><p class="portal-kicker">Treevolution Website Manager</p><h1>Your website,<br>in one place.</h1><p class="portal-lead">Preview the website, keep completed project content fresh and see how people are finding Treevolution online.</p></div>
<div class="portal-status-card"><span class="portal-status-dot" aria-hidden="true"></span><div><strong>Website manager ready</strong><span>Protected client access</span></div></div>
</section>

<section class="portal-action-grid" aria-label="Website management shortcuts">
<a class="portal-action-card portal-action-featured" href="../" target="_blank" rel="noopener"><span class="portal-action-number">01</span><div><p>Website preview</p><h2>View test website</h2><span>Open the current approved test build in a new tab.</span></div><b aria-hidden="true">↗</b></a>
<a class="portal-action-card" href="../client-update/index.php"><span class="portal-action-number">02</span><div><p>Website content</p><h2>Add completed job</h2><span>Publish a project story with up to four optimised photographs.</span></div><b aria-hidden="true">→</b></a>
<a class="portal-action-card" href="../client-update/manage.php"><span class="portal-action-number">03</span><div><p>Website content</p><h2>Manage updates</h2><span>Edit or remove existing client-created project posts.</span></div><b aria-hidden="true">→</b></a>
<a class="portal-action-card" href="performance.php"><span class="portal-action-number">04</span><div><p>Website insight</p><h2>Website performance</h2><span>View the client-friendly Google Analytics dashboard.</span></div><b aria-hidden="true">→</b></a>
</section>

<section class="portal-info-grid">
<article class="portal-panel"><p class="portal-kicker">Content guidance</p><h2>Projects that help customers — and search visibility.</h2><p>For each completed job, describe the tree, the issue, what Treevolution did and the outcome. Use a town or area, never a customer’s exact address.</p><a class="portal-text-link" href="../client-update/index.php">Add a project <span aria-hidden="true">→</span></a></article>
<article class="portal-panel portal-panel-dark"><p class="portal-kicker">Publishing status</p><h2>Test environment</h2><p>This portal currently manages the protected test website. The public website will only be switched after client approval and final pre-live checks.</p></article>
</section>
</main>
<footer class="portal-footer"><div class="portal-shell portal-footer-inner"><p>Treevolution · Professional tree care across Lewes and Sussex</p><p>Website by <a href="https://sccwebdesign.co.uk/" target="_blank" rel="noopener">SCC Webdesign</a></p></div></footer>
</body></html>
