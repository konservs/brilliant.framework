<?php
/**
 * Login page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class View_users_logout extends \Brilliant\mvc\BView{
	public function generate($data){
		if(empty($this->redirectto)){
			$this->redirectto='/';
			}
		$this->setLocation($this->redirectto,0);
		return 'Redirecting to <a href="'.$this->redirectto.'">'.$this->redirectto.'</a>...';
		}
	}
