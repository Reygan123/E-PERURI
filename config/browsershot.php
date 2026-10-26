<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Chrome / Chromium Path
    |--------------------------------------------------------------------------
    | Default to standard Google Chrome path on Windows if present,
    | otherwise falls back to system PATH or environment variable.
    */
    'chrome_path' => env(
        'BROWSERSHOT_CHROME_PATH',
        file_exists('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe')
            ? 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
            : (file_exists('C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe')
                ? 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe'
                : null)
    ),

    /*
    |--------------------------------------------------------------------------
    | Node & NPM Paths
    |--------------------------------------------------------------------------
    */
    'node_path' => env('BROWSERSHOT_NODE_PATH'),
    'npm_path' => env('BROWSERSHOT_NPM_PATH'),
    'node_modules_path' => env('BROWSERSHOT_NODE_MODULES_PATH', base_path('node_modules')),
];
