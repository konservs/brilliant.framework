<?php
/**
 * Current user panel / login&register links view
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class View_users_userpanel extends \Brilliant\mvc\BView{
	public function generate($data){
		$this->user=$data->user;
		if(isset($data->lastmod))
			$this->setlastmodified($data->lastmod);
		return $this->template_load();
		}
	}