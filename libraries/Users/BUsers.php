<?php
/**
 * Basic class to control users, to list users, to login or 
 * logout and all other operations with users.
 *
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant\Users;

use Brilliant\BFactory;
use Brilliant\Log\BLog;
use Brilliant\BDateTime;
use Brilliant\Users\BUser;
use Brilliant\Users\BUsersSession;


//============================================================
//Some defines: register errors & user statuses
//============================================================
define('USERS_ERROR_OK',0);
define('USERS_ERROR_UNKNOWN',999);
define('REGISTER_ERROR_UNKNOWN',999);
define('REGISTER_ERROR_PASSWORDNOTMATCH',1);
define('REGISTER_ERROR_NOTVALIDEMAIL',2);
define('REGISTER_ERROR_EMAILISINBASE',3);
define('REGISTER_ERROR_TELINBASE',4);
define('REGISTER_ERROR_TELNOTVALID',5);
define('REGISTER_ERROR_DIDNOTAGREE',6);
define('REGISTER_ERROR_NOTCORRECTCAPTCHA',7);
define('REGISTER_ERROR_NOREGION',8);
define('REGISTER_ERROR_NOCITY',9);
define('USERS_ERROR_NOSUCHEMAIL',10);
define('USERS_ERROR_DBERROR',11);
define('USERS_ERROR_CODEWRONG',12);
define('USERS_ERROR_COULDNOTDELETE',13);
define('USERS_ERROR_PASS',14);
define('ERROR_SPIVPADAYUT','is_user');
define('USERS_ERROR_OK_OK','ok');
define('USERS_ERROR_NOTUSER','notuser');

define('USER_STATUS_ACTIVE',0);
define('USER_STATUS_NOTACTIVATED',1);
define('USER_STATUS_BANNED',2);
//============================================================
// Main class to control lists of users.
//============================================================
class BUsers{
	protected static $instance=NULL;
	public $users_cache=array();
	/**
	 * Returns the global BUsers object, only creating it
	 * if it doesn't already exist.
	 */
	public static function getInstance(){
		if(!is_object(self::$instance)){
			self::$instance=new BUsers();
			}
		return self::$instance;
		}
	/**
	 *
	 */
	public function deleteusers($ids){
		if(!is_array($ids)){
			return false;
			}
		foreach($ids as $id){
			$user=$this->get_single_user($id);
			$user->delete();
		}
		
	
		}
	/**
	 * Get single user by ID
	 */
	public function get_single_user($id){
		$list=$this->users_get(array($id));
		return isset($list[$id])?$list[$id]:NULL;
		}
	//====================================================
	// Get users array by array of ids.
	//====================================================
	public function users_get($ids){
		$users=array();
		//-------------------------------------------------
		//Trying to get users from internal cache
		//-------------------------------------------------
		$ids_c=array(); //IDs as integer
		$ids_k=array(); //IDs as external cache key
		foreach($ids as $id)
			if(isset($this->users_cache[$id])){
				$users[$id]=$this->users_cache[$id];
				}else{
				if($id>0){
					$users[$id]=NULL;
					$ids_c[$id]=$id;
					$ids_k[$id]='users:userid:'.$id;
					}
				}
		if(empty($ids_c))
			return $users;
		//-------------------------------------------------
		//Trying to get left users from external cache
		//-------------------------------------------------
		$user_obj=array();//cache objects...
		$ids_q='';
		$cache = BFactory::getCache();
		if($cache){
			$ids_m=array();
			$users_c=$cache->mget($ids_k);
			foreach($ids_c as $id){
				$key='users:userid:'.$id;
				if((isset($users_c[$key]))&&(!empty($users_c[$key]))){
					$users[$id]=new BUser();
					$users[$id]->load($users_c[$key]);
					$this->users_cache[$id]=$users[$id];
					//ToDO: key heating check and actions can be here
					}else{
					array_push($ids_m,$id);
					$ids_q.=(empty($ids_q)?'':',').$id;
					}
				}
			}else{
			$ids_m=$ids_c;
			foreach($ids_m as $id)
				$ids_q.=(empty($ids_q)?'':',').$id;
			}
		if(empty($ids_m))
			return $users;
		//-------------------------------------------------
		// Trying to get left users from database
		//-------------------------------------------------
		$db=BFactory::getDBO();
		if(empty($db)){
			return $users;
			}
		$qr='SELECT * from `users` WHERE (id in ('.$ids_q.'))';
		$q=$db->Query($qr);
		if(empty($q)){
			BLog::addtolog('[Users]: users_get(): Could not execute query! MySQL error: '.$db->lasterror());
			return $users;
			}
		$tocache=array();
		while($l=$db->fetch($q)){
			$id=(int)$l['id'];
			$user_obj[$id]=$l;
			}
		foreach($user_obj as $k=>$l){
			$users[$k]=new BUser();
			$users[$k]->load($l);
			$this->users_cache[$k]=$users[$k];
			if(CACHE_TYPE){
				$tocache['users:userid:'.$k]=$l;
				$tocache['users:useremail:'.$users[$k]->email]=$l;
				}
			}

		if(CACHE_TYPE&&count($tocache)!=0){
			$cache->mset($tocache,3600);//1 hour
			}
		return $users;
		} //end of users_get($ids)
	//====================================================
	// Get user class by email
	//====================================================
	public function getUserByEmail($email){
		foreach($this->users_cache as $user){
			if($user->email==$email){
				return $user;
				}
			}
		$cache=BFactory::getCache();
		if($cache){
			$userfc=$cache->get('users:useremail:'.$email);
			}
		if($userfc!=false){
			$user=new BUser();
			$user->load($userfc);
			return $user;
			}
		$db=BFactory::getDBO();
		if(empty($db)){
			return NULL;
			}
		//Get users...
		$qr='SELECT * from `users` WHERE (email='.$db->escape_string($email).' )';
		$q=$db->Query($qr);
		if(empty($q)){
			return NULL;
			}
		$l=$db->fetch($q);
		if(empty($l)){
			return NULL;
			}
		//Get user...
		$id=(int)$l['id'];
		$user_obj=$l;
		BLog::addtolog('[Users] found user: '.$id);
		//Process users...
		$tocache=array();
		$res=new BUser();
		$res->load($user_obj);
		$this->users_cache[$id]=$res;
		if(CACHE_TYPE){
			$tocache['users:userid:'.$id]=$user_obj;
			$tocache['users:useremail:'.$res->email]=$user_obj;
			$cache->mset($tocache,3600);//1 hour
			}
		return $res;
		}
	/**
	 * Get WHERE SQL filter.
	 * Using in admin panel.
	 */
	public function iplog_filter($params=array()){
		$where='';

		if(!empty($params['user'])){
			$where.=(($where!='')?'&&':'').'(user='.(int)$params['user'].')';
			}
		return $where;
		}
	/**
	 *
	 */
	public function iplog_count($params=array()){
		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$where=$this->iplog_filter($params);

		$qr='select count(*) as cnt from users_iplog';
		if($where!=''){
			$qr.=' where '.$where;
			}
		$q=$db->query($qr);
		if(empty($q)){
			return false;
			}
		if(!$l=$db->fetch($q)){
			return 0;
			}
		return (int)$l['cnt'];
		}
	/**
	 *
	 */
	public function iplog_select($params=array(),$limit=10,$offset=0){
		bimport('users.iplogrecord');

		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$where=$this->iplog_filter($params);

		$qr='select * from users_iplog';
		if($where!=''){
			$qr.=' where '.$where;
			}
		if(!empty($params['ordering'])){
			$qr.=' order by `'.$params['ordering'].'`';
			if(!empty($params['orderingdir'])){
				switch($params['orderingdir']){
					case 'asc':
						$qr.=' ASC';
						break;
					case 'desc':
						$qr.=' DESC';
						break;
					}
				}
			}
		if($limit!=0){
			$qr.=' limit '.$limit.' offset '.$offset; 
			}
		$q=$db->query($qr);
		if(empty($q)){
			return false;
			}
		$res=array();
		while($l=$db->fetch($q)){
			$r=new BUsersIPLogRecord();
			$r->load($l);
			$res[]=$r;
			}
		return $res;		
		}
	//====================================================
	// Get all users
	//
	// $offset - offset of list
	// $limit - count of users block
	// $params - params to filter users
	//====================================================
	public function getusers($limit=10,$offset=0,$params=array()){
		bimport('sql.mysql');
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db))return;

		$qr='select distinct id from `users`';
		$where='';
		$jn=array();
		if(!empty($params['active'])){
			$where.=(($where!='')?'&&':'').'(active=1)';
			}
		if(!empty($params['keyword'])){
			$params['keyword']=mb_strtolower($params['keyword'],'utf8');
			$where.=(($where!='')?'&&':'').'((lower(name) like '.$db->escape_string('%'.$params['keyword'].'%').')or'.
				       '(lower(email) like '.$db->escape_string('%'.$params['keyword'].'%').'))';
			}
		if(!empty($params['ip'])){
			$where.=(($where!='')?'&&':'').'(IPv4='.sprintf('%u',ip2long($params['ip'])).')';
			$jn['iplog']=' left join users_iplog on users_iplog.user=users.id';
			}
		if(!empty($params['city'])){
			$where.=(($where!='')?'&&':'').'(city='.(int)$params['city'].')';
			}
		if(!empty($jn)){
			$qr.=' '.implode(' ',$jn);
			}
		if($where!=''){
			$qr.=' where '.$where;
			}
		if(!empty($params['ordering'])){
			$qr.=' order by `'.$params['ordering'].'`';
			if(!empty($params['orderingdir'])){
				switch($params['orderingdir']){
					case 'asc':
						$qr.=' ASC';
						break;
					case 'desc':
						$qr.=' DESC';
						break;
					}
				}
			}

		if($limit!=0){
			$qr.=' limit '.$limit.' offset '.$offset; 
			}
		$q=$db->Query($qr);
		if(empty($q)){
			return array();
			}
		while($l=$db->fetch($q))
			$ids[]=$l['id'];
		if(count($ids)==0){
			return array();
			}
		return $this->users_get($ids);		
		}

	/**
	 * 	Get users list count
	 *
	 * @param $params params to filter users
	 * @return int|void
	 */
	public function getusers_count($params){
		bimport('sql.mysql');
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db))return;

		$qr='select count(distinct users.id) as cnt from `users`';
		$where='';
		$jn=array();
		if(!empty($params['active'])){
			$where.=(($where!='')?'&&':'').'(active=1)';
			}
		if(!empty($params['keyword'])){
			$where.=(($where!='')?'&&':'').'((name like '.$db->escape_string('%'.$params['keyword'].'%').')or'.
				       '(email like '.$db->escape_string('%'.$params['keyword'].'%').'))';
			}
		if(!empty($params['ip'])){
			$where.=(($where!='')?'&&':'').'(IPv4='.sprintf('%u',ip2long($params['ip'])).')';
			$jn['iplog']=' left join users_iplog on users_iplog.user=users.id';
			}
		if(!empty($params['city'])){
			$where.=(($where!='')?'&&':'').'(city='.(int)$params['city'].')';
			}
		if(!empty($jn)){
			$qr.=' '.implode(' ',$jn);
			}
		if($where!=''){
			$qr.=' where '.$where;
			}
		$q=$db->Query($qr);
		if(empty($q)){
			if(DEBUG_MODE){
				BLog::addtolog('[Users]: getusers_count(): Could not execute query! MySQL error: '.$db->lasterror(),LL_ERROR);
				}
			return 10000;
			}
		$l=$db->fetch($q);
		return $l['cnt']; 
		}
	/**
	 * Get logged user class
	 *
	 * @return BUser|null
	 */
	public function getLoggedUser(){
		$session=BUsersSession::getInstanceAndStart();
		if(empty($session)){
			return NULL;
			}
		return $this->get_single_user($session->userid);
		}
	/**
	 * Login. Returns user object
	 *
	 * @param $email
	 * @param $password
	 * @param bool|false $longsession
	 * @return BUser|int|null
	 */
	public function login($email,$password,$longsession=false){
		$user=$this->getUserByEmail($email);
		if($user==false){
			BLog::addtolog('[Users]: login() wrong email!',LL_ERROR);
			return USERS_ERROR_NOSUCHEMAIL;
			}
		if($user->active==USER_STATUS_NOTACTIVATED){
			BLog::addtolog('[Users]: Not Activated',LL_ERROR);
			return USERS_ERROR_NOACTIVATED;
			}
		if($user->active==USER_STATUS_BANNED){
			BLog::addtolog('[Users]: Banned user',LL_ERROR);
			return USERS_ERROR_BANNED;
			}			
		$hash=$this->makepass($email,$password);
		if($user->password!=$hash){
			BLog::addtolog('[Users]: password hashes not equal! user hash='.$user->password.'; post hash='.$hash,LL_ERROR);
			return USERS_ERROR_PASS;
			}
		$options=array(
			'interval'=>$longsession?2592000:10800,
			'updatestep'=>60,
			);
		$sess=BUsersSession::getInstance();
		$sess->newSession($user->id,$options);
		return USERS_ERROR_OK;
		}
	/**
	 * returns {} if ok and array of errors if not
	 *
	 * @param $fields
	 * @return array|bool
	 */
	public function check($fields){
		$r=array();
		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}				
		//Check region & city
		if(!is_numeric($fields['region'])){
			$r['region']=REGISTER_ERROR_NOREGION;
			}
		if(!is_numeric($fields['city'])){
			$r['city']=REGISTER_ERROR_NOCITY;
			}
			
		//Check captcha
		bimport('captcha.general');
		if (!BCaptcha::getInstance()->Check())
			$r['captcha']=REGISTER_ERROR_NOTCORRECTCAPTCHA;		
		
		//Check passwords
		if($fields['password']!=$fields['password_check']){
			$r['password']=REGISTER_ERROR_PASSWORDNOTMATCH;
			}
		if(empty($fields['password'])){
			$r['password']=REGISTER_ERROR_PASSWORDNOTMATCH;
			}
		if(empty($fields['agree'])){
			$r['agree']=REGISTER_ERROR_DIDNOTAGREE;
			}			
		if(!strpos($fields['email'],'@')){
			$r['email']=REGISTER_ERROR_NOTVALIDEMAIL;
			}else{
			$qr='select * from `users` where `email`='.$db->escape_string($fields['email']);
			$q=$db->Query($qr);
			if($q->num_rows!==0){
				$r['email']=REGISTER_ERROR_EMAILISINBASE;
				}
			}
		//
		if(isset($fields['tels'])){
			$qrw='';
			foreach($fields['tels'] as $key=>$tel){
				$tel['tel']=$this->phone2numeric($tel['tel']);
				if((!is_numeric($tel['tel']))||(strlen($tel['tel'])!=9)){
					$r['tels'][$key]=REGISTER_ERROR_TELNOTVALID;
					continue;
					}
				$qr='select * from `users_phones` where tel='.$tel['tel'];
				$q=$db->Query($qr);

				//Automatically change fields log history..
				$qr2='insert into users_fieldslog (user,dt,field,prevval,nextval) values';
				$fields=array();

				if($q->num_rows!=0){
					$l=$db->fetch($q);
					if(($l['checkcode']==$tel['code'])&&(!empty($tel['code']))){
						continue;
						}
					$r['tels'][$key]=REGISTER_ERROR_TELINBASE;
					$code=rand(0,9999);
					//adding leading zeros
					while(strlen($code)<4){
						$code='0'.$code;
						}
					if(DEBUG_MODE){
						BLog::addtolog('[Users]: check(): phone('.$tel['tel'].') is already registered. Sending code='.$code,LL_ERROR);
						}
					$db->Query('update users_phones set checkcode='.$code.' where id='.$l['id']);

					bimport('sms.turbosms');

					$sms=BTurboSMS::getInstance();
					$sms->dst_phone='0'.$tel['tel'];
					$sms->text=BLang::_('SMS_USERS_PHONE_REGISTERCODE').' '.$code;	
					$res=$sms->send();
					}
				
				}//foreach
			if(!empty($qrw)){

				}
			}
		return $r;
		}
	/**
	 *
	 * Method that register user (insert his data to the
	 * DB). Return true or array with error codes.
	 *
	 * @param $fields
	 * @return bool
	 */
	public function register($fields){
		if(DEBUG_MODE){
			BLog::addtolog('[Users]: register()');
			}
		$r=$this->check($fields);
					
		if(!empty($r)){
			if(DEBUG_MODE){
				BLog::addtolog('[Users]: register check failed! Errors:'.var_export($r,true),LL_ERROR);
				}
			return $r;
			}
		//-----------------------------------------------
		// User wants the city not from database?
		// Sending request to moderator.
		//-----------------------------------------------
		if(!empty($fields['city_offer'])){
			bimport('email.templates');
			$e=new BEmailTemplate();
			$e->loadtemplate('users.offer_city');
			$e->email='yuragalin@mail.ru';
			$e->set('city_offer',$fields['city_offer']);
			$e->set('name',$fields['name']);
			$e->set('email',$fields['email']);
			$e->set('city',$fields['city']);
			$e->set('region',$fields['region']);
			$e->send();			
			}
		bimport('sql.mysql');
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db))return;
		$db->start_transaction();

		$confirmlink=sha1(uniqid(rand(),1));
		$lang=empty($fields['lang'])?'ru':$fields['lang'];

		$qr='insert into `users` (email,password,name,confirmlink,active,city,lang,created,lastmodified) values';
		$qr.='('.$db->escape_string($fields['email']).','
			.$db->escape_string($this->makepass($fields['email'],$fields['password'])).','
			.$db->escape_string($fields['name']).','
			.$db->escape_string($confirmlink).','.USER_STATUS_NOTACTIVATED.','
			.$fields['city'].','
			.$db->escape_string($lang).','
			.'NOW(),NOW())';
		if(DEBUG_MODE){
			BLog::addtolog('[Users]: register() inserting user fields...');
			}
		$q=$db->Query($qr);
		if(empty($q)){
			$db->rollback();
			if(DEBUG_MODE){
				BLog::addtolog('[Users]: register(): Could not execute query! MySQL error: '.$db->lasterror(),LL_ERROR);
				}
			return false;
			}
		$userid=$db->insert_id();
		
		if(isset($fields['tels'])){
			foreach($fields['tels'] as &$ar){
				$ar['tel']=str_replace('-','',$ar['tel']);
				$ar['tel']=str_replace('(','',$ar['tel']);
				$ar['tel']=str_replace(')','',$ar['tel']);
				$ar['tel']=str_replace(' ','',$ar['tel']);
				}
			$qr='';
			foreach($fields['tels'] as $tel){
				
				if((strlen($tel['tel'])==10)&&($tel['tel'][0]=='0')){
					$tel['tel']=substr($tel['tel'],1);
					}
				if(empty($tel['code'])){
					$qr.=empty($qr)?'(':',(';
					$qr.=$userid.',380,';
					$qr.=$tel['tel'].','.$db->escape_string($tel['from']).','.$db->escape_string($tel['to']);
					$qr.=','.$db->escape_string($tel['name']);			
					$qr.=',0)';
					}
				else{
					$db->Query('update `users_phones` set user='.$userid.',checked=0 where tel='.$tel['tel']);
					}
				}
			if(DEBUG_MODE){
				BLog::addtolog('[Users]: register() inserting user phones...');
				}
			if(!empty($qr)){
				$qr='insert into `users_phones` (user,op_code,tel,call_from,call_to,call_name,checked) values'.$qr;
				$db->Query($qr);
				}
			}
		//Send email template with completed registration
		bimport('email.templates');
		$e=new BEmailTemplate();
		$e->loadtemplate('users.newuser');
		$e->email=$fields['email'];
		$e->set('name',$fields['name']);
		bimport('cms.language');
		$lang=BLang::$langcode;
		$brouter=BRouter::getInstance();
		$e->set('confirmlink','http://'.$brouter->generateurl('users',$lang,array('view'=>'confirm','id'=>$userid,'confirmcode'=>$confirmlink)));
		$e->send();
		//Adding free publications on register
		bimport('finances.general');
		$bfinances=BFinances::getInstance();
		$bfinances->addpublicationstouser($userid);
		//Commiting...
		$db->commit();
		//All done!
		return true;
		}
	/**
	 * Confirm user email.
	 *
	 * @param $uid
	 * @param $code
	 * @return bool
	 */
	public function confirm($uid,$code){
		bimport('sql.mysql');
		$db=BMySQL::getInstanceAndConnect();
		$qr='select * from users where id='.$uid;
		$q=$db->Query($qr);
		if(empty($q))return false;
		$l=$db->fetch($q);
		if(empty($l))return false;
		if($l['active']==USER_STATUS_ACTIVE){
			return false;
			}
		if($code==$l['confirmlink']){
			$q=$db->Query('update users set active='.USER_STATUS_ACTIVE.' where id='.$uid);
			if(empty($q)){
				return false;
				}
			}
		if(CACHE_TYPE){
			bimport('cache.general');
			$user=$this->get_single_user($uid);
			$bcache=BCache::getInstance();
			$bcache->delete('users:userid:'.$user->id);
			$bcache->delete('users:useremail:'.$user->email);
			}
		return true;
		}
	/**
	 * Return salted hash of password. Depended on email and password.
	 *
	 * @param $mail user email
	 * @param $pass user password
	 * @return string salted hash
	 */
	public function makepass($mail,$pass){
		return hash('sha512',(hash('sha512', $mail.'MamaMia').hash('sha512', $pass.'LetMeGo')));
		}
	/**
	 *
	 * @param $email
	 * @return bool
	 */
	public function restore_email($email){
		if(empty($email)){
			return false;
			}
		bimport('sql.mysql');
		$db=BMySQL::getInstanceAndConnect();
		$qr='select * from users where email='.$db->escape_string($email);
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$l=$db->fetch($q);
		if(empty($l)){
			return false;
			}
		bimport('email.templates');
		$restorelink=sha1(uniqid(rand(),1));
		$qr='update users set confirmlink='.$db->escape_string($restorelink).' where email='.$db->escape_string($email);
		$q=$db->Query($qr);
		if(CACHE_TYPE){
			bimport('cache.general');
			$bcache=BCache::getInstance();
			$bcache->delete('users:userid:'.$l['id']);
			$bcache->delete('users:useremail:'.$email);
			}
		if(empty($q)){
			return false;
			}
		$e=new BEmailTemplate();
		$e->loadtemplate('users.restore');
		$e->email=$email;
		$url=(SSL_ACCOUNT_ENABLED?'https://':'http://').'account.'.BHOSTNAME.'/';
		bimport('cms.language');
		if(BLang::$langcode=='ua'){
			$url.='ua/';
			}
		$url.='restore/finish?uid='.$l['id'].'&restore='.$restorelink;
		$e->set('restorelink',$url);
		$e->send();
		return true;
		}
	/**
	 * Check email restore code and login user, if restore code is ok.
	 *
	 * @param $uid
	 * @param $restore
	 * @return bool
	 */
	public function check_restore($uid,$restore){
		if(empty($uid)){
			return false;
			}
		$user=$this->get_single_user($uid);
		
		if($user->confirmlink==$restore){
			$options=array(
				'interval'=>10800,
				'updatestep'=>60,
				);
			$sess=BUsersSession::getInstance();
			$sess->newSession($uid,$options);
			return true;
			}
		else return false;
		}
	/**
	 * Filter users by some params (keyword)
	 *
	 * @param $params
	 * @return array
	 */
	public function users_filter_json($params){
		if(!$db=BFactory::getDBO()){
			return array();
			}
		$qr='select `users`.`id` from `users`';
		$where='';
		$jn=array();
		if(!empty($params['keyword'])){
			$lcasekw=mb_strtolower($params['keyword'],'UTF-8');
			$searchmask=$db->escape_string('%'.$lcasekw.'%');

			if(is_numeric($params['keyword'])){
				$searchid=(int)$params['keyword'];
				}
			if(is_numeric($params['keyword'])){
				$searchtel=(int)$params['keyword'];
				}

			$ors=array();
			if(!empty($searchid)){
				$ors[]='(`users`.`id`='.$searchid.')';
				}
			if(!empty($searchtel)){
				$ors[]='(`users_phones`.`tel`='.$searchtel.')';
				$jn[]=' inner join users_phones on `users_phones`.`user`=`users`.`id` ';
				}
			$ors[]='(LOWER(name) like '.$searchmask.')';
			$ors[]='(LOWER(email) like '.$searchmask.')';
			$where.=(($where!='')?'&&':'').'('.implode(' or ',$ors).')';
			}
		if(!empty($params['city'])){
			$where.=(($where!='')?'&&':'').'(city='.(int)$params['city'].')';
			}
		if(!empty($jn)){
			$qr.=' '.implode(' ',$jn);
		}
		if($where!=''){
			$qr.=' where '.$where;
			}

		if($limit!=0){
			$qr.=' limit '.$limit.' offset '.$offset; 
			}

		$q=$db->Query($qr);
		if(empty($q)){
			}
		while($l=$db->fetch($q))
			$ids[]=$l['id'];
		if(count($ids)==0)return array();
		return $this->users_get($ids);
		}
	/**
	 * Get firtual user.
	 *
	 * @return BUserVirtual
	 */
	public function getVirtualUser(){
		if(!empty($this->virtualuser)) return $this->virtualuser;
		bimport('users.virtual');
		$vuser= new BUserVirtual;
		$vuser->init();
		$this->virtualuser=$vuser;
		return $this->virtualuser;
		}
	}
	
