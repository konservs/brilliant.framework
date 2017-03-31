<?php
//============================================================
// Basic class for user
//
//============================================================

class BUserVirtual{
	public function load($obj){
		$this->id=$obj['id'];
		$this->start=new BDateTime($obj['start']);
		$this->end=new BDateTime($obj['end']);
		$this->lastaction=new BDateTime($obj['lastaction']);
		$this->sessionid=$obj['sessionid'];
		$this->ipv4=$obj['ipv4'];
		$this->data=$obj['data'];
		$data=new BDatetime();
		$data->sub(date_interval_create_from_date_string('1 day'));
		$diff=$data->diff($this->lastaction);
		if($diff->invert){
			$db=BFactory::getDBO();
			$qr='update sessions_virtual set end=NOW()+INTERVAL 1 MONTH,lastaction=NOW()';
			$q=$db->Query($qr);
			}

		}
	public function init(){
		$sessionid=$_COOKIE['vuser'];
		if(empty($sessionid)){
			return $this->startnewsession();
			}
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='select * from sessions_virtual where sessionid='.$db->escapeString($sessionid);
		$q=$db->Query($qr);
		if(empty($q)){
			return $this->startnewsession();
			}
		$l=$db->fetch($q);
		if(empty($q)){
			return $this->startnewsession();
			}
		$this->load($l);
		}
	public function startnewsession(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		
		$sessid=sha1(uniqid(rand(),1));
		$nowdate=new DateTime();
		$enddate=new DateTime();
		$enddate->add(date_interval_create_from_date_string('1 month'));

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
			}
		$ipv4=ip2long($ip);
		$qr='insert into sessions_virtual (sessionid,ipv4,start,end,lastaction)values('.$db->escapeString($sessid).','.
			$ipv4.',NOW(),NOW()+INTERVAL 1 MONTH,NOW())';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$this->id=$db->insertId();
		$this->sessionid=$sessid;
		$this->lastaction=clone $nowdate;
		$this->start=clone $nowdate;
		$this->end=clone $enddate;
		$this->ipv4=$ipv4;
		$expire=time()+2629743;
		setcookie('vuser',$sessid,$expire,'/',BHOSTNAME);
		}
	}
