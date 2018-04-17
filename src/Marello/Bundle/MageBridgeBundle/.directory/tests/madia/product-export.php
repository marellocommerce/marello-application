#!/usr/bin/env php
<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Debug\DebugClassLoader;

ini_set('default_socket_timeout', 220);
ini_set('memory_limit', "1G");
error_reporting(-1);
ini_set('display_errors', 'On');
set_time_limit(0);


$loader = require __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../app/AppKernel.php';

Debug::enable();


Debug::enable();
//ErrorHandler::register();
//ExceptionHandler::register();
//DebugClassLoader::enable();

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();


//$model = $kernel->getContainer()->get('marello_magebridge.importexport.product_reader');
//
//var_dump(get_class($model));
//die();


/**
 * load into ORO
 */
/** @var \Oro\Bundle\ImportExportBundle\Handler\ExportHandler $importHandler */
$importHandler = $kernel->getContainer()->get('oro_importexport.handler.export');

/**
 * Product - Customer
 */
$importResult = $importHandler->handleExport(
    'magento_product_export',
    'marello_magebridge_importexport_product_processor'
);

//TODO:
print_r($importResult);
