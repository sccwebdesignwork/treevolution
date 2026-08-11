<?php require __DIR__ . '/bootstrap.php'; auth_required(); ?><!doctype html>
<html lang="en-GB">
<head>
<link rel="icon" href="../assets/icons/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/apple-touch-icon.png">

<meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#082a1c">
<link rel="stylesheet" href="../assets/css/treevolution-v6-4.css">
<link rel="stylesheet" href="../assets/css/client-update.css">
<title>Add website update | Treevolution</title>
</head>
<body class="client-admin">
<header class="client-header"><div class="client-shell client-header-inner">
<a class="client-brand" href="../" aria-label="Treevolution website"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"></a>
<div class="client-header-actions"><a class="client-signout" href="../client/index.php">Dashboard</a><span class="client-area-label">Client update area</span><a class="client-signout" href="manage.php">Manage updates</a><a class="client-signout" href="logout.php">Sign out</a></div>
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
<div class="client-field client-field-span-2"><label for="location">Town / area</label><input id="location" name="location" maxlength="80" required placeholder="e.g. Lewes, East Sussex"><small>Use a town or public area only — never a customer's exact address.</small></div><div class="client-field"><label for="category">Category</label><select id="category" name="category"><option>Tree removal</option><option>Tree reduction</option><option>Pollarding</option><option>Hedge cutting</option><option>Stump removal</option><option>Other tree care</option></select></div>
</div>
</section>

<section class="client-panel">
<div class="client-panel-heading"><span class="client-step">2</span><div><h2>Tell the story</h2><p>A short factual description is ideal.</p></div></div>
<div class="client-field"><label for="body">Project story</label><textarea id="body" name="body" maxlength="1600" required placeholder="What was the job, roughly where was it, and what was achieved?"></textarea><div class="client-field-help"><small>Avoid customer names, exact private addresses or unnecessary personal details. For search visibility, aim for roughly 100–250 useful words: what tree, what issue, what work was carried out and the outcome.</small><small><span id="storyCount">0</span>/1600</small></div></div>
</section>

<section class="client-panel">
<div class="client-panel-heading"><span class="client-step">3</span><div><h2>Add photographs</h2><p>Choose up to four strong images from the same completed job.</p></div></div>
<div class="client-upload-grid">
<div>
<label class="client-upload-box" for="image"><span class="client-upload-icon" aria-hidden="true">↑</span><strong>Choose project photographs</strong><span>JPG, PNG or WebP · up to 4 images · maximum 8MB each</span><input id="image" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
<label class="client-check"><input type="checkbox" name="orientation_confirmed" value="1" required><span>I confirm the selected photographs are upright and correctly orientated.</span></label>
</div>
<div class="client-upload-preview" id="uploadPreview" hidden><div class="client-preview-label">Image previews</div><div id="previewImages" class="client-preview-grid"></div></div>
</div>
</section>

<section class="client-panel">
<div class="client-panel-heading"><span class="client-step">4</span><div><h2>Accessibility description</h2><p>Describe what is visible across the selected photographs for visitors using assistive technology.</p></div></div>
<div class="client-field"><label for="alt">Photograph description</label><input id="alt" name="alt" maxlength="160" required placeholder="e.g. Completed beech tree reduction in a Lewes garden"><small>Describe the job shown in the images. If several photographs are selected, the website labels their order automatically.</small></div>
</section>

<section class="client-publish-bar">
<div><strong>Ready to add this update?</strong><span>The website deployment starts automatically after a successful publication.</span></div>
<button class="client-button client-button-primary" type="submit">Publish website update <span aria-hidden="true">→</span></button>
</section>
</form>
</main>
<footer class="client-footer"><div class="client-shell"><p>Treevolution · Client website update area</p></div></footer>
<script>
const input=document.getElementById('image'),box=document.getElementById('uploadPreview'),previewGrid=document.getElementById('previewImages');let objectUrls=[];
if(input&&box&&previewGrid){input.addEventListener('change',()=>{objectUrls.forEach(u=>URL.revokeObjectURL(u));objectUrls=[];previewGrid.innerHTML='';const files=[...(input.files||[])];if(files.length>4){alert('Please choose no more than four photographs.');input.value='';box.hidden=true;return;}if(!files.length){box.hidden=true;return;}files.forEach((f,n)=>{const u=URL.createObjectURL(f);objectUrls.push(u);const wrap=document.createElement('figure');const img=document.createElement('img');img.src=u;img.alt='Preview '+(n+1)+' of '+files.length;const cap=document.createElement('figcaption');cap.textContent='Photo '+(n+1);wrap.append(img,cap);previewGrid.appendChild(wrap);});box.hidden=false;});}
const story=document.getElementById('body'),count=document.getElementById('storyCount');if(story&&count){const u=()=>count.textContent=story.value.length;story.addEventListener('input',u);u();}
</script>
</body></html>
