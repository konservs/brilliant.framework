<?php
/**
 * Log manager library.
 *
 * @author Andrii Biriev, a@konservs.com, www.konservs.com
 */
bimport('log.types');
bimport('log.logger');

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
