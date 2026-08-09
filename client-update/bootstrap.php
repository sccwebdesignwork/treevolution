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
    exit('Client updater is not configured yet.');
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
