<?php
/**
 * Social user
 *
 * @author: Andrii Biriev
 */
bimport('items.item');
bimport('users.social.users');
bimport('log.general');

class BUsersSocialUser extends BItemsItem{
	protected $collectionname='BUsersSocialUsers';
	protected $tablename='users_social';
	protected $primarykey=array('user','provider');
	/**
	 * Constructor - init fields...
	 */
	function __construct(){
		parent::__construct();
		//$this->fieldAddRaw('provider','enum',array('values'=>array('vk','ok','fb','mailru','yandex','google')));
		$this->fieldAddRaw('social_id','string');
		$this->fieldAddRaw('name','string');
		$this->fieldAddRaw('email','string');
		$this->fieldAddRaw('social_page','string');
		$this->fieldAddRaw('sex','enum',array('values'=>array('male','female')));
		$this->fieldAddRaw('birthday','dt');
		$this->fieldAddRaw('avatar_remote','string');
		$this->fieldAddRaw('avatar_local','string');
		//
		$this->fieldAddRaw('created','dt',array('readonly'=>true));
		$this->fieldAddRaw('lastlogin','dt');
		}
	/**
	 * Get user by social user.
	 */
	public function getuser(){
		if(empty($this->user)){
			return NULL;
			}
		bimport('users.general');
		$bu=BUsers::getInstance();
		$user=$bu->itemGet($this->user);
		return $user;
		}
	/**
	 *
	 */
	public function dbcheckkeys(){
		if(empty($this->user)){
			return false;
			}
		if(empty($this->social_id)){
			return false;
			}
		if(empty($this->provider)){
			return false;
			}
		return true;		
		}
	/**
	 *
	 */
	public function dbinsert(){
		if(!$this->dbcheckkeys()){
			return false;
			}
		$db=BFactory::getDBO();
		$qr_fields=array();
		$qr_values=array();
		$this->getfieldsvalues($qr_fields,$qr_values);
		//
		$qr_fields[]='`user`';
		$qr_values[]=$this->user;
		//
		$qr_fields[]='`provider`';
		$qr_values[]=$db->escapeString($this->provider);
		//
		$qr='INSERT INTO `'.$this->tablename.'` ('.implode(',',$qr_fields).') VALUES ('.implode(',',$qr_values).')';
		//Running query...
		$q=$db->query($qr);
		if(empty($q)){
			return false;
			}
		$this->isnew=false;
		//Updating cache...
		$this->updatecache();
		//Return result
		return true;
		}
	/**
	 *
	 */
	public function dbupdate(){
		if(!$this->dbcheckkeys()){
			return false;
			}
		$db=BFactory::getDBO();
		$qr_fields=array();
		$qr_values=array();
		$this->getfieldsvalues($qr_fields,$qr_values);
		//
		$qr='UPDATE `'.$this->tablename.'` SET ';
		$first=true;
		foreach($qr_fields as $i=>$field){
			$qr.=($first?'':', ').$field.'='.$qr_values[$i];
			$first=false;
			}
		$qr.=' WHERE ((`provider`="'.$this->provider.'") AND (`user`="'.$this->user.'"))';
		//Running query...
		$q=$db->query($qr);
		if(empty($q)){
			return false;
			}
		$this->isnew=false;
		//Updating cache...
		$this->updatecache();
		//Return result
		return true;
		}
	/**
	 *
	 */
	public function geticon_fontawesome(){
		switch($this->provider){
			case 'facebook':
			case 'fb':
				return 'fa-facebook';
			case 'vkontakte':
			case 'vk':
				return 'fa-vk';
			case 'google':
				return 'fa-google-plus';
			}
		return 'fa-share-square-o';
		}
	/**
	 * Create avatar folder
	 * /users/avatars/<year>/<month>/<day>/<social type>-<social-id>/
	 */
	public function createavatardir(){
		$avatardir=$this->getavatardir();
		if(empty($avatardir)){
			return false;
			}
		if(!is_dir($avatardir)){
			mkdir($avatardir,0777, true);
			}
		return true;
		}
	/**
	 * Get avatar folder
	 * /users/avatars/<year>/<month>/<day>/<social type>-<social-id>/
	 */
	public function getavatardir(){
		if(empty($this->created)){
			return false;
			}
		if(empty($this->provider)){
			return false;
			}
		if(empty($this->social_id)){
			return false;
			}
		$DS=DIRECTORY_SEPARATOR;
		$avatardir=MEDIA_PATH_ORIGINAL.$DS.'users'.$DS.'avatars'.$DS;
		$avatardir.=$this->created->format('Y').$DS;
		$avatardir.=$this->created->format('m').$DS;
		$avatardir.=$this->created->format('d').$DS;
		$avatardir.=$this->provider.'-'.$this->social_id;
		return $avatardir;
		}
	/**
	 * Get avatar path
	 * /users/avatars/<year>/<month>/<day>/<social type>-<social-id>/
	 */
	public function getavatarpath(){
		if(empty($this->created)){
			return false;
			}
		if(empty($this->provider)){
			return false;
			}
		if(empty($this->social_id)){
			return false;
			}
		$avatarpath='/users/avatars/';
		$avatarpath.=$this->created->format('Y').'/';
		$avatarpath.=$this->created->format('m').'/';
		$avatarpath.=$this->created->format('d').'/';
		$avatarpath.=$this->provider.'-'.$this->social_id;
		return $avatarpath;
		}
	/**
	 *
	 */
	public function downloadavatar($overwrite=false){
		BLog::addToLog('[Usera.Social.User] Downloading avatar from "'.$this->avatar_remote.'"...');
		//Extract path
		//From "https://scontent.xx.fbcdn.net/hprofile-xft1/v/t1.0-1/p720x720/11014811_812264902187047_8690741747689722528_n.jpg?oh=914a8bd73e222b0ce166ef56c2a82767&oe=57A46864"
		//To "hprofile-xft1/v/t1.0-1/p720x720/11014811_812264902187047_8690741747689722528_n.jpg"
		$avatar_path=parse_url($this->avatar_remote,PHP_URL_PATH);
		BLog::addToLog('[Usera.Social.User] Avatar path="'.$avatar_path.'"...');
		//
		$extx=explode('.',$avatar_path);
		if(count($extx)<2){
			BLog::addToLog('[Usera.Social.User] Could not extract extention by remote URL',LL_ERROR);
			return false;
			}
		$ext=mb_strtolower($extx[count($extx)-1],'UTF-8');
		$allowed=array('png','jpg','jpeg','gif');
		if(!in_array($ext,$allowed)){
			BLog::addToLog('[Usera.Social.User] Invalid extention "'.$ext.'".',LL_ERROR);
			return false;
			}
		//Local filename is depended on avatar URL hash.
		//The media files are cached, so when remote avatar will change - we will create new file.
		//In case of file existing - we will just skip it (or overwrite, if necessary).
		$localfn=hash('sha256',$this->avatar_remote,false).'.'.$ext;
		//
		$this->createavatardir();
		$localdir=$this->getavatardir();
		$localfullfn=$localdir.DIRECTORY_SEPARATOR.$localfn;
		$localfullpath=$this->getavatarpath().'/'.$localfn;
		BLog::addToLog('[Usera.Social.User] local file : '.$localfullfn);
		BLog::addToLog('[Usera.Social.User] local path : '.$localfullpath);
		if((file_exists($localfullfn))&&(!$overwrite)){
			$this->avatar_local=$localfullpath;
			BLog::addToLog('[Usera.Social.User] The avatar file exist.');
			return true;
			}
		//Download avatar...
		BLog::addToLog('[Usera.Social.User] Downloading avatar...');
		$ch = curl_init($this->avatar_remote);
		$fp = fopen($localfullfn, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		//
		$this->avatar_local=$localfullpath;
		BLog::addToLog('[Usera.Social.User] All done!');
		return true;
		}

	}