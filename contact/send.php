<?php
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed');}
if(!empty($_POST['company']??'')){http_response_code(204);exit;}
$name=trim((string)($_POST['name']??'')); $phone=trim((string)($_POST['phone']??'')); $email=trim((string)($_POST['email']??'')); $postcode=trim((string)($_POST['postcode']??'')); $service=trim((string)($_POST['service']??'')); $message=trim((string)($_POST['message']??''));
$ok=$name!=='' && $phone!=='' && filter_var($email,FILTER_VALIDATE_EMAIL) && $message!=='';
if(!$ok){http_response_code(422);$status='Please check the required fields and try again.';}else{
 $subject='Treevolution website quote request – '.$name;
 $body="Name: $name\nPhone: $phone\nEmail: $email\nPostcode/area: $postcode\nService: $service\n\n$message\n";
 $headers=['From: Treevolution Website <no-reply@treevolution.uk>','Reply-To: '.$email,'Content-Type: text/plain; charset=UTF-8'];
 $sent=@mail('info@treevolution.uk',$subject,$body,implode("\r\n",$headers));
 $status=$sent?'Thank you. Your enquiry has been sent to Treevolution.':'We could not confirm email delivery. Please call 07795 181894 or email info@treevolution.uk directly.';
}
?><!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#062d20"><link rel="icon" href="../assets/icons/favicon.ico"><link rel="stylesheet" href="../assets/css/treevolution-v7.css"><title>Quote request | Treevolution</title></head><body class="simple-page"><main class="message-card"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"><h1><?=e($status)?></h1><p><a class="btn primary" href="../">Back to website</a></p></main></body></html>