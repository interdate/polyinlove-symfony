<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;


if($_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: *');

    echo 1;
    die;

}

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], array('62.90.52.239', '31.168.186.78', '213.137.71.2', '213.137.70.53')) || php_sapi_name() === 'cli-server')
) {

    /*
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
    */

//    header('HTTP/1.0 404 Not Found');
//    exit('404. Not found');
}


//ini_set('memory_limit', '2048M');
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//
//$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
//Debug::enable();
//
//require_once __DIR__.'/../app/AppKernel.php';
//
//
//
//$kernel = new AppKernel('dev', true);
//$kernel->loadClassCache();
//$request = Request::createFromGlobals();
//$response = $kernel->handle($request);
//$response->send();
//$kernel->terminate($request, $response);

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable(E_RECOVERABLE_ERROR & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED, true);
$kernel = new AppKernel('dev', true);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

