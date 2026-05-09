<?php
function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k); $v = trim(trim($v), '"\'');
        if (!array_key_exists($k, $_ENV)) { $_ENV[$k] = $v; putenv("$k=$v"); }
    }
}
loadEnv(__DIR__ . '/../.env');

function env(string $key, mixed $default = null): mixed {
    $v = $_ENV[$key] ?? getenv($key) ?? $default;
    return match($v) { 'true' => true, 'false' => false, 'null' => null, default => $v };
}
