<?php

error_reporting(0);

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/app_redirects.php';

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
