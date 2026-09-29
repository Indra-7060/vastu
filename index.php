<?php

/**
 * Optional root front controller for Hostinger.
 * Upload to: /public_html/index.php
 * (alongside app/, public/, vendor/, .env)
 *
 * Prefer using public_html.htaccess alone when possible.
 * Use this only if Hostinger Document Root cannot be set to /public.
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
