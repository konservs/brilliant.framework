<?php
//============================================================
// Basic class for user
//============================================================
namespace Brilliant\Users;

use Brilliant\BFactory;
use Brilliant\BDateTime;
use Brilliant\Log\BLog;
use Brilliant\Users\BUser;

class BUser{
	public $id;
	public $city_id;
	public $name;
	public $tels=array();//DEPRECATED.
	public $phones=array();
	public $ads_published; //Count of published ads (modified by trigger).
	/**
	 *
	 */
	public function load($obj){
		$this->id=(int)$obj['id'];
		$this->alias=$obj['alias'];
		$this->email=$obj['email'];
		$this->password=$obj['password'];
		$this->avatar=$obj['avatar'];
		$this->name=$obj['name'];
		//deprecated values
		$this->tels=$obj['tels'];

		$this->phones=array();
		if(is_array($obj['tels'])){
			foreach($obj['tels'] as $tel){
				$phone=new BPhone();
				$this->phones[]=$phone;
				$phone->init_array($tel);
				}
			}
		$this->gplus_id=$obj['gplus_id'];
		$this->active=$obj['active'];
		$this->city_id=$obj['city'];
		$this->birthday=NULL;
		if(!empty($obj['birthday'])){
			$this->birthday=new BDateTime($obj['birthday']);
			}
		if(!empty($obj['last_action'])){
			$this->last_action=new BDateTime($obj['last_action']);
			}
		if(!empty($obj['dt_lastfreepublications'])){
			$this->dt_lastfreepublications=new BDateTime($obj['dt_lastfreepublications']);
			//$this->dt_nextfreepublications=$this->dt_lastfreepublications->add(new DateInterval('P10D')); //+3 monthes
			$this->dt_nextfreepublications=$this->dt_lastfreepublications->modify('+3 month');
			}
		else{
			$this->dt_lastfreepublications=NULL;
			$this->dt_nextfreepublications=NULL; //+3 monthes
			}

		$this->confirmlink=$obj['confirmlink'];
		$this->confirmsms=$obj['confirmsms'];
		$this->photo=$obj['photo'];
		if(!empty($obj['created'])){
			$this->created=new BDateTime($obj['created']);
			}
		//
		$this->subscribes=array();
		if(!empty($obj['subscribes'])){
			$this->subscribes=json_decode($obj['subscribes'],true);
			}
		$this->lang=$obj['lang'];
		if(empty($this->lang)){
			$this->lang='ru';
			}
		//Some int values (statistics by triggers.)
		$this->ads_published=(int)$obj['ads_published'];
		}
	/**
	 *
	 */
	public function preparetime($str){
		$s=$str;
		if((is_numeric($s))&&((int)$s>=0)&&((int)$s<=24)){
			return $s.'00:00';
			}
		$s=str_replace('-',':',$s);
		$s=str_replace('/',':',$s);
		$arr=explode(':',$s);
		if(count($arr)==2){
			return $arr[0].':'.$arr[1];
			}
		if(count($arr)==3){
			return $arr[0].':'.$arr[1].':'.$arr[2];
			}
		return $s;
		}
	/**
	 *
	 */
	public function setphones($arr){
		foreach($arr as &$ar){
			$ar['tel']=str_replace('-','',$ar['tel']);
			$ar['tel']=str_replace('(','',$ar['tel']);
			$ar['tel']=str_replace(')','',$ar['tel']);
			$ar['tel']=str_replace(' ','',$ar['tel']);
			}
		$phones=array();
		foreach($this->phones as $phk=>$phone){
			foreach($arr as $pht=>$tel){
				if(empty($this->phones[$phk])){
					continue;
					}
				if($phone->tel==$tel['tel']){					
					$phone->call_from=$this->preparetime($tel['from']);
					$phone->call_to=$this->preparetime($tel['to']);
					$phone->call_name=$tel['name'];
					$phones[]=$phone;
					unset($this->phones[$phk]);
					unset($arr[$pht]);
					}
				}
			}
		if(!empty($this->phones)){
			if(!$db=BFactory::getDBO()){
				return false;
				}
			
			foreach($this->phones as $phone){
				$this->changed_fields[]=array('field'=>'phone','prevval'=>(string)$phone->tel,'nextval'=>'Удален');
				$qr='delete from users_phones where id='.$phone->id.'&&user='.$this->id;
				$q=$db->Query($qr);
				if(empty($q)){
					return false;
					}
				}
			}
		$this->phones=$phones;
		foreach($arr as $ph){
			$this->changed_fields[]=array('field'=>'phone','prevval'=>'не было','nextval'=>$ph['tel']);
			$phone=new BPhone();
			$phone->user=$this->id;
			$phone->tel=$ph['tel'];
			$phone->call_to=$ph['to'];
			$phone->call_from=$ph['from'];
			$phone->call_name=$ph['name'];
			$phone->checked=0;
			$this->phones[]=$phone;
			}
		return true;//TODO 
		
		$this->phones=array();
		if(!is_array($arr)){
			return false;
			}
		foreach($arr as $tel){
			$phone=new BPhone();
			$this->phones[]=$phone;
			$tel['checked']=false;
			$phone->init_array($tel);
			}
		}
	/**
	 *
	 */
	public function updatecache(){
		$bCache=BFactory::getCache();
		$bCache->delete('users:userid:'.$this->id);
		$bCache->delete('users:useremail:'.$this->email);
		}
	/**
	 *
	 */
	public function validatetels(){
		if(!$db=BFactory::getDBO()){	
			return false;
			}
		$err=array();
		if(is_array($this->tels)){
			foreach($this->tels as $k=>$tel){
				if(empty($tel['tel'])){
					continue;
					$err["tels[$k][tel]"]=1;
					}
				if(strlen($tel['tel'])!==9){
					$err["tels[$k][tel]"]=2;
					}
				if(!ctype_digit($tel['tel'])){
					$err["tels[$k][tel]"]=3;
					}
				if(!empty($err["tels[$k][tel]"])){
					continue;
					}
				/*
				$qr='select id from users_phones where tel='.$tel['tel'].(!empty($tel['id'])?'&& id not in ('.$tel['id'].')':'');
				$q=$db->Query($qr);
				if($q->num_rows!=0){
					$err["tels[$k][tel]"]=4;
					}
				*/
				}
			}
		return $err;	
		}
	/**
	 *
	 */
	public function validate(){
		$db=BFactory::getDBO();
		$err=array();
		if(empty($this->name)){
			$err['name']=1;
			}
		//if(empty($this->email)){
		//	$err['email']=1;
		//	}
		//if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)){
		//	$err['email']=2;
		//	}
		//$err=array_merge($err,$this->validatetels());
		return $err;
		}
	/**
	 * Store password into database & clear cache.
	 */
	public function savepassword(){
		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$qr='update users set password='.$db->escape_string($this->password).' where id='.$this->id;
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$this->updatecache();
		return true;
		}
	/**
	 *
	 */
	public function getphones(){
		if(!is_array($this->tels)){
			return array();
			}
		return $this->tels;
		}
	/**
	 *
	 */
	public function getfirstphone(){
		if(!is_array($this->tels)){
			return '';
			}
		$tel=$this->tels[0]['tel'];
		$str='0'.sprintf('%09d', $tel);
		return '('.substr($str,0,3).') '.substr($str,3,3).'-'.substr($str,6,2).'-'.substr($str,8,2);
		}
	/**
	 *
	 */
	public function setemail($email){
		if(empty($email)){
			return false;
			}
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$q=$db->Query('select id from users where email='.$db->escape_string($email));
		if($q->num_rows>1){
			return false;
			}
		if($q->num_rows==0){
			$this->updatecache();
			$this->changed_fields[]=array('field'=>'email','prevval'=>$this->email,'nextval'=>$email);
			$this->email=$email;
			return true;
			}
		$l=$db->fetch($q);
		if($l['id']!=$this->id){
			return false;
			}
		return true;
		}
	/**
	 *
	 */
	public function db_insert(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='insert into users (name,password,email,active,created,modified,birthday)values(';
		$qr.=$db->escape_string($this->name);
		$qr.=','.$db->escape_string($this->password);
		$qr.=','.(empty($this->email)?'NULL':$db->escape_string($this->email));
		$qr.=','.$db->escape_string($this->active);
		$qr.=',now(),now()';
		$qr.=','.(empty($this->birthday)?'NULL':$db->escape_string($this->birthday->format('Y-m-d')));
		$qr.=')';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$this->id=$db->insert_id();
		$this->updatecache();
		return true;
		}
	/**
	 *
	 */
	public function db_update(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		//$db->start_transaction();
		$qr='update `users` set';
		$qr.=' name='.$db->escape_string($this->name);
		$qr.=',password='.$db->escape_string($this->password);
		$qr.=',email='.(empty($this->email)?'NULL':$db->escape_string($this->email));
		//$qr.=',active='.(int)$this->active;
		if($this->birthday instanceof DateTime){
			$qr.=',birthday='.$db->escape_string($this->birthday->format("Y-m-d"));
			}
		$qr.=',avatar='.$db->escape_string($this->avatar);
		$qr.=' where id='.(int)$this->id;
		$db->Query($qr);
		$qr='';
		if(!$this->savechanged_fields()){
			//$db->rollback();
			return false;
			}
		//$db->commit();
		$this->updatecache();
		return true;
		}
	/**
	 *
	 */
	public function savechanged_fields(){
		if(empty($this->changed_fields)){
			return true;
			}
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='insert into users_fieldslog (user,dt,field,prevval,nextval) values';
		$fields=array();
		foreach($this->changed_fields as $fld){
			$fields[]='('.$this->id.',NOW(),'.$db->escape_string($fld['field']).','.$db->escape_string($fld['prevval']).','.$db->escape_string($fld['nextval']).')';
			}
		if(empty($fields)){
			return false;
			}
		$qr.=implode(',',$fields);
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		return true;
		}
	/**
	 * Update user information in the database
	 */
	public function savetodb(){
		$err=$this->validate();
		if(!empty($err)){
			return false;
			//return $err;
			}
		if(!$db=BFactory::getDBO()){
			return false;
			}
		if(isset($this->id)){
			return $this->db_update();
			}
		return $this->db_insert();	
		}
	/**
	 *
	 */
	public function setpassword($pass){	
		$busers=BUsers::getInstance();
		$hash=$busers->makepass($this->email,$pass);
		if(DEBUG_MODE){
			BLog::addtolog('[BUsers.single]: updaing password. hash='.$hash);
			}
		$this->changed_fields[]=array('field'=>'password','prevval'=>'','nextval'=>'');
		$this->password=$hash;
		return true;
		}
	/**
	 * Google+ page of user
	 */
	public function gplus_url(){
		if(empty($this->gplus_id))
			return '';
		return '//plus.google.com/'.$this->gplus_id;
		}
	/**
	 * Avatar image
	 */
	public function deleteavatar(){
		if(!file_exists(MEDIA_PATH_ORIGINAL.'/users/'.$this->id.'/face.jpg')){
			return true;
			}
		return unlink(MEDIA_PATH_ORIGINAL.'/users/'.$this->id.'/face.jpg');
		}
	/**
	 * Avatar image
	 */
	public function hasavatar(){
		if(!file_exists(MEDIA_PATH_ORIGINAL.'/users/'.$this->id.'/face.jpg')){
			return false;
			}
		return true;
		}
	/**
	 *
	 */
	public function getavatarimg($par){
		bimport('images.single');
		$img=new BImage();
		$img->url=$this->avatar;//'/users/'.$this->id.'/face.jpg';
		if((empty($this->avatar))||(!$img->isfile())){
			$img->url='users/empty/avatar.face.jpg';
			}
		return $img->drawimg($par,$this->getname());
		}
	/**
	 * Avatar image??? Depracated?
	 */
	public function avatar_url($options=array()){
		$rsuffix='.s100';
		if(isset($options['resize'])&&($options['resize']!='')){
			$rsuffix='.'.$options['resize'];
			}
		if(isset($options['type'])&&($options['type']!='')){
			$tsuffix='.'.$options['type'];
			}else{
			$tsuffix='.face';
			}
		//
		$path_user=PATH_AVATARS.$this->id.DIRECTORY_SEPARATOR;
		if(file_exists($path_user.'avatar.face.jpg')){
			return URL_AVATARS.$this->id.'/avatar'.$tsuffix.$rsuffix.'.jpg';
			}
		return URL_AVATARS.'default/avatar'.$tsuffix.$rsuffix.'.jpg';
		}
	/**
	 *
	 */
	public function getCity(){
		if(empty($this->city_id)){
			return NULL;
			}
		bimport('regions.general');
		$bregions=BRegions::getInstance();
		return $bregions->city_get($this->city_id);
		}
	/**
	 *
	 */
	public function setCity($id){
		if($this->city_id!=$id)
			$this->changed_fields[]=array('field'=>'city','prevval'=>$this->city_id,'nextval'=>$id);
		$this->city_id=$id;
		return true;		
		}
	/**
	 * Get user name
	 */
	public function getName($charslimit=0){
		$username=$this->name;
		if(($charslimit)&&(mb_strlen($username,'UTF-8')>$charslimit)){
			$username=mb_substr($username,0,$charslimit,'UTF-8').'...';
			}

		return $username;
		}
	/**
	 * Get user email
	 */
	public function getEmail(){
		return $this->email;
		}
	/**
	 *
	 */
	public function getfullyears(){
		if(empty($this->birthday)){
			return NULL;
			}
		return $this->birthday->diff(new DateTime)->y;
		}
	/**
	 *
	 */
	public function delete(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$this->updatecache();
		$qr='update users set email="",active=3 where (id='.$this->id.')';
		$q=$db->Query($qr);
		$this->updatecache();
		}
	/**
	 *
	 */
	public function getsociallinks(){
		bimport('users.social.users');
		$bsu=BUsersSocialUsers::getInstance();
		$list=$bsu->items_filter(array('user'=>$this->id));
		return $list;
		}
	}
