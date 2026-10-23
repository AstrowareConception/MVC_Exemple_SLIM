<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$directories = ['bin', 'config', 'database', 'public', 'src', 'tests', 'views'];
$failures = 0;

foreach ($directories as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root . '/' . $directory, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname());
        exec($command, $output, $exitCode);
        echo implode(PHP_EOL, $output) . PHP_EOL;
        $output = [];
        if ($exitCode !== 0) {
            ++$failures;
        }
    }
}

exit($failures === 0 ? 0 : 1);
