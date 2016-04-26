<?php
/**
 * Logout page
 *
 * @author Andrii Biriev
 */
defined('BEXEC') or die('No direct access!');

bimport('mvc.component');
bimport('mvc.model');
bimport('http.request');

class Model_users_logout extends BModel{
	/**
	 * Preform actions & get necessary data..
	 */
	public function get_data($segments){
		$data=new stdClass;
		bimport('users.session');
		$session=BUsersSession::getInstanceAndStart();
		if(!empty($session)){
			$data->result=$session->close();
			$red='';

			//Trying to get redirect URL from GET/POST param
			$rget=BRequest::getString('url','','get');//$_GET['url'];
			if(!empty($rget)){
				$red=base64_decode($rget);
				if(DEBUG_MODE){
					BDebug::message('[com_users]: logout(). Redirect URL (GET/POST)='.$red);
					}
				}
			//The second way - referer
			if(empty($red)){
				$red=$_SERVER["HTTP_REFERER"];
				if(DEBUG_MODE){
					BDebug::message('[com_users]: logout(). Redirect URL (REFERER)='.$red);
					}
				}
			//The last way - main page.
			if(empty($red)){
				$brouter=BRouter::getInstance();
				$red='//'.$brouter->generateURL('mainpage',BLang::$langcode,array('view'=>'mainpage'));
				if(DEBUG_MODE){
					BDebug::message('[com_users]: logout(). Redirect URL is empty!. will use "'.$red.'"');
					}
				}
			//----------------------------------------
			// Checking links with empty protocol,
			// uncompleted protocol or completed links
			//----------------------------------------
			if((strlen($red)>2)&&(substr($red,0,2)=='//')){
				$purl=parse_url('http:'.$red);
				if(DEBUG_MODE){
					BDebug::message('[com_users]: logout(). the link is with uncompleted protocol');
					}
				}
			elseif((strlen($red)>7)&&(substr($red,0,7)=='http://')){
				$purl=parse_url($red);
				if(DEBUG_MODE){
					BDebug::message('[com_users]: logout(). the link is with http protocol');
					}
				}
			elseif((strlen($red)>8)&&(substr($red,0,8)=='https://')){
				$purl=parse_url($red);
				if(DEBUG_MODE){
					BDebug::message('[com_users]: logout(). the link is with https protocol');
					}
				}
			else{
				$purl=parse_url('http://'.$red);
				$red='//'.$red;
				if(DEBUG_MODE){
					BDebug::message('[com_users]: logout(). the link is without protocol');
					}
				}
			//----------------------------------------
			// Parsing URL...
			//----------------------------------------
			$purl_host=$purl['host'];
			$purl_path=$purl['path'];
			if(DEBUG_MODE){
				BDebug::message('[com_users]: logout(). Redirect host='.$purl_host);
				BDebug::message('[com_users]: logout(). Redirect path='.$purl_path);
				}
			//----------------------------------------
			// Redirecting
			//----------------------------------------
			if(substr($purl_host,-strlen(BHOSTNAME))==BHOSTNAME){
				$data->redirect=$red;
				}else{
				if(DEBUG_MODE){
					BDebug::error('[com_users]: logout(). Wrong redirect host!');
					}
				}
        		if(empty($data->redirect)){
				$brouter=BRouter::getInstance();
				$data->redirect='//'.$brouter->generateURL('mainpage',BLang::$langcode,array('view'=>'mainpage'));
				}
			}
		return $data;
		}//END OF function get_data();
	}
