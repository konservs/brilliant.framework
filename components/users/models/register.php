<?php
/**
 * Model for registration page
 * 
 * @author Andrii Biriev
 */
defined('BEXEC') or die;

bimport('mvc.component');
bimport('mvc.model');
bimport('users.general');
bimport('http.request');
bimport('captcha.general');
bimport('regions.general');
bimport('cms.language');

class Model_users_register extends BModel{
	/**
	 * Get necessary data.
	 */
	public function get_data($segments){
		$data=new stdClass;
		$data->captcha=BCaptcha::getInstance();
		$bregions=BRegions::getInstance();
		$data->regions=$bregions->rcities_get_tree(BLang::$langcode);
		$data->do=BRequest::GetString('do');
		$busers=BUsers::getInstance();
		if($data->do=='register'){
			$data->user=BRequest::getStrings(array(
				'email','name','password','password_check','region','city','captcha','agree','subscription'));
			$data->user['tels']=BRequest::getVar('tels',array());
			$data->user['lang']=BLang::$langcode;

			$r=$busers->register($data->user);
			if(!empty($r)){
				$data->errors=$r;
				}
			if($r===true){
				$brouter=BRouter::getInstance();
				$data->redirect=(SSL_ACCOUNT_ENABLED?'https://':'http://').$brouter->generateurl('users',BLang::$langcode,array('view'=>'register_success'));
				}
			}
		return $data;
		}
	}
