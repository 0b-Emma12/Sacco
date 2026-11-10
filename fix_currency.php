<?php
$files = [
    'resources/views/members/show.blade.php',
    'resources/views/transactions/index.blade.php',
    'resources/views/transactions/show.blade.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $content = str_replace('Ksh', 'UGX', $content);
        $content = str_replace('KES', 'UGX', $content);
        file_put_contents($path, $content);
        echo "Updated $file\n";
    }
}
