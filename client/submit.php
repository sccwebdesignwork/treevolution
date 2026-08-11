<?php
require __DIR__ . '/bootstrap.php'; auth_required();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed.');} verify_csrf();
try{
    if(($_POST['orientation_confirmed']??'')!=='1') throw new RuntimeException('Please confirm that the photographs are upright before publishing.');
    $files=requested_uploads(); if(!$files||count($files)>4)throw new RuntimeException('Please upload between one and four photographs.'); validate_uploads($files);
    $title=trim((string)($_POST['title']??'')); $body=trim((string)($_POST['body']??'')); $alt=trim((string)($_POST['alt']??'')); $location=trim((string)($_POST['location']??'')); $date=(string)($_POST['date']??date('Y-m-d')); $category=trim((string)($_POST['category']??'Other tree care'));
    if(!$title||!$body||!$alt||!$location)throw new RuntimeException('Please complete all required fields.'); if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date))throw new RuntimeException('Invalid date.');
    $allowed=['Tree removal','Tree reduction','Pollarding','Hedge cutting','Stump removal','Other tree care']; if(!in_array($category,$allowed,true))$category='Other tree care';
    $slug=slugify($date . '-' . $title); $images=save_story_images($files,$date,$slug,$title,0);
    [$existing,$stories]=gh_get_json_file('content/stories.json');
    $story=['id'=>bin2hex(random_bytes(8)),'slug'=>$slug,'title'=>$title,'date'=>$date,'location'=>$location,'category'=>$category,'body'=>$body,'image'=>$images[0],'images'=>$images,'alt'=>$alt,'created'=>date('c'),'updated'=>date('c')];
    array_unshift($stories,$story); $stories=array_slice($stories,0,100);
    gh_put_text_file('content/stories.json',json_encode($stories,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)."\n",'Client story: '.$title,(string)$existing['sha']);
    gh_put_text_file(project_page_path($slug),project_page_html($story),'Create SEO project page: '.$title); update_sitemap_for_story($story,false);
    $_SESSION['csrf']=bin2hex(random_bytes(32));
}catch(Throwable $e){http_response_code(500);$error=$e->getMessage();?><!doctype html><html lang="en-GB"><head>
<link rel="icon" href="../favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="../assets/icons/apple-touch-icon.png">
<meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/site.css"><link rel="stylesheet" href="../assets/css/client-editor.css"><title>Update failed | Treevolution</title></head><body class="client-admin"><main class="client-shell client-main"><section class="client-panel"><h1>Update not published</h1><div class="notice"><?=e($error)?></div><p>The website story was not added. Please return and try again.</p><p><a class="client-button client-button-primary" href="add.php">Back to updater</a></p></section></main></body></html><?php exit;}
?><!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/site.css"><link rel="stylesheet" href="../assets/css/client-editor.css"><title>Published | Treevolution</title></head><body class="client-admin"><main class="client-shell client-main"><section class="client-panel"><h1>Update sent to GitHub</h1><p>The optimised photographs, website story, dedicated project page and sitemap entry were committed to the repository. GitHub Actions will now validate and deploy the test site.</p><p><a class="client-button client-button-primary" href="add.php">Add another</a> <a class="client-button" href="manage.php">Manage updates</a> <a class="client-button" href="../our-work/">View website</a></p></section></main></body></html>
