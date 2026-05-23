<?php
// Application path configuration
if (!defined('BASE_URL')) {
    define('BASE_URL', '/grocery');
}

function site_url($path = '') {
    $base = rtrim(BASE_URL, '/');
    if ($path === '') {
        return $base . '/';
    }
    return $base . '/' . ltrim($path, '/');
}
