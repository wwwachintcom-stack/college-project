<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly (css, js, images)
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// ── API Routes ────────────────────────────────────────────────────────────────
if (str_starts_with($uri, '/api')) {
    require __DIR__ . '/api/index.php';
    exit;
}

// ── Web Routes ────────────────────────────────────────────────────────────────
$file = match(true) {
    $uri === '/' || $uri === ''          => __DIR__ . '/index.php',
    file_exists(__DIR__ . $uri)          => __DIR__ . $uri,
    file_exists(__DIR__ . $uri . '.php') => __DIR__ . $uri . '.php',
    default                              => null,
};

if ($file) { require $file; exit; }

http_response_code(404);
echo '<div style="font-family:sans-serif;padding:40px;text-align:center">
    <h2>404 — Page not found</h2>
    <p style="color:#64748b;margin-top:8px">' . htmlspecialchars($uri) . '</p>
    <a href="/" style="color:#2563eb;margin-top:16px;display:inline-block">← Go Home</a>
</div>';
