<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Static files — let PHP serve directly
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// API
if (str_starts_with($uri, '/api')) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

// Root → index.php
if ($uri === '/' || $uri === '') {
    require_once __DIR__ . '/index.php';
    exit;
}

// Try exact file
if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    require_once __DIR__ . $uri;
    exit;
}

// Try with .php extension
if (file_exists(__DIR__ . $uri . '.php')) {
    require_once __DIR__ . $uri . '.php';
    exit;
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>404</title>
<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f8fafc}
.box{text-align:center;padding:40px}.box h2{font-size:24px;color:#1e293b;margin-bottom:8px}
.box p{color:#64748b;margin-bottom:20px}.box a{color:#2563eb;text-decoration:none;font-weight:600}</style>
</head><body><div class="box">
<h2>404 — Page not found</h2>
<p>' . htmlspecialchars($uri) . '</p>
<a href="/">← Go Home</a>
</div></body></html>';
