<?php
/**
 * HTML logger.
 *
 * @author Andrii Biriev, a@konservs.com, www.konservs.com
 */

namespace Brilliant\log;
use Brilliant\log\BLogger;
use Brilliant\log\BLog;
use Application\BRouter;

class BLoggerHTML extends BLogger{
	public static $memprof=true;
	public static $strings=array();
	/**
	 *
	 */
	public function addToLog($msg,$level=LL_GENERAL){
		$mem=0;
		if(self::$memprof){
			$mem=memory_get_usage();
			}
		$time=BRouter::page_time();
		self::$strings[]=(object)array(
			'level'=>$level,
			'time'=>$time,
			'text'=>$msg,
			'mem'=>$mem
			);
		}
	/**
	 *
	 */
	public function addHR(){
		$this->addToLog('---------------------------------------------------------');
		}
	/**
	 * Print HTML log.
	 */
	public static function print_html(){
		echo('<div class="debuginfo">');
		echo('<p><b>Debug information:</b></p>');
		foreach(self::$strings as $msg){
			$style='';
			switch($msg->level){
				case LL_GENERAL:
				case LL_DEBUG:
				case LL_INFO:
					$style='';
					break;
				case LL_WARNING:
				case LL_ERROR:
					$style='color: red;';
					break;
				}
			echo('<p style="'.$style.'">');
			if(self::$memprof){
				echo('(<span class="time">'.sprintf('%.7f',$msg->time).'</span> : ');
				echo('<span class="mem" title="'.$msg->mem.' bytes">'.sprintf('%.2f Mb',($msg->mem / 1048576)).')</span> - ');
				}
			else{
				echo('<span class="time">('.sprintf('%.7f',$msg->time).')</span> - ');
				}
			echo(htmlspecialchars($msg->text));
			echo('</p>');
			}
		echo('</div>');
		}
	}
