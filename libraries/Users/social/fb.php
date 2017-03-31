<?php
bimport('users.social.abstractadapter');
bimport('users.social.adapterinterface');

class BSocialAdapterFb extends BSocialAbstractAdapter{
	protected $access_token;
	/**
	 *
	 */
	public function __construct($config){
		parent::__construct($config);
		$this->socialFieldsMap = array(
			'socialId'=>'id',
			'email'=>'email',
			'name'=>'name',
			'socialPage'=>'link',
			'sex'=>'gender',
			'birthday'=>'birthday'
			);
		$this->provider = 'fb';//Facebook
		}
	/**
	 * Get url of user's avatar or null if it is not set
	 *
	 * @return string|null
	 */
	public function getAvatar(){
		//Get User picture
		$params = array(/*'access_token'=>$this->access_token,*/'height'=>512,'width'=>512,'redirect'=>0,'type'=>'square');
		$userPicture = $this->get('https://graph.facebook.com/'.$this->userInfo['id'].'/picture', $params);
		if(!isset($userPicture['data'])) {
			BLog::addToLog('[Social.FB] User photo is not set!',LL_ERROR);
			return false;
			}
		$result=$userPicture['data']['url'];
		return $result;
		}
	/**
	 * Get user social id or null if it is not set
	 *
	 * @return string|null
	 */
	public function getSocialPage(){
		$result = null;
		if (isset($this->userInfo['id'])) {
			return 'https://facebook.com/' . $this->userInfo['id'];
			}
		return $result;
		}
	/**
	 * Authenticate and return bool result of authentication
	 *
	 * @return bool
	 */
	public function authenticate(){
		$result = false;
		bimport('http.request');
		$code=BRequest::GetString('code');
		if(!isset($code)) {
			BLog::addToLog('[Social.FB] Code is empty!',LL_ERROR);
			return false;
			}
		$params = array(
			'client_id'	=> $this->clientId,
			'redirect_uri'  => $this->redirectUri,
//			'state'		=> 
			'client_secret' => $this->clientSecret,
			'code'	        => $code
			);
		parse_str($this->get('https://graph.facebook.com/oauth/access_token', $params, false), $tokenInfo);
		if(empty($tokenInfo)){
			BLog::addToLog('[Social.FB] tokenInfo is empty!',LL_ERROR);
			return false;
			}
		if(!isset($tokenInfo['access_token'])) {
			BLog::addToLog('[Social.FB] Access token is not set!',LL_ERROR);
			BLog::addToLog('[Social.FB] tokenInfo="'.var_export($tokenInfo,true).'".',LL_ERROR);
			return false;
			}
		$this->access_token=$tokenInfo['access_token'];
		$params = array('access_token' => $this->access_token,'fields' => 'id,name,email,birthday,gender');
		$userInfo = $this->get('https://graph.facebook.com/me', $params);
		if(!isset($userInfo['id'])) {
			BLog::addToLog('[Social.FB] userInfo id is not set!',LL_ERROR);
			BLog::addToLog('[Social.FB] userInfo="'.var_export($userInfo,true).'".',LL_ERROR);
			return false;
			}
		BLog::addToLog('[Social.FB] Got social info: '.var_export($userInfo,true));
		$this->userInfo = $userInfo;
		//
		if(!$this->createsession()){
			BLog::addToLog('[Social.FB] Could not create session!',LL_ERROR);
			return false;
			}
		BLog::addToLog('[Social.FB] All done!');
		return true;
		}
	/**
	 * Prepare params for authentication url
	 *
	 * @return array
	 */
	public function prepareAuthParams(){
		return array(
			'auth_url'	=> 'https://www.facebook.com/dialog/oauth',
			'auth_params' => array(
				'client_id'	 => $this->clientId,
				'redirect_uri'  => $this->redirectUri,
				'state'  	=> $this->redirectState,
				'response_type' => 'code',
				'scope'		 => 'email'
				)
			);
		}
	}