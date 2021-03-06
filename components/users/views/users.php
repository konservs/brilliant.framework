<?php
/**
 * View for users list
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class View_users_users extends \Brilliant\MVC\BView{
	public $users;

	public function generate($data){
		$this->users=$data->users;
		$this->setTitle(BLang::_('USERS_LIST_TITLE'));
		$this->addMeta('description',BLang::_('USERS_LIST_METADESC'));
		//$this->setlastmodified($this->article->date_modified);
		return $this->templateLoad();
		}
	}
