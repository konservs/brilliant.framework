<?php
define('BROOTPATH', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('BFRAMEWORKPATH', BROOTPATH.'framework'.DIRECTORY_SEPARATOR);
define('BAPPLICATIONPATH', BROOTPATH.'application'.DIRECTORY_SEPARATOR);
//
define('BCOMPONENTSAPPLICATIONPATH', BAPPLICATIONPATH.'components'.DIRECTORY_SEPARATOR);
define('BCOMPONENTSFRAMEWORKPATH', BFRAMEWORKPATH.'components'.DIRECTORY_SEPARATOR);
define('BLIBRARIESAPPLICATIONPATH',  BAPPLICATIONPATH.'libraries'.DIRECTORY_SEPARATOR);
define('BLIBRARIESFRAMEWORKPATH',  BFRAMEWORKPATH.'libraries'.DIRECTORY_SEPARATOR);
//Other paths
define('BTEMPLATESPATH',  BROOTPATH.'templates'.DIRECTORY_SEPARATOR);
define('BLANGUAGESPATH',  BROOTPATH.'languages'.DIRECTORY_SEPARATOR);
//Cache path
define('PATH_CACHE',  BROOTPATH.'application'.DIRECTORY_SEPARATOR.'filecache'.DIRECTORY_SEPARATOR);
//Load configuration file
$fn_config=BROOTPATH.'application'.DIRECTORY_SEPARATOR.'config.php';
if(!file_exists($fn_config)){
	die('Could not load config file! Please, copy config.default.php as config.php in config folder.');
	}
include($fn_config);

//Initialize router
$loader=require(BROOTPATH.'vendor'.DIRECTORY_SEPARATOR.'autoload.php');
//
$logger = new \Brilliant\Log\BLoggerFile();
$logger->logsDirectory = __DIR__.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR;
\Brilliant\Log\BLog::RegisterLogger($logger);
