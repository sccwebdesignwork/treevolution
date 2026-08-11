<?php
require __DIR__ . '/../client-update/bootstrap.php';
auth_required();
$embedFile = __DIR__ . '/looker-embed-url.txt';
$lookerUrl = is_file($embedFile) ? trim((string)file_get_contents($embedFile)) : '';
$lookerReady = (bool)preg_match('~^https://lookerstudio\.google\.com/embed/reporting/[A-Za-z0-9_-]+~', $lookerUrl);
?><!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow,noarchive">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#082a1c">
<title>Website performance | Treevolution Website Manager</title>
<link rel="icon" href="../assets/icons/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/apple-touch-icon.png">
<link rel="manifest" href="manifest.webmanifest">
<link rel="stylesheet" href="../assets/css/treevolution-v7.css">
<link rel="stylesheet" href="../assets/css/client-portal.css">
</head>
<body class="portal-body">
<header class="portal-header"><div class="portal-shell portal-header-inner">
<a class="portal-brand" href="index.php"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"></a>
<nav class="portal-nav" aria-label="Client portal"><a href="index.php">Dashboard</a><a href="../client-update/manage.php">Updates</a><a aria-current="page" href="performance.php">Performance</a><a href="logout.php">Sign out</a></nav>
</div></header>
<main class="portal-shell portal-main">
<section class="portal-hero portal-hero-compact"><div><p class="portal-kicker">Website insight</p><h1>Website performance</h1><p class="portal-lead">A clear view of how people are discovering and using the Treevolution website.</p></div><div class="portal-status-card"><span class="portal-status-dot" aria-hidden="true"></span><div><strong><?= $lookerReady ? 'Analytics connected' : 'Analytics panel prepared' ?></strong><span><?= $lookerReady ? 'Interactive report below' : 'Awaiting Looker Studio embed URL' ?></span></div></div></section>

<section class="portal-report-shell">
<?php if ($lookerReady): ?>
<div class="portal-report-topbar"><div><strong>Treevolution website performance</strong><span>Use the date control inside the report to change the reporting period.</span></div><a href="<?=e($lookerUrl)?>" target="_blank" rel="noopener">Open full report ↗</a></div>
<div class="portal-report-frame"><iframe src="<?=e($lookerUrl)?>" title="Treevolution website performance report" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
<?php else: ?>
<div class="portal-report-empty"><span class="portal-report-icon" aria-hidden="true">↗</span><p class="portal-kicker">Looker Studio</p><h2>Your performance dashboard is ready to connect.</h2><p>Create the Treevolution Looker Studio report, enable embedding, then replace the placeholder inside <code>client/looker-embed-url.txt</code> with the Google embed URL.</p><div class="portal-metric-preview"><div><strong>Users</strong><span>Last 30 days</span></div><div><strong>Sessions</strong><span>vs previous period</span></div><div><strong>Page views</strong><span>Top content</span></div><div><strong>Google traffic</strong><span>Organic search</span></div></div></div>
<?php endif; ?>
</section>

<section class="portal-info-grid portal-info-grid-three">
<article class="portal-panel"><p class="portal-kicker">Overview</p><h2>What we will show</h2><p>Users, sessions, page views, new visitors and a simple 30-day activity trend.</p></article>
<article class="portal-panel"><p class="portal-kicker">Discovery</p><h2>How people arrive</h2><p>Organic search, direct visits, referrals and social traffic in plain client language.</p></article>
<article class="portal-panel"><p class="portal-kicker">Content</p><h2>What people view</h2><p>Top pages and completed-project stories, helping show whether regular updates are working.</p></article>
</section>
</main>
<footer class="portal-footer"><div class="portal-shell portal-footer-inner"><p>Treevolution Website Manager</p><p>Website by <a href="https://sccwebdesign.co.uk/" target="_blank" rel="noopener">SCC Webdesign</a></p></div></footer>
</body></html>
