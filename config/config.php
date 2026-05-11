<?php
require_once __DIR__ . '/env.php';

define('APP_NAME',  env('APP_NAME',  'MediCare'));
define('APP_URL',   env('APP_URL',   'http://localhost:8000'));
define('APP_DEBUG', env('APP_DEBUG', true));

if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }
else           { ini_set('display_errors', 0); error_reporting(0); }

// Only load database if not already loaded (Vercel pre-loads it)
if (!function_exists('col')) {
    require_once __DIR__ . '/database.php';
}
if (!function_exists('me')) {
    require_once __DIR__ . '/auth.php';
}
