<?php

/**
 * Local development router for `php artisan serve`.
 *
 * Laravel used to ship this file from the framework package. Recent vendor
 * updates removed it, so we keep a project copy to satisfy ServeCommand's
 * fallback when it cannot find the vendor version. The behaviour matches the
 * historical default: delegate all requests to `public/index.php` unless the
 * requested asset exists within the `public` directory.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';

