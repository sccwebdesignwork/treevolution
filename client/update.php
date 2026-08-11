<?php
require __DIR__ . '/bootstrap.php'; auth_required();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed.');} verify_csrf();
try{
    $id=(string)($_POST['id']??''); [$file,$stories]=gh_get_json_file('content/stories.json'); $idx=null;
    foreach($stories as $i=>$s){if(story_key($s)===$id){$idx=$i;break;}}
    if($idx===null) throw new RuntimeException('Update not found.');
    $old=$stories[$idx]; $title=trim((string)($_POST['title']??'')); $date=(string)($_POST['date']??''); $location=trim((string)($_POST['location']??'')); $category=trim((string)($_POST['category']??'')); $body=trim((string)($_POST['body']??'')); $alt=trim((string)($_POST['alt']??''));
    if(!$title||!$body||!$alt||!$location) throw new RuntimeException('Please complete all required fields.');
    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) throw new RuntimeException('Invalid date.');
    $allowed=['Tree removal','Tree reduction','Pollarding','Hedge cutting','Stump removal','Other tree care']; if(!in_array($category,$allowed,true))$category='Other tree care';
    $oldImages=$old['images']??(!empty($old['image'])?[$old['image']]:[]); if(!is_array($oldImages))$oldImages=[]; $remove=$_POST['remove_images']??[]; if(!is_array($remove))$remove=[];
    $kept=array_values(array_filter($oldImages,fn($p)=>!in_array($p,$remove,true))); $files=requested_uploads(); validate_uploads($files); if(count($kept)+count($files)>4)throw new RuntimeException('A post can contain no more than four photographs.'); if(count($kept)+count($files)<1)throw new RuntimeException('Please keep or upload at least one photograph.');
    $slug=slugify($date . '-' . $title); $newImages=save_story_images($files,$date,$slug,$title,count($kept)); $images=array_merge($kept,$newImages);
    $story=['id'=>(string)($old['id']??story_key($old)),'slug'=>$slug,'title'=>$title,'date'=>$date,'location'=>$location,'category'=>$category,'body'=>$body,'image'=>$images[0],'images'=>$images,'alt'=>$alt,'updated'=>date('c')];
    $stories[$idx]=$story; gh_put_text_file('content/stories.json',json_encode($stories,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n",'Edit client story: '.$title,(string)$file['sha']);
    foreach($remove as $p){if(in_array($p,$oldImages,true))gh_delete_file_if_exists((string)$p,'Remove image from client story: '.$title);}
    $oldSlug=slugify((string)($old['slug']??$old['title']??'project')); if($oldSlug!==$slug)gh_delete_file_if_exists(project_page_path($oldSlug),'Rename project page: '.$title);
    $pagePath=project_page_path($slug); $sha=null; global $config; try{$existing=gh_request('GET',$pagePath.'?ref='.rawurlencode($config['github_branch']));$sha=(string)($existing['sha']??'');}catch(RuntimeException $e){if(strpos($e->getMessage(),'GitHub API error 404:')===false)throw $e;}
    gh_put_text_file($pagePath,project_page_html($story),'Update SEO project page: '.$title,$sha?:null); if($oldSlug!==$slug)update_sitemap_for_story($old,true); update_sitemap_for_story($story,false);
    $_SESSION['csrf']=bin2hex(random_bytes(32)); header('Location: manage.php'); exit;
}catch(Throwable $e){http_response_code(500);$error=$e->getMessage();?><!doctype html><html lang="en-GB"><head>
<link rel="icon" href="../favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/apple-touch-icon.png">
<meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/site.css"><link rel="stylesheet" href="../assets/css/client-editor.css"><title>Update failed | Treevolution</title></head><body class="client-admin"><main class="client-shell client-main"><section class="client-panel"><h1>Changes not published</h1><div class="notice"><?=e($error)?></div><p><a class="client-button client-button-primary" href="manage.php">Back to manage</a></p></section></main></body></html><?php }
