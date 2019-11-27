<?php

error_reporting(0);

use Symfony\Component\HttpFoundation\Request;

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    //|| isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('188.92.241.45', '127.0.0.1', 'fe80::1', '::1', '37.145.9.54', '93.80.8.92', '81.23.9.212'))
) {
    if (!preg_match('/^(95\.25\.|95\.26\.)/u', @$_SERVER['REMOTE_ADDR'])) {
        header('HTTP/1.0 403 Forbidden');
        exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
    }
}

# память, необходимая админке Сонаты
if (strpos($_SERVER['REQUEST_URI'], '/admin/vidal/') !== false) {
	ini_set('memory_limit', -1);
}

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';
require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
