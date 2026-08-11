<?php
require __DIR__ . '/bootstrap.php'; auth_required();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed.');} verify_csrf();
$id=(string)($_POST['id']??''); [$file,$stories]=gh_get_json_file('content/stories.json'); $found=null; $remaining=[];
foreach($stories as $s){if(story_key($s)===$id){$found=$s;}else{$remaining[]=$s;}}
if(!$found)exit('Update not found.');
gh_put_text_file('content/stories.json',json_encode($remaining,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n",'Delete client story: '.($found['title']??''),(string)$file['sha']);
$imgs=$found['images']??(!empty($found['image'])?[$found['image']]:[]); if(is_array($imgs)){foreach($imgs as $img)gh_delete_file_if_exists((string)$img,'Delete image for client story: '.($found['title']??''));}
$slug=slugify((string)($found['slug']??$found['title']??'project')); gh_delete_file_if_exists(project_page_path($slug),'Delete project page: '.($found['title']??'')); update_sitemap_for_story($found,true);
$_SESSION['csrf']=bin2hex(random_bytes(32)); header('Location: manage.php');
