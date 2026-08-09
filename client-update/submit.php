<?php
require __DIR__ . '/bootstrap.php';
auth_required();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
verify_csrf();

try {
    if (($_POST['orientation_confirmed'] ?? '') !== '1') {
        throw new RuntimeException('Please confirm that the photograph is upright before publishing.');
    }
    $file = $_FILES['image'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('Invalid image upload. Maximum file size is 8MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) {
        throw new RuntimeException('Unsupported image type. Please use JPG, PNG or WebP.');
    }

    $title = trim((string)($_POST['title'] ?? ''));
    $body = trim((string)($_POST['body'] ?? ''));
    $alt = trim((string)($_POST['alt'] ?? ''));
    $date = (string)($_POST['date'] ?? date('Y-m-d'));
    $category = trim((string)($_POST['category'] ?? 'Other tree care'));
    if (!$title || !$body || !$alt) throw new RuntimeException('Please complete all required fields.');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) throw new RuntimeException('Invalid date.');

    $allowedCategories = ['Tree removal','Tree reduction','Pollarding','Hedge cutting','Stump removal','Other tree care'];
    if (!in_array($category, $allowedCategories, true)) $category = 'Other tree care';

    $slug = slugify($title);
    $imagePath = 'content/uploads/' . $date . '-' . $slug . '.webp';
    $optimised = optimise_image_to_webp($file['tmp_name']);

    // Refuse accidental overwrite of an existing image name.
    try {
        gh_request('GET', $imagePath . '?ref=' . rawurlencode($config['github_branch']));
        $imagePath = 'content/uploads/' . $date . '-' . $slug . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.webp';
    } catch (RuntimeException $e) {
        if (!str_contains($e->getMessage(), '404')) throw $e;
    }

    gh_request('PUT', $imagePath, [
        'message' => 'Client image: ' . $title,
        'content' => base64_encode($optimised),
        'branch' => $config['github_branch'],
    ]);

    $existing = gh_request('GET', 'content/stories.json?ref=' . rawurlencode($config['github_branch']));
    $stories = json_decode(base64_decode((string)($existing['content'] ?? '')), true);
    if (!is_array($stories)) $stories = [];

    array_unshift($stories, [
        'title' => $title,
        'date' => $date,
        'category' => $category,
        'body' => $body,
        'image' => $imagePath,
        'alt' => $alt,
    ]);
    $stories = array_slice($stories, 0, 100);

    gh_request('PUT', 'content/stories.json', [
        'message' => 'Client story: ' . $title,
        'content' => base64_encode(json_encode($stories, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"),
        'sha' => $existing['sha'],
        'branch' => $config['github_branch'],
    ]);

    $_SESSION['csrf'] = bin2hex(random_bytes(32));
} catch (Throwable $e) {
    http_response_code(500);
    $error = $e->getMessage();
    ?><!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/treevolution-v6-3.css"><title>Update failed | Treevolution</title></head><body class="admin"><main class="container formcard"><h1>Update not published</h1><div class="notice"><?=e($error)?></div><p>No website story was intentionally published after this error. Please return and try again.</p><p><a class="btn btn-primary" href="index.php">Back to updater</a></p></main></body></html><?php
    exit;
}
?><!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="robots" content="noindex,nofollow"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/css/treevolution-v6-3.css"><title>Published | Treevolution</title></head><body class="admin"><main class="container formcard"><h1>Update sent to GitHub</h1><p>The optimised image and story were committed to the repository. GitHub Actions will now validate and deploy the updated test site.</p><p><a class="btn btn-primary" href="index.php">Add another</a> <a class="btn" href="../our-work/">View website</a></p></main></body></html>
