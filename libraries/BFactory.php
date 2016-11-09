<?php
/**
 * Brilliant Framework factory
 * 
 * @author Andrii Biriev
 * 
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant;
use Brilliant\log\BLog;
use Brilliant\sql\BMySQL;
use Brilliant\cache\BCache;

class BFactory{
	protected static $db=NULL;
	/**
	 *
	 */
	public static function getDBO(){
		if(!empty(self::$db)){
			return self::$db;
			}
		BLog::addtolog('[BFactory] Connecting to the database "'.MYSQL_DB_HOST.'"...');
		self::$db=BMySQL::getInstanceAndConnect();
		if(empty(self::$db)){
			BLog::addtolog('[BFactory] Could not connect to the MySQL database!',LL_ERROR);
			return NULL;
			}
		return self::$db;
		}
	/**
	 *
	 */
	public static function getCache(){
		if(CACHE_TYPE){
			$bcache=BCache::getInstance();
			return $bcache;
			}
		return NULL;
		}

	/**
	 *
	 */
	public static function getTempFn(){
		$tempfn=BROOTPATH.'temp'.DIRECTORY_SEPARATOR;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		for ($i=0;$i<15;$i++){
			$tempfn.= $characters[rand(0, $charactersLength - 1)];
			}
		$tempfn.='.tmp';
		return $tempfn;
		}
	}
