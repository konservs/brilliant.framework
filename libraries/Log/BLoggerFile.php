<?php
/**
 * File logger.
 *
 * @author Andrii Biriev, a@konservs.com, www.konservs.com
 */

namespace Brilliant\log;
use Brilliant\log\BLogger;

class BLoggerFile extends BLogger{
	public $logsDirectory;
	protected $logFile='';
	public function 
	/**
	 *
	 */
	public function addToLog($msg,$level=LL_GENERAL){
		echo('[dd.mm.YYYY hh:mm:ss] '.$msg.PHP_EOL);
		}
	/**
	 *
	 */
	public function addHR(){
		$this->addToLog('---------------------------------------------------------');
		}

	}