<?php
/**
 * Bootstrap — works on both localhost and Vercel
 * Include this at the top of every PHP file instead of '../config/config.php'
 */

// Find project root regardless of where script is called from
$root = dirname(__DIR__); // medicare/ folder

// Load config
require_once $root . '/config/env.php';
require_once $root . '/config/database.php';
require_once $root . '/config/auth.php';

define('APP_NAME',  env('APP_NAME',  'MediCare'));
define('APP_URL',   env('APP_URL',   'http://localhost:8000'));
define('APP_DEBUG', env('APP_DEBUG', true));

if (APP_DEBUG) { ini_set('display_errors', 1); error_reporting(E_ALL); }
else           { ini_set('display_errors', 0); error_reporting(0); }

// Fix include paths for Vercel
set_include_path($root);
