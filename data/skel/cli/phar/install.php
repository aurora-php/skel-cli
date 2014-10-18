<?php

/*
 * This file is part of the '{{$vendor}}/{{$module}}' package.
 *
 * (c) {{$company}}
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Build PHAR package.
 *
 * @octdoc      h:install/build
 * @copyright   copyright (c) {{$year}} by {{$company}}
 * @author      {{$author}} <{{$email}}>
 */
/**/

define('APP_NAME', '{{$module}}');

if ($argc <= 1) {
    printf("usage: %s installation-path\n", $argv[0]);
    exit(1);
}

if (!class_exists('PHAR') || !Phar::canWrite()) {
    printf("unable to create PHAR package\n");
    exit(1);
}

$dir  = rtrim($argv[1], '/');
$exec = $dir . '/' . APP_NAME;
$file = $exec . '.phar';

if (!is_writable($dir)) {
    printf("destination is not writable '%s'\n", $dir);
    exit(1);
}

if (file_exists($exec)) {
    unlink($exec);
}

function getDirIterator($dir) {
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator(
            realpath($dir), 
            \FilesystemIterator::SKIP_DOTS
        )
    );

    return $iterator;
}

$phar = new Phar(
    $file, 
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 
    basename($file)
);

$iterator = new AppendIterator();
$iterator->append(getDirIterator(__DIR__ . '/../libs/'));

$phar->buildFromIterator($iterator, realpath(__DIR__ . '/../'));
$phar->setStub(file_get_contents(__DIR__ . '/stub.php'));

rename($file, $exec);

chmod($exec, 0755);
