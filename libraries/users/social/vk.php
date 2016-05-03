<?php
bimport('users.social.abstractadapter');
bimport('users.social.adapterinterface');

class BSocialAdapterVk extends BSocialAbstractAdapter{
	/**
	 *
	 */
	public function __construct($config){
		parent::__construct($config);
		$this->socialFieldsMap = array(
			'socialId'   => 'uid',
			'email'      => 'email',
			'avatar'     => 'photo_max',
			'birthday'   => 'bdate'
			);
		$this->provider = 'vk';
			}

	/**
	 * Get user name or null if it is not set
	 *
	 * @return string|null
	 */
	public function getName(){
		$result = null;
		if (isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
			$result = $this->userInfo['first_name'] . ' ' . $this->userInfo['last_name'];
			}
		elseif (isset($this->userInfo['first_name']) && !isset($this->userInfo['last_name'])) {
			$result = $this->userInfo['first_name'];
			}
		elseif (!isset($this->userInfo['first_name']) && isset($this->userInfo['last_name'])) {
			$result = $this->userInfo['last_name'];
			}
		return $result;
		}
	/**
	 * Get user social id or null if it is not set
	 *
	 * @return string|null
	 */
	public function getSocialPage(){
		$result = null;
		if (isset($this->userInfo['screen_name'])) {
			$result = 'http://vk.com/' . $this->userInfo['screen_name'];
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
		if (isset($this->userInfo['sex'])) {
			$result = $this->userInfo['sex'] == 1 ? 'female' : 'male';
			}
		return $result;
		}
	/**
	 * Authenticate and return bool result of authentication
	 *
	 * @return bool
	 */
	public function authenticate(){
		BLog::addtolog('[Social.VK] Authentication started.');
		bimport('http.request');
		$code=BRequest::GetString('code');
		if(!isset($code)) {
			BLog::addtolog('[Social.VK] Could not get authorization code!',LL_ERROR);
			return false;
			}
		//
		$params=array(
			'client_id'=>$this->clientId,
			'client_secret'=>$this->clientSecret,
			'code'=>$code,
			'redirect_uri'=>$this->redirectUri
			);
		BLog::addtolog('[Social.VK] Getting token info...');
		$tokenInfo=$this->get('https://oauth.vk.com/access_token', $params);
		if(!isset($tokenInfo['access_token'])){
			BLog::addtolog('[Social.VK] Access token is not set!',LL_ERROR);
			//echo('<pre>'); var_dump($params); echo('</pre>'); die();
			return false;
			}
		//
		$params=array(
			'uids'=>$tokenInfo['user_id'],
			'fields'=>'uid,first_name,last_name,screen_name,sex,bdate,photo_max',
			'access_token'=> $tokenInfo['access_token']
			);
		$userInfo=$this->get('https://api.vk.com/method/users.get', $params);
		if(!isset($userInfo['response'][0]['uid'])) {
			BLog::addtolog('[Social.VK] Response UID is not set!',LL_ERROR);
			return false;
			}
		$this->userInfo = $userInfo['response'][0];
		if(!$this->createsession()){
			BLog::addtolog('[Social.VK] Could not create session!',LL_ERROR);
			return false;
			}
		BLog::addtolog('[Social.VK] All done!');
		return true;
		}
	/**
	 * Prepare params for authentication url
	 *
	 * @return array
	 */
	public function prepareAuthParams(){
		return array(
			'auth_url'	=> 'http://oauth.vk.com/authorize',
			'auth_params' => array(
				'client_id'	 => $this->clientId,
				'scope'		 => 'notify',
				'redirect_uri'  => $this->redirectUri,
				'state'  	=> $this->redirectState,
				'response_type' => 'code'
				)
			);
		}
	}
