<?php
//============================================================
// Sets of functions and classes to work with sessions
//
// Author: Andrii Biriev, b@brilliant.ua
// Copyright © Brilliant IT corporation, www.it.brilliant.ua
//============================================================
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'defines.php');
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'db_api.php');

define('BSessionStorageCookie',1);

//Security...
define('BSessionSecureIP',1);
define('BSessionSecureUserAgent',2);

class BSession{
	protected static $instance=NULL;
	//================================================================================
	// Returns the global Session object, only creating it
	// if it doesn't already exist.
	//================================================================================
	public static function getInstance(){
		if (!is_object(self::$instance))
			self::$instance=new BSession();
		return self::$instance;
		}
	//================================================================================
	//
	//================================================================================
	public static function getInstanceAndStart(){
		$r=self::getInstance();
		if(empty($r))return NULL;
		if(!$r->start())
			return NULL;
		return $r;
		}
	//================================================================================
	// Get all sessions
	//================================================================================
	public function listall(&$list){
		return false;
		}
	//================================================================================
	//
	//================================================================================
	public function start(){
		return session_start();
		}
	//================================================================================
	//
	//================================================================================
	public function get($var_name,$default=NULL){
		if(!isset($_SESSION[$var_name]))return $default;
		return $_SESSION[$var_name];
		}
	//================================================================================
	//
	//================================================================================
	public function set($var_name,$var_val){
		$_SESSION[$var_name]=$var_val;
		return true;
		}
	//================================================================================
	//
	//================================================================================
	public function clear($var_name){
		unset($_SESSION[$var_name]);
		return true;
		}
	//================================================================================
	//
	//================================================================================
	public function is_set($var_name){
		return isset($_SESSION[$var_name]);
		}
	}

$session=BSession::getInstance();
$session->start();


function session_get($var_name,$default=0){
	$session=BSession::getInstance();
	return($session->get($var_name,$default));
	}

function session_set($var_name,$var_val){
	$session=BSession::getInstance();
	return($session->set($var_name,$var_val));
	}

function session_clear($var_name){
	$session=BSession::getInstance();
	return($session->clear($var_name));
	}

function session_isset($var_name){
	$session=BSession::getInstance();
	return($session->is_set($var_name));
	}

?>