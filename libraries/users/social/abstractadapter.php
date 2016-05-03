<?php
/**
 * Social authorization
 *
 * @author: Andrii Biriev
 */

bimport('log.general');

abstract class BSocialAbstractAdapter implements BSocialAdapterInterface{
	/**
	 * Social Client ID
	 *
	 * @var string null
	 */
	protected $clientId = null;
	/**
	 * Social Client Secret
	 *
	 * @var string null
	 */
	protected $clientSecret = null;

	/**
	 * Social Redirect Uri
	 *
	 * @var string null
	 */
	public $redirectUri = null;

	/**
	 * Social Redirect State
	 *
	 * @var string null
	 */
	public $redirectState = null;

	/**
	 * Name of auth provider
	 *
	 * @var string null
	 */
	protected $provider = null;

	/**
	 * Social Fields Map for universal keys
	 *
	 * @var array
	 */
	protected $socialFieldsMap = array();

	/**
	 * Storage for user info
	 *
	 * @var array
	 */
	protected $userInfo = null;

	/**
	 * Constructor
	 *
	 * @param array $config
	 * @throws Exception\InvalidArgumentException
	 */
	public function __construct($config){
		if (!is_array($config))
			throw new Exception\InvalidArgumentException(
				__METHOD__ . ' expects an array with keys: `client_id`, `client_secret`, `redirect_uri`'
			);

		foreach(array('client_id', 'client_secret', 'redirect_uri') as $param) {
			if(!array_key_exists($param, $config)) {
				throw new Exception\InvalidArgumentException(
					__METHOD__ . ' expects an array with key: `' . $param . '`'
				);
				}
			else {
				$property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $param))));
				$this->$property = $config[$param];
				}
			}
		}

	/**
	 * Get user social id or null if it is not set
	 *
	 * @return string|null
	 */
	public function getSocialId(){
		$result = null;
		if(isset($this->userInfo[$this->socialFieldsMap['socialId']])) {
			$result = $this->userInfo[$this->socialFieldsMap['socialId']];
			}
		return $result;
		}
	/**
	 * Get user email or null if it is not set
	 *
	 * @return string|null
	 */
	public function getEmail(){
		$result = null;
		if (isset($this->userInfo[$this->socialFieldsMap['email']])) {
			$result = $this->userInfo[$this->socialFieldsMap['email']];
			}
		return $result;
		}
	/**
	 * Get user name or null if it is not set
	 *
	 * @return string|null
	 */
	public function getName(){
		$result = null;
		if (isset($this->userInfo[$this->socialFieldsMap['name']])) {
			$result = $this->userInfo[$this->socialFieldsMap['name']];
			}
		return $result;
		}
	/**
	 * Get user social page url or null if it is not set
	 * @return string|null
	 */
	public function getSocialPage(){
		$result = null;
		if (isset($this->userInfo[$this->socialFieldsMap['socialPage']])) {
			$result = $this->userInfo[$this->socialFieldsMap['socialPage']];
			}
		return $result;
		}
	/**
	 * Get url of user's avatar or null if it is not set
	 *
	 * @return string|null
	 */
	public function getAvatar(){
		$result = null;
		if (isset($this->userInfo[$this->socialFieldsMap['avatar']])) {
			$result = $this->userInfo[$this->socialFieldsMap['avatar']];
			}
		return $result;
		}
	/**
	 * Get user sex or null if it is not set
	 *
	 * @return string|null
	 */
	public function getSex(){
		$result = null;
		if (isset($this->userInfo[$this->socialFieldsMap['sex']])) {
			$result = $this->userInfo[$this->socialFieldsMap['sex']];
			}
		return $result;
		}

	/**
	 * Get user birthday in format dd.mm.YYYY or null if it is not set
	 *
	 * @return string|null
	 */
	public function getBirthday(){
		$result = null;
		if (isset($this->userInfo[$this->socialFieldsMap['birthday']])) {
			$result = date('d.m.Y', strtotime($this->userInfo[$this->socialFieldsMap['birthday']]));
			}
		return $result;
		}
	/**
	 * Get user birthday DateTime object
	 */
	public function getBirthdayObject(){
		$birthday=$this->getBirthday();
		if(empty($birthday)){
			return NULL;
			}
		$birthday=new DateTime($birthday);
		return $birthday;
		}
	/**
	 * Return name of auth provider
	 *
	 * @return string
	 */
	public function getProvider(){
		return $this->provider;
		}

	/**
	 * Get authentication url
	 *
	 * @return string
	 */
	public function getAuthUrl(){
		$config = $this->prepareAuthParams();
		//return $result = $config['auth_url'] . '?' . urldecode(http_build_query($config['auth_params']));
		return $result = $config['auth_url'] . '?' . http_build_query($config['auth_params']);
		}

	/**
	 * Make post request and return result
	 *
	 * @param string $url
	 * @param string $params
	 * @param bool $parse
	 * @return array|string
	 */
	protected function post($url, $params, $parse = true){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
//		curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
//		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);
		if($parse){
			$result = json_decode($result, true);
			}
		return $result;
		}
	/**
	 * Make get request and return result
	 *
	 * @param $url
	 * @param $params
	 * @param bool $parse
	 * @return mixed
	 */
	protected function get($url, $params, $parse = true){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url . '?' . urldecode(http_build_query($params)));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);

		if($parse) {
			$result = json_decode($result, true);
			}
			return $result;
		}
	/**
	 * Insert user data into.
	 */
	protected function createsession(){
		bimport('users.general');
		bimport('users.social.user');
		bimport('users.social.users');
		$bsu=BUsersSocialUsers::getInstance();
		$list=$bsu->items_filter(array('provider'=>$this->provider,'social_id'=>$this->getSocialId()));
		$user=NULL;
		if(!empty($list)){
			BLog::addtolog('[Social.Adapter] Found social record in the database!');
			$su=reset($list);
			$user=$su->getuser();
			}else{
			BLog::addtolog('[Social.Adapter] Could not find social record in the database! Need to create it.');
			//Try to load existing user with such email.
			$email=$this->getEmail();
			if(!empty($email)){
				BLog::addtolog('[Social.Adapter] Loading user by email "'.$email.'"!');
				$bu=BUsers::getInstance();
				$user=$bu->get_user_byemail($email);
				}
			//If we have not loaded user = 
			if(empty($user)){
				BLog::addtolog('[Social.Adapter] Could not load user! Creating it. Email="'.$email.'"');
				$user=new BUser();
				$user->email=empty($email)?NULL:$email;
				$user->name=$this->getName();
				$user->active='Y';
				$user->birthday=$this->getBirthdayObject();
				$user->last_action=new DateTime();
				$r=$user->savetodb();
				if(empty($r)){
					BLog::addtolog('[Social.Adapter] Could not save user!',LL_ERROR);
					return false;
					}
				BLog::addtolog('[Social.Adapter] User saved. User id='.$user->id.'.');
				//User saved.
				}
			$su=new BUsersSocialUser();
			$su->user=$user->id;
			$su->social_id=$this->getSocialId();
			$su->provider=$this->provider;
			$su->created=new DateTime();
			}
		if(empty($su)){
			BLog::addtolog('[Social.Adapter] Could not load social user record!',LL_ERROR);
			return false;
			}
		if(empty($user)){
			BLog::addtolog('[Social.Adapter] Could not load user!',LL_ERROR);
			return false;
			}
		//Get fields.
		$su->name=$this->getName();
		$su->email=$this->getEmail();
		$su->sex=$this->getSex();
		$su->birthday=$this->getBirthdayObject();
		$su->lastlogin=new DateTime();
		$su->avatar_remote=$this->getAvatar();
		$su->social_page=$this->getSocialPage();
		if(!empty($su->avatar_remote)){
			$r=$su->downloadavatar();
			if(!$r){
				return false;
				}
			}
		$r=$su->savetodb();
		if(!$r){
			return false;
			}
		if((!empty($su->avatar_local))&&($user->avatar!=$su->avatar_local)){
			$user->avatar=$su->avatar_local;
			$user->savetodb();
			}
		//Session.
		$options=array('interval'=>2592000,'updatestep'=>60);
		$sess=BUsersSession::getInstance();
		$r=$sess->NewSession($user->id,$options);
		if(empty($r)){
			return false;
			}
		return true;
		}
	}
