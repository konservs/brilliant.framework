<?php
//============================================================
// Basic class to control users, to list users, to login or 
// logout
//============================================================
namespace Brilliant\Users;

use Brilliant\BFactory;
use Brilliant\Log\BLog;
use Brilliant\Users\BUsers;

class BUsersSession {
	protected $triedresult = false;
	protected $triedstart = false;
	protected static $instance = NULL;

	/**
	 * Get instance
	 *
	 * @return BUsersSession|null
	 */
	public static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new BUsersSession();
		}
		return self::$instance;
	}

	/**
	 * Get instance
	 *
	 * @return BUsersSession|null
	 */
	public static function getInstanceAndStart() {
		if (!is_object(self::$instance)) {
			self::$instance = new BUsersSession();
		}
		if (!self::$instance->Start()) {
			return NULL;
		} else {
			return self::$instance;
		}
	}

	/**
	 * Init session
	 *
	 * @return bool
	 */
	public function Start() {
		if ($this->triedstart) {
			return $this->triedresult;
		}
		BLog::addToLog('[Users.Session]: Start()');
		$this->triedresult = false;
		$this->triedstart = true;
		$secret = explode(':', $_COOKIE['brillsecret']);
		if (count($secret) < 2) {
			$this->triedresult = false;
			return false;
		}
		$bCache = BFactory::getCache();
		if (!empty($bCache)) {
			$sess = $bCache->get('session:' . $secret[1]);
			if (($sess !== false) && ($sess !== NULL)) {
				$this->load($sess);
				$this->triedresult = true;
				return true;
			}
		}
		$db = BFactory::getDBO();
		if (empty($db)) {
			return false;
		}
		$qr = 'SELECT * from `sessions` WHERE (sessionid=' . $db->escapeString($secret[1]) . '&&userid=' . (int)$secret[0] . ')';
		$q = $db->Query($qr);
		if (empty($q)) {
			if (DEBUG_MODE) {
				BLog::addToLog('[Session]: Could not execute query! MySQL error: ' . $db->lasterror(), LL_ERROR);
			}
			return false;
		}
		$l = $db->fetch($q);
		if (empty($l)) {
			setcookie('brillsecret', '', time(), '/', BHOSTNAME);
			return false;
		}
		if (!empty($bCache)) {
			$bCache->set('session:' . $l['sessionid'], $l, 600);
		}
		$this->load($l, true);
		$this->triedstart = true;
		$this->triedresult = true;
		return true;
	}
	//====================================================
	// New session
	//====================================================
	public function newSession($uid, $options = array()) {
		BLog::addToLog('[Users.Session]: SessionStart()');
		//Checking options...
		if (!isset($options['interval'])) {
			$options['interval'] = 10800;
		}
		//Conncting to the database
		$db = BFactory::getDBO();
		if (empty($db)) {
			return false;
		}
		//-------------------------------------------
		// PHP porn :-(
		//-------------------------------------------
		$DTI_GENERAL = (int)$options['interval'];
		$DTI_D = (int)($DTI_GENERAL / 86400);
		$DTI_GENERAL -= $DTI_D * 86400;
		$DTI_H = (int)($DTI_GENERAL / 3600);
		$DTI_GENERAL -= $DTI_H * 3600;
		$DTI_M = (int)($DTI_GENERAL / 60);
		$DTI_GENERAL -= $DTI_M * 60;
		$DTI_S = $DTI_GENERAL;
		$DTI_STRING = 'P0Y0M' . $DTI_D . 'DT' . $DTI_H . 'H' . $DTI_M . 'M' . $DTI_S . 'S';
		$DTI = new \DateInterval($DTI_STRING);
		//-------------------------------------------
		// Variables
		//-------------------------------------------
		$sessid = sha1(uniqid(rand(), 1));
		$nowdate = new \DateTime();
		$enddate = new \DateTime();
		$enddate->add($DTI);

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$ipv4 = ip2long($ip);
		//Object for cache.
		$obj = array();
		$obj['sessionid'] = $sessid;
		$obj['start'] = $nowdate->format('Y-m-d H:i:s');
		$obj['end'] = $enddate->format('Y-m-d H:i:s');
		$obj['lastaction'] = $nowdate->format('Y-m-d H:i:s');
		$obj['interval'] = (int)$options['interval'];
		$obj['updatestep'] = $options['updatestep'];
		$obj['userid'] = $uid;
		$obj['data'] = '{}';
		$obj['ipv4'] = $ipv4;
		//-------------------------------------------
		//Forming query...
		//-------------------------------------------
		$qr = 'insert into `sessions` (`sessionid`,`ipv4`,`start`,`end`,' . 'lastaction,userid,`data`,updatestep,`interval`) values (' . $db->escapeString($obj['sessionid']) . ', ' . sprintf('%u', $obj['ipv4']) . ', ' . $db->escapeString($obj['start']) . ', ' . $db->escapeString($obj['end']) . ', ' . $db->escapeString($obj['lastaction']) . ', ' . $obj['userid'] . ',' . $db->escapeString($obj['data']) . ', ' . $obj['updatestep'] . ',' . $obj['interval'] . ')';
		$q = $db->Query($qr);
		if (empty($q)) {
			return false;
		}
		//-------------------------------------------
		// Updating last action
		//-------------------------------------------
		$qr = 'update users set last_action=NOW() where id=' . $obj['userid'];
		$q = $db->Query($qr);
		if (empty($q)) {
			return false;
		}
		//-------------------------------------------
		// Inserting into log...
		//-------------------------------------------
		$qr = 'insert into `users_iplog` (`user`,`dt`,`IPv4`,`UserAgent`) values (' . $obj['userid'] . ',' . $db->escapeString($obj['start']) . ', ' . sprintf('%u', $obj['ipv4']) . ', ' . $db->escapeString($_SERVER['HTTP_USER_AGENT']) . ')';
		$q = $db->Query($qr);
		if (empty($q)) {
			return false;
		}
		$busers = BUsers::getInstance();
		$me = $busers->itemGet($obj['userid']);
		$me->updatecache();
		//-------------------------------------------
		// Set cache...
		//-------------------------------------------
		$bCache = BFactory::getCache();
		if ($bCache) {
			$bCache->set('session:' . $obj['sessionid'], $obj, 600);
		}
		//Set cookie
		$expire = time() + $options['interval'] + 3600;//+1 hour
		//$expire=0;
		if (DEBUG_MODE) {
			BLog::addToLog('[Users.Session]: SessionStart() enddate=' . $enddate->format('Y-m-d H:i:s'));
			BLog::addToLog('[Users.Session]: SessionStart() nowdate=' . $nowdate->format('Y-m-d H:i:s'));
			BLog::addToLog('[Users.Session]: Interval: ' . $options['interval']);
			BLog::addToLog('[Users.Session]: Set cookie. Expire=' . $expire);
		}
		//BDebug::print_html();die('SetCookie!');
		setcookie('brillsecret', $uid . ':' . $sessid, $expire, '/', BHOSTNAME);
		return true;
	}
	//====================================================
	//
	//====================================================
	public function load($obj, $cacheupd = false) {
		if (DEBUG_MODE) {
			BLog::addToLog('[Users.Session]: Load()');
		}

		$enddate = new \DateTime($obj['end']);
		$nowdate = new \DateTime();
		$lacdate = new \DateTime($obj['lastaction']);
		if (DEBUG_MODE) {
			BLog::addToLog('[Users.Session]: Load() enddate=' . $enddate->format('Y-m-d H:i:s'));
			BLog::addToLog('[Users.Session]: Load() nowdate=' . $nowdate->format('Y-m-d H:i:s'));
			BLog::addToLog('[Users.Session]: Load() lacdate=' . $lacdate->format('Y-m-d H:i:s'));
		}
		if (!is_string($obj['sessionid'])) {
			if (DEBUG_MODE) {
				BLog::addToLog('[Users.Session]: Load() sessionid is not a string!', LL_ERROR);
			}
			$this->sessionid = '';
			$this->close();
			return false;
		}
		if ($enddate < $nowdate) {
			if (DEBUG_MODE) {
				BLog::addToLog('[Users.Session]: Load() $enddate<$nowdate. Closing session.', LL_ERROR);
			}
			$this->sessionid = $obj['sessionid'];
			$this->close();
			return false;
		}
		if (($nowdate->getTimestamp() - $lacdate->getTimestamp()) > $obj['updatestep']) {
			if (!$db = BFactory::getDBO()) {
				return false;
			}
			$qr = 'update sessions set 
			     `end`=DATE_ADD(now(),INTERVAL ' . $obj['interval'] . ' SECOND),
			     lastaction=NOW()
			     WHERE sessionid=' . $db->escapeString($obj['sessionid']);
			$q = $db->Query($qr);
			if (empty($q)) {
				return false;
			}

			$qr = 'update users set last_action=NOW() where id=' . $obj['userid'];
			$q = $db->Query($qr);
			$busers = BUsers::getInstance();
			$me = $busers->itemGet($obj['userid']);
			if (!empty($me)) {
				$me->updatecache();
			}
			$lacdate = $nowdate;
			$enddate->add(new \DateInterval('PT' . $obj['interval'] . 'S'));
			$cacheupd = true;
		}
		$this->sessionid = $obj['sessionid'];
		$this->ipv4 = $obj['ipv4'];
		$this->start = new \DateTime($obj['start']);
		$this->end = $enddate;
		$this->lastaction = $lacdate;//->format("Y-m-d H:i:s");
		$this->userid = $obj['userid'];
		$this->data = unserialize($obj['data']);
		$this->updatestep = $obj['updatestep'];
		$this->interval = $obj['interval'];
		if (CACHE_TYPE && $cacheupd) {
			$obj['end'] = $this->end->format("Y-m-d H:i:s");;
			$obj['lastaction'] = $this->lastaction->format("Y-m-d H:i:s");;
			$bCache = BFactory::getCache();
			$bCache->set('session:' . $obj['sessionid'], $obj, 600);
		}
		return true;
	}

	/**
	 * Close session.
	 * 
	 * @return bool
	 */
	public function close() {
		if (DEBUG_MODE) {
			BLog::addToLog('[Users.Session]: close()');
		}
		setcookie('brillsecret', '', time() - 3600, '/', BHOSTNAME);
		if (DEBUG_MODE) {
			BLog::addToLog('[Users.Session] close session');
		}
		if (!$db = BFactory::getDBO()) {
			return false;
		}
		$bCache = BFactory::getCache();
		if ($bCache) {
			$bCache->delete('session:' . $this->sessionid);
		}
		$q = $db->Query('delete from sessions where sessionid=' . $db->escapeString($this->sessionid));
		if (empty($q)) {
			BLog::addToLog('[Users.Session]: close(): Could not execute query! MySQL error: ' . $db->lasterror(), LL_ERROR);
			return false;
		}
		return true;
	}

}
