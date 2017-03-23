<?php
/**
 * Pretty class for extended DateTime functions
 * 
 * @author Andrii Biriev
 */
namespace Brilliant;

use Brilliant\CMS\BLang;

class BDateTime extends \DateTime{
	public static $dtnow=NULL;
	/**
	 *
	 * $format=1 - 01.10.2014
	 * $format=2 - 14 Янв.
	 */
	public function prettydate($format=1){
		$now=new \DateTime();
		$now_year=(int)$now->format('Y');

		$ths_year=(int)$this->format('Y');
		$ths_month=(int)$this->format('m');
		$ths_day=(int)$this->format('d');

		if($format==2){
			$res=$ths_day;
			$res.=' '.BLang::_('PRETTYDATETIME_MONTH_'.$this->format('M')).'.';
			return $res;
			}
		if($ths_year!=$now_year){
			return sprintf('%02d.%02d.%04d',$ths_day,$ths_month,$ths_year);
			}
		return sprintf('%02d.%02d',$ths_day,$ths_month);
		}
	/**
	 * Difference between now() and current datetime
	 */
	function SeconsToNow(){ // as datetime object returns difference in seconds
		if(empty(self::$dtnow)){
			self::$dtnow=new \DateTime();
			}
		//Get difference
		$diff = $this->diff(self::$dtnow);
		$diff_sec =$diff->format('%r').( // prepend the sign - if negative, change it to R if you want the +, too
                	($diff->s)+ // seconds (no errors)
                	(60*($diff->i))+ // minutes (no errors)
	                (60*60*($diff->h))+ // hours (no errors)
        	        (24*60*60*($diff->d))+ // days (no errors)
                	(30*24*60*60*($diff->m))+ // months (???)
	                (365*24*60*60*($diff->y)) // years (???)
                	);
		return $diff_sec;
		}
	/**
	 * Difference between now() and current datetime in Days
	 */
	function DaysToNow(){ // as datetime object returns difference in seconds
		if(empty(self::$dtnow)){
			self::$dtnow=new \DateTime();
			}
		//Get difference
		$diff=$this->diff(self::$dtnow);
		$diff_days =$diff->format('%r').($diff->days);
		return $diff_days;
		}

	/**
	 * Compare current date/time with other date/time
	 *
	 * Return 1 if $dt2 > $this
	 * Return 0 if $dt2 == $this
	 * Return -1 if $dt2 < $this
	 */
	public function bcompare($dt2){
		//Get the difference between two DateTime objects
		$bdiff=$this->diff($dt2);
		//Debug:
		//var_dump($this); echo('<hr>'); var_dump($dt2); echo('<hr>'); var_dump($bdiff); die();

		//Interval is negative - $dt2>$this
		if($bdiff->invert==1){
			return 1;
			}
		//The dates are equal
		if(($bdiff->y==0)&&($bdiff->m==0)&&($bdiff->d==0)&&
                   ($bdiff->h==0)&&($bdiff->i==0)&&($bdiff->s==0)){
			return 0;
			}
		return -1;
		}
	/**
	 *
	 */
	public function prettyDateTime($format=1){
		$now=new \DateTime();
		if($now->format('Ymd')==$this->format('Ymd')){
			return $this->format(BLang::sprintf('PRETTYDATETIME_TODAY'));
			}
		if($now->add(\DateInterval::createFromDateString('yesterday'))->format('Ymd')==$this->format('Ymd')){
			return $this->format(BLang::sprintf('PRETTYDATETIME_YESTERDAY'));
			}
		return $this->format(BLang::sprintf('PRETTYDATETIME_COMMON',
			BLang::_('PRETTYDATETIME_MONTH_'.$this->format('M')),
			BLang::_('PRETTYDATETIME_INTIME')		
			));
		}
	/**
	 * Format:
	 *   05.08 11:25 (current year)
	 *   02.12.2014 23:50 (other years)
	 */
	public function prettydatetime2(){
		return $this->prettydate().' '.$this->format('H:i');
		}

	/**
	 *
	 */
	public function format_sql(){
		return $this->format('Y-m-d H:i:s');
		}
	}
