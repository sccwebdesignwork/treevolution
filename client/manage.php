<?php
require __DIR__ . '/bootstrap.php';
auth_required();
[$storiesFile, $stories] = gh_get_json_file('content/stories.json');
?><!doctype html><html lang="en-GB"><head>
<link rel="icon" href="../favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/apple-touch-icon.png">
<meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#082a1c"><link rel="stylesheet" href="../assets/css/site.css"><link rel="stylesheet" href="../assets/css/client-editor.css"><title>Manage website updates | Treevolution</title></head><body class="client-admin">
<header class="client-header"><div class="client-shell client-header-inner"><a class="client-brand" href="../"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"></a><div class="client-header-actions"><a class="client-signout" href="index.php">Dashboard</a><a class="client-signout" href="add.php">Add update</a><a class="client-signout" href="logout.php">Sign out</a></div></div></header>
<main class="client-shell client-main"><section class="client-page-heading"><div><p class="client-kicker">Website updates</p><h1>Manage published jobs</h1><p class="client-lead">Edit wording, location and photographs, or remove a post you no longer want shown.</p></div></section>
<section class="client-panel"><div class="client-manage-list">
<?php if (!$stories): ?><p>No client updates have been published yet.</p><?php endif; ?>
<?php foreach ($stories as $story): $key=story_key($story); $imgs=$story['images']??(!empty($story['image'])?[$story['image']]:[]); ?>
<article class="client-manage-card"><?php if(!empty($imgs[0])):?><img src="../<?=e(ltrim((string)$imgs[0],'/'))?>" alt="" loading="lazy"><?php endif; ?><div class="client-manage-copy"><p class="client-kicker"><?=e((string)($story['category']??'Tree care'))?><?php if(!empty($story['location'])):?> · <?=e((string)$story['location'])?><?php endif;?></p><h2><?=e((string)($story['title']??'Website update'))?></h2><p><?=e((string)($story['date']??''))?></p></div><div class="client-manage-actions"><a class="client-button" href="edit.php?id=<?=urlencode($key)?>">Edit</a><form action="delete.php" method="post" onsubmit="return confirm('Delete this website update? This cannot be undone.');"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=e($key)?>"><button class="client-button client-button-danger" type="submit">Delete</button></form></div></article>
<?php endforeach; ?></div></section></main></body></html>
