<?php
/**
 * Brilliant Framework factory
 *
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant;

use Brilliant\Log\BLog;
use Brilliant\Sql\BMySQL;
use Brilliant\Cache\BCache;

class BFactory {
	protected static $db = NULL;

	/**
	 * Get BMySQL instance
	 *
	 * @return BMySQL|null
	 */
	public static function getDBO() {
		if (!empty(self::$db)) {
			return self::$db;
		}
		BLog::addToLog('[BFactory] Connecting to the database "' . MYSQL_DB_HOST . '"...');
		self::$db = BMySQL::getInstanceAndConnect();
		if (empty(self::$db)) {
			BLog::addToLog('[BFactory] Could not connect to the MySQL database!', LL_ERROR);
			return NULL;
		}
		return self::$db;
	}

	/**
	 * get BCache
	 * @return BCache|null
	 */
	public static function getCache() {
		if (CACHE_TYPE) {
			$bCache = BCache::getInstance();
			return $bCache;
		}
		return NULL;
	}

	/**
	 * Get temporary filename
	 *
	 * @return string
	 */
	public static function getTempFn() {
		$tempFileName = BROOTPATH . 'temp' . DIRECTORY_SEPARATOR;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		for ($i = 0; $i < 15; $i++) {
			$tempFileName .= $characters[rand(0, $charactersLength - 1)];
		}
		$tempFileName .= '.tmp';
		return $tempFileName;
	}
}
