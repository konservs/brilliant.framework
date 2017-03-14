<?php
/**
 * Login page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\users\BUsers;
use \Brilliant\users\BUsersSession;

class Model_users_login extends \Brilliant\mvc\BModel{
	/**
	 *
	 */
	public function get_data($segments){
		$data=new stdClass;
		$data->error=false;
		$data->logged=false;
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session)){
			$data->logged=true;
			}
		$data->do=isset($_POST['do'])?$_POST['do']:'';
		$data->email=isset($_POST['email'])?$_POST['email']:'';
		$data->password=isset($_POST['password'])?$_POST['password']:'';
		$data->save_me=((isset($_POST['save_me']))&&($_POST['save_me']=='on'))?true:false;

		if($data->do=='login'){
			//Try to login
			$busers=BUsers::getInstance();
			$user=$busers->login($data->email,$data->password,$data->save_me);
			if(empty($user)){
				$data->error=true;
				}else{
				$data->logged=true;
				}
			}else{
			//Show empty login form
			}
		return $data;
		}
	}
