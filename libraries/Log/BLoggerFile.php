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
	/**
	 */
	public function getLogFile(){
		if(empty($this->logFile)){
			$now = new \DateTime();
			$this->logFile = $this->logsDirectory.'log_'.$now->format('Y_m_d_h_i_s').'.log';
			}
		return $this->logFile;
		}
	/**
	 *
	 */
	public function addToLog($msg,$level=LL_GENERAL){
		$now = new \DateTime();
		$pref=$now->format('[Y-m-d h:i:s]');
		switch($level){
			case LL_GENERAL:
				$pref .= ' [-] ';
				break;
			case LL_ERROR:
				$pref .= ' [E] ';
				break;
			case LL_WARNING:
				$pref .= ' [W] ';
				break;
			case LL_DEBUG:
				$pref .= ' [D] ';
				break;
			default:
				$pref .= ' [?] ';
			}
		file_put_contents($this->getLogFile(),$pref.$msg.PHP_EOL,FILE_APPEND | LOCK_EX);
		}
	/**
	 *
	 */
	public function addHR(){
		$this->addToLog('---------------------------------------------------------');
		}
	}