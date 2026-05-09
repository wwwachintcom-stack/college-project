<?php
// Run once: C:\php\php.exe fix_phpini.php
$ini = file_get_contents('C:\php\php.ini');
$ini = preg_replace('/^extension=php_mongodb.*$/m', '', $ini);
$ini = preg_replace('/^\s*[\r\n]/m', '', $ini); // remove blank lines created
file_put_contents('C:\php\php.ini', $ini);
echo "php.ini fixed! mongodb line removed.\n";
echo "Now run: C:\\php\\php.exe -S localhost:8000 router.php\n";
