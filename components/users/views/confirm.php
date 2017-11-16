<?php
/**
 * View confirm page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class View_users_confirm extends \Brilliant\MVC\BView{
	public function generate($data){
		$this->status=$data->status;
		if($this->logged){
			$this->setLocation($data->redirect,0);
			return $this->renderredirect();
			}else{
			return $this->templateLoad();
			}
		}
	}
