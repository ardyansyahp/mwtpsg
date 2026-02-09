<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Load Maintenance Mode
if (file_exists($maintenance = __DIR__.'/masterpsg/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register The Auto Loader
require __DIR__.'/masterpsg/vendor/autoload.php';

// Run The Application
(require_once __DIR__.'/masterpsg/bootstrap/app.php')
    ->handleRequest(Request::capture());
