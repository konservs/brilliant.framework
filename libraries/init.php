<?php
/**
 * Initial file of CMS.
 * Load router and send requested URL to router.
 * 
 * @author Andrii Biriev
 * 
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
function bimport($libraryname){
	$fn=BLIBRARIESPATH.str_replace('.',DIRECTORY_SEPARATOR,$libraryname).'.php';
	if(!file_exists($fn)){
		die('Could not load "'.htmlspecialchars($libraryname).'" library ('.$fn.')!');
		}
	require_once($fn);
	}
function definepaths(){
	$fn_config=BROOTPATH.'config'.DIRECTORY_SEPARATOR.'config.php';
	if(!file_exists($fn_config)){
		die('Could not load config file! Please, copy config.default.php as config.php in config folder.');
		}
	include($fn_config);
	define('BCOMPONENTSPATH', BROOTPATH.'components'.DIRECTORY_SEPARATOR);
	define('BTEMPLATESPATH', BROOTPATH.'templates'.DIRECTORY_SEPARATOR);
	define('BLANGUAGESPATH', BROOTPATH.'language'.DIRECTORY_SEPARATOR);
	define('BMEDIAPATH', BROOTPATH.'htdocs'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR);
	date_default_timezone_set('Europe/Kiev');
	}
function binit(){
	definepaths();
	bimport('factory');
	bimport('router');
	$router=BRouter::getInstance();
	if(DEBUG_MODE){
		bimport('log.general');
		bimport('log.logger.html');
		$router->htmllogger=new BLoggerHTML();
		BLog::RegisterLogger($router->htmllogger);
		}

	$router->run($_SERVER['REQUEST_URI'],$_SERVER['HTTP_HOST']);
	}
function binitmedia(){
	definepaths();
	bimport('factory');
	bimport('images.general');
	$bimages=BImages::getInstance();
	if(!empty($_SERVER['DOCUMENT_URI'])){
		$bimages->run($_SERVER['DOCUMENT_URI']);
		}else{
		$bimages->run($_SERVER['REQUEST_URI']);
		}
	}
