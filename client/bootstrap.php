<?php
declare(strict_types=1);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

$configPath = __DIR__ . '/.treevolution-config.php';
if (!is_file($configPath)) {
    http_response_code(503);
    exit('Website Manager is not configured yet.');
}
$config = require $configPath;

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    $token = (string)($_POST['csrf'] ?? '');
    if (!hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) {
        http_response_code(403);
        exit('Your session token expired. Please go back and try again.');
    }
}

function auth_required(): void {
    if (empty($_SESSION['treevolution_admin'])) {
        header('Location: login.php');
        exit;
    }
}

function gh_request(string $method, string $path, ?array $payload = null): array {
    global $config;
    $url = 'https://api.github.com/repos/' . rawurlencode($config['github_owner']) . '/' . rawurlencode($config['github_repo']) . '/contents/' . $path;
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Could not initialise GitHub connection.');
    }
    $headers = [
        'Authorization: Bearer ' . $config['github_token'],
        'Accept: application/vnd.github+json',
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: Treevolution-Client-Updater',
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
    $raw = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        throw new RuntimeException('GitHub connection failed: ' . $error);
    }
    $data = json_decode((string)$raw, true) ?? [];
    if ($code < 200 || $code >= 300) {
        $message = $data['message'] ?? 'Unknown GitHub API error';
        throw new RuntimeException('GitHub API error ' . $code . ': ' . $message);
    }
    return $data;
}

function slugify(string $value): string {
    $value = trim($value);
    if (function_exists('iconv')) {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii !== false) $value = $ascii;
    }
    $value = strtolower((string)preg_replace('/[^a-zA-Z0-9]+/', '-', $value));
    return trim($value, '-') ?: 'tree-care-update';
}

function normalise_jpeg_orientation($image, string $sourcePath) {
    if (!function_exists('exif_read_data')) return $image;
    $exif = @exif_read_data($sourcePath);
    $orientation = (int)($exif['Orientation'] ?? 1);
    switch ($orientation) {
        case 2:
            imageflip($image, IMG_FLIP_HORIZONTAL);
            break;
        case 3:
            $image = imagerotate($image, 180, 0);
            break;
        case 4:
            imageflip($image, IMG_FLIP_VERTICAL);
            break;
        case 5:
            imageflip($image, IMG_FLIP_HORIZONTAL);
            $image = imagerotate($image, 90, 0);
            break;
        case 6:
            $image = imagerotate($image, -90, 0);
            break;
        case 7:
            imageflip($image, IMG_FLIP_HORIZONTAL);
            $image = imagerotate($image, -90, 0);
            break;
        case 8:
            $image = imagerotate($image, 90, 0);
            break;
    }
    return $image;
}

function optimise_image_to_webp(string $sourcePath, int $maxWidth = 1800, int $maxHeight = 1800, int $quality = 82): string {
    if (!extension_loaded('gd') || !function_exists('imagewebp')) {
        throw new RuntimeException('Image processing is not available on the server (PHP GD/WebP required).');
    }
    $bytes = file_get_contents($sourcePath);
    if ($bytes === false) throw new RuntimeException('Could not read uploaded image.');
    $src = @imagecreatefromstring($bytes);
    if ($src === false) throw new RuntimeException('The uploaded file is not a readable image.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($sourcePath);
    if ($mime === 'image/jpeg') {
        $src = normalise_jpeg_orientation($src, $sourcePath);
    }

    $width = imagesx($src);
    $height = imagesy($src);
    if ($width < 1 || $height < 1) {
        imagedestroy($src);
        throw new RuntimeException('Invalid image dimensions.');
    }

    $scale = min(1, $maxWidth / $width, $maxHeight / $height);
    $newWidth = max(1, (int)round($width * $scale));
    $newHeight = max(1, (int)round($height * $scale));
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    ob_start();
    $ok = imagewebp($dst, null, $quality);
    $output = (string)ob_get_clean();
    imagedestroy($src);
    imagedestroy($dst);
    if (!$ok || $output === '') throw new RuntimeException('Could not optimise the uploaded image.');
    return $output;
}

/* Project-management and SEO helpers */
function story_key(array $story): string {
    if (!empty($story['id']) && preg_match('/^[a-zA-Z0-9_-]{6,80}$/', (string)$story['id'])) return (string)$story['id'];
    return substr(hash('sha256', (string)($story['date'] ?? '') . '|' . (string)($story['title'] ?? '') . '|' . (string)($story['image'] ?? '')), 0, 16);
}

function gh_get_json_file(string $path): array {
    global $config;
    $existing = gh_request('GET', $path . '?ref=' . rawurlencode($config['github_branch']));
    $decoded = json_decode(base64_decode((string)($existing['content'] ?? '')), true);
    return [$existing, is_array($decoded) ? $decoded : []];
}

function gh_put_text_file(string $path, string $content, string $message, ?string $sha = null): array {
    global $config;
    $payload = [
        'message' => $message,
        'content' => base64_encode($content),
        'branch' => $config['github_branch'],
    ];
    if ($sha) $payload['sha'] = $sha;
    return gh_request('PUT', $path, $payload);
}

function gh_delete_file_if_exists(string $path, string $message): void {
    global $config;
    try {
        $existing = gh_request('GET', $path . '?ref=' . rawurlencode($config['github_branch']));
        if (!empty($existing['sha'])) {
            gh_request('DELETE', $path, [
                'message' => $message,
                'sha' => $existing['sha'],
                'branch' => $config['github_branch'],
            ]);
        }
    } catch (RuntimeException $e) {
        if (strpos($e->getMessage(), 'GitHub API error 404:') === false) throw $e;
    }
}

function project_page_path(string $slug): string {
    return 'our-work/projects/' . slugify($slug) . '/index.html';
}

function project_meta_description(array $story): string {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)($story['body'] ?? ''))));
    if ($text === '') $text = (string)($story['title'] ?? 'Tree care project') . ' completed by Treevolution.';
    if (function_exists('mb_substr')) return rtrim(mb_substr($text, 0, 154, 'UTF-8')) . (mb_strlen($text, 'UTF-8') > 154 ? '…' : '');
    return strlen($text) > 154 ? rtrim(substr($text, 0, 154)) . '…' : $text;
}

function project_page_html(array $story): string {
    $title = (string)($story['title'] ?? 'Tree care project');
    $slug = slugify((string)($story['slug'] ?? $title));
    $location = trim((string)($story['location'] ?? 'Sussex'));
    $category = (string)($story['category'] ?? 'Tree care');
    $date = (string)($story['date'] ?? date('Y-m-d'));
    $body = (string)($story['body'] ?? '');
    $alt = (string)($story['alt'] ?? $title);
    $images = $story['images'] ?? (!empty($story['image']) ? [$story['image']] : []);
    if (!is_array($images)) $images = [];
    $canonical = 'https://treevolution.uk/our-work/projects/' . rawurlencode($slug) . '/';
    $meta = project_meta_description($story);
    $pageTitle = $title . ($location ? ' | ' . $location : '') . ' | Treevolution';
    $imageUrl = !empty($images[0]) ? 'https://treevolution.uk/' . ltrim((string)$images[0], '/') : 'https://treevolution.uk/assets/img/site/social-share.jpg';
    $imageMarkup = '';
    foreach ($images as $n => $img) {
        $src = '../../../' . ltrim((string)$img, '/');
        $imgAlt = $alt . (count($images) > 1 ? ' – photo ' . ($n + 1) : '');
        $imageMarkup .= '<figure class="project-photo"><img src="' . e($src) . '" alt="' . e($imgAlt) . '" loading="' . ($n === 0 ? 'eager' : 'lazy') . '" decoding="async"></figure>';
    }
    $heroImage = !empty($images[0]) ? '../../../' . ltrim((string)$images[0], '/') : '../../../assets/img/site/tree-surgeon-working-at-height.webp';
    $schema = [
        '@context' => 'https://schema.org','@type' => 'Article','headline' => $title,'description' => $meta,'datePublished' => $date,'dateModified' => date('Y-m-d'),'mainEntityOfPage' => $canonical,
        'author' => ['@type'=>'Organization','name'=>'Treevolution','url'=>'https://treevolution.uk/'],'publisher' => ['@type'=>'Organization','name'=>'Treevolution','url'=>'https://treevolution.uk/'],'about' => $category,
        'contentLocation' => ['@type'=>'Place','name'=>$location ?: 'Sussex'],'image' => array_map(fn($p) => 'https://treevolution.uk/' . ltrim((string)$p, '/'), $images),
    ];
    $crumb = ['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>[
        ['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>'https://treevolution.uk/'],['@type'=>'ListItem','position'=>2,'name'=>'Our work','item'=>'https://treevolution.uk/our-work/'],['@type'=>'ListItem','position'=>3,'name'=>$title,'item'=>$canonical],
    ]];
    return '<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="robots" content="index,follow">'
        . '<title>' . e($pageTitle) . '</title><meta name="description" content="' . e($meta) . '"><link rel="canonical" href="' . e($canonical) . '"><meta property="og:type" content="article"><meta property="og:title" content="' . e($pageTitle) . '"><meta property="og:description" content="' . e($meta) . '"><meta property="og:url" content="' . e($canonical) . '"><meta property="og:image" content="' . e($imageUrl) . '"><meta name="twitter:card" content="summary_large_image"><meta name="theme-color" content="#062d20"><link rel="icon" href="../../../favicon.ico"><link rel="stylesheet" href="../../../assets/css/site.css?v=20260812-0715">'
        . '<script type="application/ld+json">' . json_encode($schema,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . '</script><script type="application/ld+json">' . json_encode($crumb,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . '</script></head><body><a class="skip-link" href="#main">Skip to content</a>'
        . '<div class="topbar"><div class="shell"><span>Tree surgeons serving Lewes, Sussex & surrounding areas</span><div><a href="tel:+447795181894">07795 181894</a><a href="mailto:info@treevolution.uk">info@treevolution.uk</a></div></div></div><header class="site-header"><div class="shell nav-shell"><a class="brand" href="../../../" aria-label="Treevolution home"><img src="../../../assets/branding/treevolution-logo.svg" alt="Treevolution Professional Tree Care" width="260" height="74"></a><button class="nav-toggle" aria-expanded="false" aria-controls="site-nav"><span></span><span></span><span></span><span class="sr-only">Menu</span></button><nav class="site-nav" id="site-nav" aria-label="Primary"><a href="../../../">Home</a><a href="../../../services/">Services</a><a href="../../" aria-current="page">Our work</a><a href="../../../about/">About</a><a href="../../../reviews/">Reviews</a><a href="../../../areas/">Areas</a><a href="../../../contact/">Contact</a><a class="nav-cta" href="../../../contact/">Free quote</a></nav></div></header>'
        . '<main id="main"><section class="project-hero"><div class="shell project-hero-grid"><div><p class="kicker light">' . e($category) . ($location ? ' • ' . e($location) : '') . '</p><h1>' . e($title) . '</h1><p class="lead">A recent Treevolution project completed on ' . e($date) . '.</p><div class="hero-actions"><a class="btn primary" href="../../../contact/">Request a free quote</a><a class="btn outline" href="tel:+447795181894">Call Henry</a></div></div><div class="project-hero-image"><img src="' . e($heroImage) . '" alt="' . e($alt) . '" loading="eager" fetchpriority="high"></div></div></section><section class="section"><div class="shell project-detail"><div class="project-story"><p class="kicker">About the job</p><h2>Work carried out by Treevolution.</h2><p>' . nl2br(e($body)) . '</p><p><a class="btn dark" href="../../../contact/">Ask about similar work</a></p></div><div class="project-gallery">' . $imageMarkup . '</div></div></section></main>'
        . '<footer class="site-footer"><div class="shell footer-grid"><div class="footer-brand"><img src="../../../assets/branding/treevolution-logo.svg" alt="Treevolution Professional Tree Care"><p>Experienced tree surgeons providing practical tree surgery and tree care across Lewes, Sussex and surrounding areas.</p></div><div><h2>Tree surgery</h2><a href="../../../services/tree-removal/">Tree removal</a><a href="../../../services/tree-reduction/">Reduction & pruning</a><a href="../../../services/pollarding/">Pollarding</a><a href="../../../services/hedge-cutting/">Hedge cutting</a></div><div><h2>Company</h2><a href="../../../about/">About</a><a href="../../">Our work</a><a href="../../../reviews/">Reviews</a><a href="../../../areas/">Areas</a><a href="../../../contact/">Contact</a></div><div><h2>Contact</h2><a href="tel:+447795181894">07795 181894</a><a href="mailto:info@treevolution.uk">info@treevolution.uk</a></div></div><div class="shell footer-bottom"><span>© <span data-year></span> Treevolution.</span><span><a href="../../">More Treevolution projects</a></span><span>Website created by <a href="https://sccwebdesign.co.uk/" target="_blank" rel="noopener">SCC Webdesign</a></span></div></footer><div class="mobile-contact"><a href="tel:+447795181894">Call Henry</a><a href="../../../contact/">Free quote</a></div><script src="../../../assets/js/site.js?v=20260812-0715" defer></script></body></html>';
}

function update_sitemap_for_story(array $story, bool $remove = false): void {
    global $config;
    $path = 'sitemap.xml';
    $existing = gh_request('GET', $path . '?ref=' . rawurlencode($config['github_branch']));
    $xml = base64_decode((string)($existing['content'] ?? ''));
    if (!is_string($xml) || $xml === '') return;
    $slug = slugify((string)($story['slug'] ?? $story['title'] ?? 'project'));
    $loc = 'https://treevolution.uk/our-work/projects/' . $slug . '/';
    $pattern = '~\s*<url><loc>' . preg_quote($loc, '~') . '</loc>(?:<lastmod>[^<]+</lastmod>)?</url>~';
    $xml = preg_replace($pattern, '', $xml);
    if (!$remove) {
        $entry = "\n  <url><loc>" . $loc . "</loc><lastmod>" . date('Y-m-d') . "</lastmod></url>";
        $xml = str_replace('</urlset>', $entry . "\n</urlset>", $xml);
    }
    gh_put_text_file($path, $xml, ($remove ? 'Remove' : 'Update') . ' project in sitemap: ' . (string)($story['title'] ?? ''), (string)($existing['sha'] ?? ''));
}

function requested_uploads(): array {
    if (!isset($_FILES['images']) || !is_array($_FILES['images']['name'] ?? null)) return [];
    $files=[]; $count=count($_FILES['images']['name']);
    for($i=0;$i<$count;$i++){
        if((int)$_FILES['images']['error'][$i]===UPLOAD_ERR_NO_FILE) continue;
        $files[]=['name'=>(string)$_FILES['images']['name'][$i],'tmp_name'=>(string)$_FILES['images']['tmp_name'][$i],'error'=>(int)$_FILES['images']['error'][$i],'size'=>(int)$_FILES['images']['size'][$i]];
    }
    return $files;
}

function validate_uploads(array $files): void {
    foreach($files as $file){
        if((int)$file['error']!==UPLOAD_ERR_OK || (int)$file['size']<1 || (int)$file['size']>8*1024*1024) throw new RuntimeException('Invalid image upload. Each photograph must be no larger than 8MB.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if(!in_array($mime,['image/jpeg','image/png','image/webp'],true)) throw new RuntimeException('Unsupported image type. Please use JPG, PNG or WebP.');
    }
}

function save_story_images(array $files, string $date, string $slug, string $title, int $startIndex=0): array {
    global $config; $paths=[];
    foreach($files as $i=>$file){
        $position=$startIndex+$i+1; $path='content/uploads/'.$date.'-'.$slug.'-'.$position.'.webp'; $optimised=optimise_image_to_webp($file['tmp_name']);
        try { gh_request('GET',$path.'?ref='.rawurlencode($config['github_branch'])); $path='content/uploads/'.$date.'-'.$slug.'-'.$position.'-'.substr(bin2hex(random_bytes(3)),0,6).'.webp'; }
        catch(RuntimeException $e){ if(strpos($e->getMessage(),'GitHub API error 404:')===false) throw $e; }
        gh_request('PUT',$path,['message'=>'Client image '.$position.': '.$title,'content'=>base64_encode($optimised),'branch'=>$config['github_branch']]); $paths[]=$path;
    }
    return $paths;
}
