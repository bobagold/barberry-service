#!/usr/bin/env php
<?php

$listFile = dirname(__DIR__) . '/var/used_files.txt';
$storageDir = dirname(__DIR__) . '/usr/storage';
$cacheDir = dirname(__DIR__) . '/public/cache';

// check we have files list and it is not older than 1 hour
if (!is_file($listFile) || !is_readable($listFile) || !filesize($listFile)) {
    fwrite(STDERR, "ERROR: List file '{$listFile}' with used IDs does not exist or not readable.\n");
    exit(1);
}
$modified = filemtime($listFile);
if ($modified + 3600 < time()) {
    $date = date('r', $modified);
    fwrite(STDERR, "ERROR: List file '{$listFile}' is too old to be used (last modified '{$date}').\n");
    exit(1);
}

// find all files that are older 7 days and grep they are not from list and remove them
fwrite(STDOUT, "Removing not used files from storage...\n");
$command = 'find ' . escapeshellarg($storageDir) . ' -mindepth 1 -maxdepth 1 -mtime +7 -type f -printf "%f\n" ' .
    ' | grep -Fxvf ' . escapeshellarg($listFile) . ' | while read F; do echo rm -fv "'.addslashes($storageDir).'/$F"; done';
passthru($command);

// the same for cache
fwrite(STDOUT, "Removing not used files from cache...\n");
$command = 'find ' . escapeshellarg($cacheDir) . ' -mindepth 1 -maxdepth 1 -mtime +7 -type d -printf "%f\n" ' .
    ' | grep -Fxvf ' . escapeshellarg($listFile) . ' | while read F; do echo rm -fv "'.addslashes($cacheDir).'/$F"; done';
passthru($command);

// and delete just old cache too (older than 45 days)
fwrite(STDOUT, "Removing cache older than 45 days...\n");
$command = 'find ' . escapeshellarg($cacheDir) . ' -mindepth 1 -maxdepth 1 -mtime +45 -type d -printf "%f\n" ' .
    ' | while read F; do echo rm -fv "'.addslashes($cacheDir).'/$F"; done';
passthru($command);
