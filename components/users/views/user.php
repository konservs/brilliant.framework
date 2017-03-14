<?php
/**
 * View for single user page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class View_users_user extends \Brilliant\mvc\BView{
	public $user;

	public function generate($data){
		if(empty($data)){
			return '';
			}
		$this->user=$data->user;
		//
		if(empty($this->user->title)){
			$this->settitle(BLang::sprintf('USER_PAGE_TITLE',$this->user->name));
			}else{
			$this->settitle($this->user->title);
			}
		//Set META description
		if(empty($this->user->metadesc)){
			$this->addmeta('description',BLang::sprintf('USER_PAGE_DESCRIPTION',$this->user->name));
			}else{
			$this->addmeta('description',$this->user->metadesc);
			}
		if(empty($this->user->metakeys)){
			$this->addmeta('keywords',$this->user->name);
			}else{
			$this->addmeta('keywords',$this->user->metakeys);
			}

		if($this->user->indexable == 'N'){
			$this->addmeta('robots', 'noindex, nofollow');
			}
		//
		$gurl=$this->user->gplus_url();
		if(!empty($gurl)){
			$this->add_link(array('rel'=>'author','href'=>$gurl));
			}
		return $this->template_load();
		}
	}
