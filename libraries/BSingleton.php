<?php
namespace Brilliant;

trait BSingleton{
	protected static $instance;
	/**
	 * Singleton initializer
	 *
	 * @return static
	 */
	final public static function getInstance(){
		return isset(self::$instance)?self::$instance:self::$instance=new self;
    		}
	/**
	 *
	 */
	final private function __wakeup(){
		}
	/**
	 *
	 */
	final private function __clone(){
		}
	}
