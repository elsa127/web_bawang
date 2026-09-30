<?php

echo 'GD: '.(extension_loaded('gd') ? 'YES' : 'NO').PHP_EOL;
echo 'imagecreatefromtiff: '.(function_exists('imagecreatefromtiff') ? 'YES' : 'NO').PHP_EOL;
echo 'Imagick: '.(extension_loaded('imagick') ? 'YES' : 'NO').PHP_EOL;
echo 'Python3: ';
exec('python3 --version 2>&1', $o);
echo implode(' ', $o).PHP_EOL;
echo 'Python: ';
exec('python --version 2>&1', $o);
echo implode(' ', $o).PHP_EOL;

if (extension_loaded('gd')) {
    $info = gd_info();
    foreach ($info as $k => $v) {
        echo "  $k: ".($v ? 'yes' : 'no').PHP_EOL;
    }
}
