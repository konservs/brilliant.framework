<?php
/**
 * Console logger.
 *
 * @author Andrii Biriev, a@konservs.com, www.konservs.com
 */
namespace Brilliant\Log;
use Brilliant\Log\BLogger;

class BLoggerConsole extends BLogger{
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
