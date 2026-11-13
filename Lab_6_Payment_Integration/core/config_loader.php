<?php
// db and paystack settings: check shoppn-private next to the web root, then core/private

function shoppn_config_candidates(string $filename): array
{
    $candidates = [];

    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($docRoot !== '') {
        $realDoc = realpath($docRoot);
        if ($realDoc !== false) {
            $candidates[] = dirname($realDoc)
                . DIRECTORY_SEPARATOR . 'shoppn-private'
                . DIRECTORY_SEPARATOR . $filename;
        }
    }

    $candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . $filename;
    return $candidates;
}

function shoppn_load_config(string $filename): void
{
    foreach (shoppn_config_candidates($filename) as $path) {
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }

    error_log('Shoppn configuration file was not found: ' . $filename);
    if (!headers_sent()) {
        http_response_code(500);
    }
    exit('Application configuration is missing.');
}
