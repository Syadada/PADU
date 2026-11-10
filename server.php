<?php

/**
 * Laravel - A PHP Framework For Web Artisans (Fallback Server Router)
 * PADU Enterprise v2.0 - High Compatibility Router
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$publicFile = __DIR__ . '/public' . $uri;

// 1. Direct Static File Serving (Immune to Document Root Mismatches)
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    $ext = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'mjs'   => 'application/javascript; charset=UTF-8',
        'json'  => 'application/json; charset=UTF-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
        'map'   => 'application/json',
        'csv'   => 'text/csv; charset=UTF-8',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'pdf'   => 'application/pdf',
        'txt'   => 'text/plain; charset=UTF-8',
    ];

    $contentType = $mimes[$ext] ?? 'application/octet-stream';
    header("Content-Type: {$contentType}");
    header('Content-Length: ' . filesize($publicFile));
    header('Cache-Control: public, max-age=3600');
    readfile($publicFile);
    exit;
}

// 2. Delegate all dynamic web requests to Laravel Front Controller
require_once __DIR__ . '/public/index.php';
