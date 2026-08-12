<?php
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function fail_status(string $message,int $code=422): void { http_response_code($code); render_status($message); exit; }
function render_status(string $status): void { ?>
<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#062d20"><link rel="icon" href="../favicon.ico"><link rel="stylesheet" href="../assets/css/site.css?v=20260812-0715"><title>Quote request | Treevolution</title></head><body class="simple-page"><main class="message-card"><img src="../assets/branding/treevolution-logo.svg" alt="Treevolution"><h1><?=e($status)?></h1><p><a class="btn primary" href="../">Back to website</a></p></main></body></html>
<?php }
function rate_limit_ok(): bool {
    $ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');
    $file=rtrim(sys_get_temp_dir(),DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'treevolution-contact-'.hash('sha256',$ip).'.json';
    $fh=@fopen($file,'c+');
    if(!$fh) return true; // Do not break genuine enquiries if temp storage is unavailable.
    $ok=true;
    if(flock($fh,LOCK_EX)){
        rewind($fh); $raw=stream_get_contents($fh); $hits=json_decode((string)$raw,true); if(!is_array($hits))$hits=[];
        $now=time(); $hits=array_values(array_filter($hits,fn($t)=>is_int($t)&&$t>$now-600));
        if(count($hits)>=4){$ok=false;}else{$hits[]=$now; ftruncate($fh,0); rewind($fh); fwrite($fh,json_encode($hits)); fflush($fh);} flock($fh,LOCK_UN);
    }
    fclose($fh); return $ok;
}
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed');}
// Hidden honeypot: bots that fill every field are silently discarded.
if(!empty($_POST['company']??'')){http_response_code(204);exit;}
// Time trap: the real form is timestamped by site.js. Instant or stale submissions are treated as automated.
$started=(string)($_POST['started']??'');
if(!ctype_digit($started)) fail_status('Please reload the contact page and try again.');
$age=(int)round(microtime(true)*1000)-(int)$started;
if($age<2500 || $age>7200000) fail_status('Please reload the contact page and try again.');
$name=trim((string)($_POST['name']??'')); $phone=trim((string)($_POST['phone']??'')); $email=trim((string)($_POST['email']??'')); $postcode=trim((string)($_POST['postcode']??'')); $service=trim((string)($_POST['service']??'')); $message=trim((string)($_POST['message']??''));
if(preg_match('/[\r\n]/',$name.$email)) fail_status('Please check your name and email address and try again.');
$ok=$name!=='' && mb_strlen($name)<=100 && $phone!=='' && mb_strlen($phone)<=40 && filter_var($email,FILTER_VALIDATE_EMAIL) && mb_strlen($email)<=150 && mb_strlen($postcode)<=30 && $message!=='' && mb_strlen($message)>=10 && mb_strlen($message)<=2500;
if(!$ok) fail_status('Please check the required fields and try again.');
if(!rate_limit_ok()) fail_status('Too many quote requests were submitted from this connection. Please wait a few minutes, or call 07795 181894.',429);
$subject='Treevolution website quote request – '.$name;
$body="Name: $name\nPhone: $phone\nEmail: $email\nPostcode/area: $postcode\nService: $service\n\n$message\n";
$headers=['From: Treevolution Website <no-reply@treevolution.uk>','Reply-To: '.$email,'Content-Type: text/plain; charset=UTF-8'];
$sent=@mail('info@treevolution.uk',$subject,$body,implode("\r\n",$headers));
$status=$sent?'Thank you. Your enquiry has been sent to Treevolution.':'We could not confirm email delivery. Please call 07795 181894 or email info@treevolution.uk directly.';
render_status($status);
