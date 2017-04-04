<?php
/**
 * Login page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\Users\BUsers;
use \Brilliant\Users\BUsersSession;
use \Brilliant\HTTP\BRequest;

class Model_users_login extends \Brilliant\MVC\BModel{
	/**
	 *
	 */
	public function getData($segments){
		$data=new stdClass;
		$data->error=false;
		$data->logged=false;
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session)){
			$data->logged=true;
			}
		$data->do=BRequest::getString('do');
		$data->email=BRequest::getString('email');
		$data->password=BRequest::getString('password');
		$data->save_me=(BRequest::getString('save_me')=='on')?true:false;

		if($data->do == 'login'){
			$busers = BUsers::getInstance();
			$errorCode = $busers->login($data->email,$data->password,$data->save_me);
			if($errorCode != USERS_ERROR_OK){
				$data->error = true;
				}else{
				$data->logged = true;
				}
			}else{
			//Show empty login form
			}
		return $data;
		}
	}
