#!/usr/bin/env php
<?php

ini_set('default_socket_timeout', 220);
ini_set('memory_limit', "1G");
error_reporting(-1);
ini_set('display_errors', 'On');
set_time_limit(0);

use Symfony\Component\Debug\Debug;

$loader = require __DIR__ . '/../../app/autoload.php';
require_once __DIR__ . '/../../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__ . '/../../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

/**
 * oauth1 - M1
 */
$name = "magento";
$options = array(
    'identifier' => 'user.username',
    'nickname' => 'user.username',
    'realname' => 'user.display_name',
    'profilepicture' => 'user.avatar',
);

/** @var \Marello\Bundle\MageBridgeBundle\OAuth\ResourceOwner\MagentoResourceOwner $resourceOwner */
$resourceOwner = $kernel->getContainer()->get("hwi_oauth.resource_owner.magento");

$redirectUri = "https://m1demo.test-madia.nl/";

/*

//1. temp credentials -> #POST /oauth/initiate
$requestTokens = $resourceOwner->getRequestToken($redirectUri);



//2:autorization - via M1 admin GUI
$redirectUri = $resourceOwner->getAuthorizationUrl($redirectUri, $requestTokens);
print_r($requestTokens);
echo "\n";
print_r($redirectUri);
echo "\n";
die();
*/

/*
//TEMP DATA WITH AUTHORIZATION
$requestTokens = [
        'oauth_token' => '4355040b6c6a9d94fae9651bdc760a2f',
        'oauth_token_secret' => 'a3a88dbe80db4135af40ccf7ef7326e5',
        'oauth_verifier' => 'e7339c8a309ed61df3b7b0f55d989131'
    ];


//3. TOKEN EXCHANGE - LAST AUTH STEP
$request = new \Symfony\Component\HttpFoundation\Request($requestTokens);
$accessToken = $resourceOwner->getAccessToken($request, $redirectUri);

//reveal
print_r($accessToken);
*/

//RUN REGULAR REQUESTS
$accessToken = [
    'oauth_token' => '54ab54b0b3a87d2988d5a96e87f9cded',
    'oauth_token_secret' => 'c9c6d2a2127f471c8b898cc3c2fdc51d',
];

// - Retrieve

$products = $resourceOwner->getProducts($accessToken);
var_dump($products->getResponse());
die();

// - Create
$productData = json_encode(array(
    'type_id' => 'simple',
    'attribute_set_id' => 4,
    'sku' => 'simple' . uniqid(),
    'weight' => 1,
    'status' => 1,
    'visibility' => 4,
    'name' => 'Simple Product',
    'description' => 'Simple Description',
    'short_description' => 'Simple Short Description',
    'price' => 99.95,
    'tax_class_id' => 2,
));

//echo $productData;die();

$products = $resourceOwner->createProducts($accessToken, $productData);
print_r($products->getResponse());
print_r($products);
