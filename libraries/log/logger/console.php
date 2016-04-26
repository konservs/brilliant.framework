<?php
/**
 * Console logger.
 *
 * @author Andrii Biriev, a@konservs.com, www.konservs.com
 */

bimport('log.types');
bimport('log.logger');

class BLoggerConsole extends BLogger{
	/**
	 *
	 */
	public function addtolog($msg,$level=LL_GENERAL){
		echo('[dd.mm.YYYY hh:mm:ss] '.$msg.PHP_EOL);
		}
	/**
	 *
	 */
	public function addHR(){
		$this->addtolog('---------------------------------------------------------');
		}
	}
