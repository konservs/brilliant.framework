<?php
/**
 * Log manager library.
 *
 * @author Andrii Biriev, a@konservs.com, www.konservs.com
 */
namespace Brilliant\Log;

define('LL_GENERAL',1);
define('LL_DEBUG',2);
define('LL_INFO',4);
define('LL_WARNING',8);
define('LL_ERROR',16);

class BLog{
	protected static $loggers=array();
	/**
	 *
	 */
	public static function addToLog($msg,$level=LL_GENERAL){
		foreach(self::$loggers as $logger){
			$logger->addToLog($msg,$level);
			}
		return true;
		}
	/**
	 *
	 */
	public static function addHR(){
		foreach(self::$loggers as $logger){
			$logger->addHR();
			}
		return true;
		}
	/**
	 *
	 */
	public static function RegisterLogger($logger){
		self::$loggers[]=$logger;
		}
	/**
	 *
	 */
        public function UnRegisterLogger($logger){
		}
	}
