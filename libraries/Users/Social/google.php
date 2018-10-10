<?php
bimport('users.social.abstractadapter');
bimport('users.social.adapterinterface');

class BSocialAdapterGoogle extends BSocialAbstractAdapter{
	public function __construct($config){
		parent::__construct($config);
		$this->socialFieldsMap = array(
			'socialId'=>'id',
			'email'=>'email',
			'name'=>'name',
			'socialPage'=>'link',
			'avatar'=>'picture',
			'sex'=>'gender'
			);
		$this->provider = 'google';
		}
	/**
	 * Get user birthday or null if it is not set
	 *
	 * @return string|null
	 */
	public function getBirthday(){
		if(isset($this->_userInfo['birthday'])) {
			$this->_userInfo['birthday'] = str_replace('0000', date('Y'), $this->_userInfo['birthday']);
			$result = date('d.m.Y', strtotime($this->_userInfo['birthday']));
			}
		else {
			$result = null;
			}
		return $result;
		}
	/**
	 * Authenticate and return bool result of authentication
	 *
	 * @return bool
	 */
	public function authenticate(){
		bimport('http.request');
		$code=BRequest::GetString('code');
		if(empty($code)) {
			return false;
			}
		$params = array(
			'client_id'	 => $this->clientId,
			'client_secret' => $this->clientSecret,
			'redirect_uri'  => $this->redirectUri,
			'grant_type'	=> 'authorization_code',
			'code'		  => $code
			);
		$tokenInfo = $this->post('https://accounts.google.com/o/oauth2/token', $params);

		if(!isset($tokenInfo['access_token'])) {
			return false;
			}
		$params['access_token'] = $tokenInfo['access_token'];
		$userInfo = $this->get('https://www.googleapis.com/oauth2/v1/userinfo', $params);
		if(!isset($userInfo[$this->socialFieldsMap['socialId']])) {
			BLog::addToLog('[Social.Google] userInfo socialId is not set!',LL_ERROR);
			BLog::addToLog('[Social.Google] userInfo="'.var_export($userInfo,true).'".',LL_ERROR);
			return false;
			}
		$this->userInfo = $userInfo;
		//
		if(!$this->createsession()){
			BLog::addToLog('[Social.Google] Could not create session!',LL_ERROR);
			return false;
			}
		BLog::addToLog('[Social.Google] All done!');
		return true;

		}

	/**
	 * Prepare params for authentication url
	 *
	 * @return array
	 */
	public function prepareAuthParams(){
		return array(
			'auth_url'	=> 'https://accounts.google.com/o/oauth2/auth',
			'auth_params' => array(
				'redirect_uri'  => $this->redirectUri,
				'state'  	 => $this->redirectState,
				'response_type' => 'code',
				'client_id'	 => $this->clientId,
				'scope'		 => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
				)
			);
		}
	
	}
