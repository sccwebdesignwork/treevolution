<?php require __DIR__ . '/bootstrap.php'; auth_required(); ?><!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#082a1c">
<link rel="stylesheet" href="../assets/css/treevolution-v6-4.css">
<link rel="stylesheet" href="../assets/css/client-update.css">
<title>Add website update | Treevolution</title>
</head>
<body class="client-admin">
<header class="client-header"><div class="client-shell client-header-inner">
<a class="client-brand" href="../" aria-label="Treevolution website"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"></a>
<div class="client-header-actions"><span class="client-area-label">Client update area</span><a class="client-signout" href="logout.php">Sign out</a></div>
</div></header>
<main class="client-shell client-main">
<section class="client-page-heading">
<div><p class="client-kicker">Website updates</p><h1>Add a completed job</h1><p class="client-lead">Add a recent project and photograph. Your image will be optimised automatically and prepared for publication on the website.</p></div>
<div class="client-status-card"><span class="client-status-dot" aria-hidden="true"></span><div><strong>Ready to publish</strong><span>Complete the details below</span></div></div>
</section>
<form action="submit.php" method="post" enctype="multipart/form-data" class="client-update-form">
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">

<section class="client-panel">
<div class="client-panel-heading"><span class="client-step">1</span><div><h2>Project details</h2><p>Give the job a clear title, date and category.</p></div></div>
<div class="client-grid client-grid-3">
<div class="client-field client-field-span-2"><label for="title">Story title</label><input id="title" name="title" maxlength="90" required placeholder="e.g. Beech reduction in Lewes"><small>Keep this short and specific.</small></div>
<div class="client-field"><label for="date">Date completed</label><input id="date" type="date" name="date" value="<?=date('Y-m-d')?>" required></div>
<div class="client-field client-field-span-3"><label for="category">Category</label><select id="category" name="category"><option>Tree removal</option><option>Tree reduction</option><option>Pollarding</option><option>Hedge cutting</option><option>Stump removal</option><option>Other tree care</option></select></div>
</div>
</section>

<section class="client-panel">
<div class="client-panel-heading"><span class="client-step">2</span><div><h2>Tell the story</h2><p>A short factual description is ideal.</p></div></div>
<div class="client-field"><label for="body">Project story</label><textarea id="body" name="body" maxlength="1200" required placeholder="What was the job, roughly where was it, and what was achieved?"></textarea><div class="client-field-help"><small>Avoid customer names, exact private addresses or unnecessary personal details.</small><small><span id="storyCount">0</span>/1200</small></div></div>
</section>

<section class="client-panel">
<div class="client-panel-heading"><span class="client-step">3</span><div><h2>Add a photograph</h2><p>Choose one strong image that shows the completed work clearly.</p></div></div>
<div class="client-upload-grid">
<div>
<label class="client-upload-box" for="image"><span class="client-upload-icon" aria-hidden="true">↑</span><strong>Choose project photograph</strong><span>JPG, PNG or WebP · maximum 8MB</span><input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" required></label>
<label class="client-check"><input type="checkbox" name="orientation_confirmed" value="1" required><span>I confirm this photograph is upright and correctly orientated.</span></label>
</div>
<div class="client-upload-preview" id="uploadPreview" hidden><div class="client-preview-label">Image preview</div><img id="previewImage" alt="Preview of selected upload"></div>
</div>
</section>

<section class="client-panel">
<div class="client-panel-heading"><span class="client-step">4</span><div><h2>Accessibility description</h2><p>Describe what is visible in the photograph for visitors using assistive technology.</p></div></div>
<div class="client-field"><label for="alt">Image description</label><input id="alt" name="alt" maxlength="160" required placeholder="e.g. Completed beech tree reduction in a Lewes garden"><small>Describe the image itself, not promotional wording.</small></div>
</section>

<section class="client-publish-bar">
<div><strong>Ready to add this update?</strong><span>The website deployment starts automatically after a successful publication.</span></div>
<button class="client-button client-button-primary" type="submit">Publish website update <span aria-hidden="true">→</span></button>
</section>
</form>
</main>
<footer class="client-footer"><div class="client-shell"><p>Treevolution · Client website update area</p></div></footer>
<script>
const input=document.getElementById('image'),box=document.getElementById('uploadPreview'),preview=document.getElementById('previewImage');let objectUrl=null;
if(input&&box&&preview){input.addEventListener('change',()=>{const f=input.files&&input.files[0];if(objectUrl){URL.revokeObjectURL(objectUrl);objectUrl=null;}if(!f){box.hidden=true;preview.removeAttribute('src');return;}objectUrl=URL.createObjectURL(f);preview.src=objectUrl;box.hidden=false;});}
const story=document.getElementById('body'),count=document.getElementById('storyCount');if(story&&count){const u=()=>count.textContent=story.value.length;story.addEventListener('input',u);u();}
</script>
</body></html>
