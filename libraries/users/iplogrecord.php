<?php
/**
 * Basic class work with IP log record.
 *
 * @author Andrii Biriev
 * @author Andrii Karepin
 * @copyright © Brilliant IT corporation, www.it.brilliant.ua
 */
bimport('cms.datetime');

class BUsersIPLogRecord{
	/**
	 *
	 */
	public function load($obj){
		$this->user=(int)$obj['user'];
		$this->dt=empty($obj['dt'])?NULL:new BDateTime($obj['dt']);
		$this->IPv4=$obj['IPv4'];
		$this->useragent=$obj['UserAgent'];
		}
	/**
	 *
	 */
	public function IPAddr(){
		return $this->IPv4;
		}
	/**
	 * Get country code by IP.
	 */
	public function IP2Country(){
		return $this->IPv4;
		}

	}