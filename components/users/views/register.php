<?php
/**
 * Register page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\cms\BLang;
use \Brilliant\users\social\BSocialFabric;
use \Application\BRouter;

class View_users_register extends \Brilliant\mvc\BView{
	public function generate($data){
		if($data->result){
			$brouter=BRouter::getInstance();
			$url=$brouter->generateURL('users',array('view'=>'registerdone'),array('usehostname'=>true));
			$this->setLocation($url,0);
			return '';
			}
		$this->email=$data->email;
		$this->name=$data->name;
		$this->user=$data->user;
		$this->errors=$data->errors;
		$this->socloginlist=BSocialFabric::getSocialList();
		$this->setTitle(BLang::_('USERS_REGISTRATION_TITLE'));
		return $this->template_load();
		}
	}
