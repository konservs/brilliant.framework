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
	protected $s_id;
	protected $secret;
	protected static $instance=NULL;
	//================================================================================
	//
	//================================================================================
	public function __construct(){
		$this->secret='';
		$this->s_id=0;
		//
		ini_set('session.gc_divisor',100);
		//Set 
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc'));
		}
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
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)) return false;

		$q=$db->Query(
			'SELECT s_id,s_secret,s_ipv4,s_data,s_start,s_lastaction, TIME_TO_SEC(TIMEDIFF(NOW(),s_lastaction)) as act_time'.
			' FROM `sessions`');
		if(!$q)return false;
		$list=array();
		while($l=$db->fetch($q)){
			$itm['id']=(int)$l['s_id'];
			$itm['ip_v4']=(int)$l['s_ipv4'];
			$itm['act_time']=(int)$l['act_time'];
			array_push($list,$itm);
			}
		return true;
		}
	//================================================================================
	//
	//================================================================================
	public function open($savePath, $sessionName){
		return true;
		}
	//================================================================================
	//
	//================================================================================
	public function close(){
		return true;
		}
	//================================================================================
	//
	//================================================================================
	public function read($id){
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)) return false;
		$q=$db->Query(
			'SELECT s_id,s_ipv4,s_data,s_start,s_lastaction'.
			' FROM `sessions`'.
			' WHERE (s_secret='.$db->escape_string($id).')');
		if(empty($q))return false;
		if($l=$db->fetch($q)){
			$ip_s=(int)$l['s_ipv4'];
			$ip_r=ip2long($_SERVER['REMOTE_ADDR']);
			if($ip_s!=$ip_r)
				return '';
			$this->s_id=(int)$l['s_id'];
			return $l['s_data'];
			}else{
			$q=$db->Query('INSERT INTO `sessions` (s_secret,s_ipv4,s_start,s_lastaction) VALUES ('.
				$db->escape_string($id).','.
				ip2long($_SERVER['REMOTE_ADDR']).','.
				'NOW(),NOW())');
			if(empty($q))return false;
			$this->s_id=(int)$db->insert_id();
			return '';
			}
		}
	//================================================================================
	//
	//================================================================================
	public function write($id, $data){
		if(empty($this->s_id))return false;
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)) return false;
		$qr='UPDATE `sessions` SET'.
			' s_data='.$db->escape_string($data).','.
			' s_lastaction=NOW()'.
			' WHERE(s_id='.$this->s_id.')';
		$q=$db->Query($qr);
		if(!$q)return false;
		return true;
		}
	//================================================================================
	//
	//================================================================================
	public function destroy($id){
		return true;
		}
	//================================================================================
	//   The garbage collector callback is invoked internally by PHP periodically
	// in order to purge old session data. The frequency is controlled by
	// session.gc_probability and session.gc_divisor. The value of lifetime which
	// is passed to this callback can be set in session.gc_maxlifetime. Return
	// value should be TRUE for success, FALSE for failure.
	//================================================================================
	public function gc($maxlifetime){
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)) return false;

		$q=$db->Query('DELETE FROM `sessions`'.
			' WHERE (TIME_TO_SEC(TIMEDIFF(NOW(),s_lastaction))>'.$maxlifetime.')');
		if(empty($q))return false;
		return true;
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