<?php

declare(strict_types=1);

$roots = [
    __DIR__.'/../app',
    __DIR__.'/../bootstrap',
    __DIR__.'/../config',
    __DIR__.'/../database',
    __DIR__.'/../routes',
    __DIR__,
];

$phpBinary = PHP_BINARY;
$errors = [];

foreach ($roots as $root) {
    if (! is_dir($root)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $command = escapeshellarg($phpBinary).' -l '.escapeshellarg($path).' 2>&1';
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $errors[] = [
                'path' => $path,
                'output' => implode(PHP_EOL, $output),
            ];
        }

        $output = [];
    }
}

if ($errors === []) {
    fwrite(STDOUT, 'PHP syntax check passed.'.PHP_EOL);
    exit(0);
}

fwrite(STDERR, 'PHP syntax check failed:'.PHP_EOL.PHP_EOL);

foreach ($errors as $error) {
    fwrite(STDERR, $error['path'].PHP_EOL);
    fwrite(STDERR, $error['output'].PHP_EOL.PHP_EOL);
}

exit(1);
