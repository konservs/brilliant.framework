<?php
/**
 * Log manager library.
 *
 * @author Andrii Biriev, a@konservs.com, www.konservs.com
 */
namespace Brilliant\log;
use BLogger;
use types;

class BLog{
	protected static $loggers=array();
	/**
	 *
	 */
	public static function addtolog($msg,$level=LL_GENERAL){
		foreach(self::$loggers as $logger){
			$logger->addtolog($msg,$level);
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