<?php
/**
 * Basic class to control users, to list users, to login or
 * logout and all other operations with users.
 *
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant\Users;

use Brilliant\BFactory;
use Brilliant\Users\BUser;
use Brilliant\BSingleton;
use Brilliant\Log\BLog;

define('USERS_ERROR_OK', 0);
define('USERS_ERROR_UNKNOWN', 999);
define('REGISTER_ERROR_UNKNOWN', 999);
define('REGISTER_ERROR_PASSWORDNOTMATCH', 1);
define('REGISTER_ERROR_NOTVALIDEMAIL', 2);
define('REGISTER_ERROR_EMAILISINBASE', 3);
define('REGISTER_ERROR_TELINBASE', 4);
define('REGISTER_ERROR_TELNOTVALID', 5);
define('REGISTER_ERROR_DIDNOTAGREE', 6);
define('REGISTER_ERROR_NOTCORRECTCAPTCHA', 7);
define('REGISTER_ERROR_NOREGION', 8);
define('REGISTER_ERROR_NOCITY', 9);
define('USERS_ERROR_NOSUCHEMAIL', 10);
define('USERS_ERROR_DBERROR', 11);
define('USERS_ERROR_CODEWRONG', 12);
define('USERS_ERROR_COULDNOTDELETE', 13);
define('USERS_ERROR_PASS', 14);
define('ERROR_SPIVPADAYUT', 'is_user');
define('USERS_ERROR_OK_OK', 'ok');
define('USERS_ERROR_NOTUSER', 'notuser');

/**
 * Basic class to control bugtracker issues
 *
 * @method BUser itemGet(integer $id)
 * @method BUser[] itemsGet(integer[] $ids)
 * @method BUser[] itemsFilter($params)
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
class BUsers extends \Brilliant\Items\BItemsList {
	use BSingleton;
	protected $tableName = 'users';
	protected $itemClassName = '\Brilliant\Users\BUser';

	/**
	 * Return salted hash of password. Depended on email and password.
	 *
	 * @param $mail user email
	 * @param $pass user password
	 * @return string salted hash
	 */
	public function makepass($mail, $pass) {
		return hash('sha512', (hash('sha512', $mail . 'MamaMia') . hash('sha512', $pass . 'LetMeGo')));
	}

	/**
	 * Get logged user class
	 *
	 * @return BUser|null
	 */
	public function getLoggedUser() {
		$session = BUsersSession::getInstanceAndStart();
		if (empty($session)) {
			return NULL;
		}
		return $this->itemGet($session->userid);
	}

	/**
	 * Login. Returns user object
	 *
	 * @param $email
	 * @param $password
	 * @param bool|false $longsession
	 * @return BUser|int|null
	 */
	public function login($email, $password, $longsession = false) {
		$user = $this->getUserByEmail($email);
		if ($user == false) {
			BLog::addToLog('[Users]: login() wrong email!', LL_ERROR);
			return USERS_ERROR_NOSUCHEMAIL;
		}
		if ($user->active == USER_STATUS_NOTACTIVATED) {
			BLog::addToLog('[Users]: Not Activated', LL_ERROR);
			return USERS_ERROR_NOACTIVATED;
		}
		if ($user->active == USER_STATUS_BANNED) {
			BLog::addToLog('[Users]: Banned user', LL_ERROR);
			return USERS_ERROR_BANNED;
		}
		$hash = $this->makepass($email, $password);
		if ($user->password != $hash) {
			BLog::addToLog('[Users]: password hashes not equal! user hash=' . $user->password . '; post hash=' . $hash, LL_ERROR);
			return USERS_ERROR_PASS;
		}
		$options = array('interval' => $longsession ? 2592000 : 10800, 'updatestep' => 60,);
		$sess = BUsersSession::getInstance();
		$sess->newSession($user->id, $options);
		return USERS_ERROR_OK;
	}

	/**
	 * Items Filter SQL
	 *
	 * @param $params
	 * @param $wh
	 * @param $jn
	 * @return bool
	 */
	public function itemsFilterSql($params, &$wh, &$jn) {
		parent::itemsFilterSql($params, $wh, $jn);
		$db = BFactory::getDBO();
		//Filter users by Email
		if (isset($params['email'])) {
			$wh[] = '(`email`=' . $db->escapeString($params['email']) . ')';
		}
		return true;
	}

	/**
	 * Return items hash
	 * @param array $params
	 * @return string
	 */
	public function itemsFilterHash($params) {
		$itemsHash = parent::itemsFilterHash($params);
		//Filter users by email
		if (isset($params['email'])) {
			$itemsHash .= ':email=' . $params['email'];
		}
		return $itemsHash;
	}

	/**
	 * Get user By email
	 * @param $email
	 * @return BUser|null
	 */
	public function getUserByEmail($email) {
		$list = $this->itemsFilter(['email' => $email, 'limit' => 1]);
		if (empty($list)) {
			return NULL;
		}
		$user = reset($list);
		return $user;
	}
}
