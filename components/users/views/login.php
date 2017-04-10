<?php
/**
 * Login page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

use \Brilliant\CMS\BLang;

class View_users_login extends \Brilliant\MVC\BView{
	/**
	 *
	 */
	public function generate($data){
		$this->do=$data->do;
		$this->email=$data->email;
		$this->password=$data->password;
		$this->error=$data->error;
		$this->logged=$data->logged;
		//Title & meta
		$this->settitle(BLang::_('USERS_LOGIN_TITLE'));
		$this->addmeta('description',BLang::_('USERS_LOGIN_METADESC'));
		//
		if($this->logged){
			$this->redirectto=$data->redirectto;
			if(empty($this->redirectto)){
				//ToDO: BRouter
				$bRouter=\Application\BRouter::getInstance();
				$this->redirectto=$bRouter->generateUrl('users',array('view'=>'dashboard','lang'=>BLang::$langcode),array('usehostname'=>true));
				}
			//$this->setLocation($this->redirectto,0);
			return 'Redirecting to <a href="'.$this->redirectto.'">'.$this->redirectto.'</a>...';
			//return $this->templatLoad('done');
			}else{
			return $this->templateLoad();
			}

		}
	}
