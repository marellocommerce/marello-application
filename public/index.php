<?php

//use Symfony\Bundle\FrameworkBundle\Console\Application;
//use Symfony\Component\Console\Input\ArgvInput;
//use Symfony\Component\ErrorHandler\Debug;

// if you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);

//set_time_limit(0);
//
//// Set the project directory as the current working directory
//// for running child processes correctly without specifying the execution directory
//chdir(__DIR__ . DIRECTORY_SEPARATOR . '..');
//
//require __DIR__.'/../vendor/autoload.php';
//
//$input = new ArgvInput();
//$env = 'dev';
//if ($env === 'dev' && !isset($_ENV['APP_FRONT_CONTROLLER'])) {
//    $_ENV['APP_FRONT_CONTROLLER'] = 'index.php';
//}
//$debug = true;
//if ($debug) {
//    Debug::enable();
//}
//$_SERVER['argv'] = [
//    'bin/console',
//    'oro:install',
//    '--env=dev',
//    '--timeout=3600',
//    '--sample-data=y',
//    '--drop-database',
//    '--application-url=http://0.0.0.0:5080/',
//    '--user-name=admin',
//    '--user-firstname=John',
//    '--user-lastname=Doe',
//    '--user-password=marello123',
//    '--user-email=johndoe@example.com',
//    '--organization-name=Marello',
//    '--formatting-code=en_US',
//    '--language=en',
//];
//$input = new ArgvInput([
//    'bin/console',
//    'oro:install',
//    '--env=dev',
//    '--timeout=3600',
//    '--sample-data=y',
//    '--drop-database',
//    '--application-url=http://0.0.0.0:5080/',
//    '--user-name=admin',
//    '--user-firstname=John',
//    '--user-lastname=Doe',
//    '--user-password=marello123',
//    '--user-email=johndoe@example.com',
//    '--organization-name=Marello',
//    '--formatting-code=en_US',
//    '--language=en',
//]);
//$kernel = new AppKernel($env, $debug);
//$application = new Application($kernel);
//$application->run($input);
//
//exit;

error_reporting(-1);
ini_set('display_errors', 'On');

use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

// Use APC for autoloading to improve performance
// Change 'sf2' by the prefix you want in order to prevent key conflict with another application
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

//require_once __DIR__.'/../src/AppCache.php';

require_once __DIR__.'/../src/AppKernel.php';

$kernel = new AppKernel('prod', false);

//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);