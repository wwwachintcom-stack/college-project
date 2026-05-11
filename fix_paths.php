<?php
/**
 * Run once: php fix_paths.php
 * Replaces relative config require with absolute path in all PHP files
 */
$dirs = ['admin','doctor','patient','reception','auth'];
$base = __DIR__;
$count = 0;

foreach ($dirs as $dir) {
    $files = glob("$base/$dir/*.php");
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $new = str_replace(
            "require_once '../config/config.php';",
            "require_once dirname(__DIR__) . '/config/config.php';",
            $content
        );
        if ($new !== $content) {
            file_put_contents($file, $new);
            echo "Fixed: $dir/" . basename($file) . "\n";
            $count++;
        }
    }
}

// Fix index.php
$idx = file_get_contents("$base/index.php");
$new = str_replace(
    "require_once 'config/config.php';",
    "require_once __DIR__ . '/config/config.php';",
    $idx
);
if ($new !== $idx) { file_put_contents("$base/index.php", $new); echo "Fixed: index.php\n"; $count++; }

echo "\nTotal fixed: $count files\n";
