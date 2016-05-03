<?php
/**
 * Login page model
 *
 * @author Andrii Biriev
 */
defined('BEXEC') or die('No direct access!');

bimport('mvc.component');
bimport('mvc.model');
bimport('http.request');

class Model_users_login extends BModel{
	/**
	 * Model - get necessary data.
	 */
	public function get_data($segments){
		$data=new stdClass;
		$data->error=false;
		$data->logged=false;
		bimport('users.session');
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session)){
			$data->logged=true;
			}
		$data->do=isset($_POST['do'])?$_POST['do']:'';
		$data->email=isset($_POST['email'])?$_POST['email']:'';
		$data->password=isset($_POST['password'])?$_POST['password']:'';
		$data->save_me=((isset($_POST['save_me']))&&($_POST['save_me']=='on'))?true:false;
		$data->url=isset($_POST['url'])?$_POST['url']:'';
		bimport('cms.language');
		$brouter=BRouter::getInstance();
		$blang=BLang::getInstance();
		$data->url_restore=$brouter->generateURL('users',$blang->suffix,array('view'=>'restore'));

		if($data->do=='login'){
			//Try to login
			bimport('users.general');
			$busers=BUsers::getInstance();
			$user=$busers->login($data->email,$data->password,$data->save_me);
			
			if(!is_numeric($user->id)){
				if(DEBUG_MODE){
					BLog::addtolog('Could not login!');
					}
				//Send warning to IP ban system
				bimport('ip.ban');
				BIpBan::blockwarn();
				//return error
				$data->error=$user;
				return $data;
				}

			$data->logged=true;
			}
		//If we are logged - redirect...
		if($data->logged){
			//----------------------------------------
			// Get request URL
			//----------------------------------------
			$rget=BRequest::getString('url','');//$_GET['url'];
			if(empty($rget)){
				//$red='https://account.'.BHOSTNAME.'/';
				$brouter=BRouter::getInstance();
				$red='//'.$brouter->generateURL('users',BLang::$langcode,array('view'=>'dashboard'));

				if(DEBUG_MODE){
					BLog::addtolog('[com_users]: login(). Redirect URL is empty!');
					}
				}else{
				$red=base64_decode($rget);
				if(DEBUG_MODE){
					BLog::addtolog('[com_users]: login(). Redirect URL='.$red);
					}
				}
			//----------------------------------------
			// Checking links with empty protocol,
			// uncompleted protocol or completed links
			//----------------------------------------
			if((strlen($red)>2)&&(substr($red,0,2)=='//')){
				$purl=parse_url('http:'.$red);
				if(DEBUG_MODE){
					BLog::addtolog('[com_users]: login(). the link is with uncompleted protocol');
					}
				}
			elseif((strlen($red)>7)&&(substr($red,0,7)=='http://')){
				$purl=parse_url($red);
				if(DEBUG_MODE){
					BLog::addtolog('[com_users]: login(). the link is with http protocol');
					}
				}
			elseif((strlen($red)>8)&&(substr($red,0,8)=='https://')){
				$purl=parse_url($red);
				if(DEBUG_MODE){
					BLog::addtolog('[com_users]: login(). the link is with https protocol');
					}
				}
			else{
				$purl=parse_url('http://'.$red);
				$red='//'.$red;
				if(DEBUG_MODE){
					BLog::addtolog('[com_users]: login(). the link is without protocol');
					}
				}
			//----------------------------------------
			// Parsing URL...
			//----------------------------------------
			$purl_host=$purl['host'];
			$purl_path=$purl['path'];
			if(DEBUG_MODE){
				BLog::addtolog('[com_users]: login(). Redirect host='.$purl_host);
				BLog::addtolog('[com_users]: login(). Redirect path='.$purl_path);
				}
			//----------------------------------------
			// Redirecting
			//----------------------------------------
			if(substr($purl_host,-strlen(BHOSTNAME))==BHOSTNAME){
				$data->redirect=$red;
				}else{
				if(DEBUG_MODE){
					BLog::addtolog('[com_users]: login(). Wrong redirect host!');
					}
				}
        		if(empty($data->redirect)){
				$brouter=BRouter::getInstance();
				$data->redirect='//'.$brouter->generateURL('users',BLang::$langcode,array('view'=>'dashboard'));
				}
			}
		return $data;
		}
	}
