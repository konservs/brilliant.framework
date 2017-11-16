<?php
/**
 * Register page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\CMS\BLang;
use \Brilliant\Users\Social\BSocialFabric;
use \Application\BRouter;

class View_users_register extends \Brilliant\MVC\BView{
	public $email;
	public $name;
	public $errors;
	/**
	 * Generate HTML
	 *
	 * @param $data
	 * @return string
	 */
	public function generate($data){
		if($data->result){
			$bRouter=BRouter::getInstance();
			$url=$bRouter->generateURL('users',array('view'=>'registerdone'),array('usehostname'=>true));
			$this->setLocation($url,0);
			return '';
			}
		$this->email=$data->email;
		$this->name=$data->name;
		$this->errors=$data->errors;
		$this->socLoginList=BSocialFabric::getSocialList();
		$this->setTitle(BLang::_('USERS_REGISTRATION_TITLE'));
		return $this->templateLoad();
		}
	}
