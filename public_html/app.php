<?php

use Composer\Autoload\ClassLoader;
use Symfony\Component\HttpFoundation\Request;

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: *');

    echo 1;
    die;

}

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';
$request = Request::createFromGlobals();


$kernel = new AppKernel('prod', false);
if ($request->server->get('SCRIPT_URL') != '/user') {
    $kernel->loadClassCache();
}
$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();


$response = $kernel->handle($request);
//var_dump(123);die;
$response->send();
$kernel->terminate($request, $response);
