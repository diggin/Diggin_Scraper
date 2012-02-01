<?php

error_reporting( E_ALL | E_STRICT );

$vendor = dirname(__DIR__).'/vendor';
require_once $vendor.'/SplClassLoader.php';

$loader = new SplClassLoader('Diggin\\Http\Charset', $vendor.'/Diggin_Http_Charset/src');
$loader->register();
$loader = new SplClassLoader('Diggin\\Scraper\\Adapter\\Htmlscraping', $vendor.'/Diggin_Scraper_Adapter_Htmlscraping/src');
$loader->register();
$loader = new SplClassLoader('Diggin\\Scraper', dirname(__DIR__).'/src');
$loader->register();
$loader = new SplClassLoader('Zend', dirname(__DIR__).'/vendor');
$loader->register();

