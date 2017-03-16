<?php
//============================================================
// View class for admin panel
//
// Author: Andrii Biriev
//============================================================
bimport('html.bootstrap-helper');
//============================================================
//
//============================================================
class BViewAdmin extends BView{
	//bootstrap helper property
	protected $bs;
	protected $activetab;
	//====================================================
	// Constructor - create the bootstrap helper
	//====================================================
	public function __construct(){
		parent::__construct();
		$this->bs=BBoostrapHelper::getInstance();
		$this->activetab='ru';
		}
	//====================================================
	// Traw tree items...
	//====================================================
	public function DrawTreeElement($count){
		$html='';
		for($i=0;$i<$count;$i++){
			$html.='<span class="gi">|-</span>';
			}
		return $html;
		}
	//====================================================
	// Processing errors...
	//====================================================
	public function process_errors($errors){
		if(!is_array($errors)){
			return false;
			}
		$this->errors=$errors;
		if(isset($errors['database'])){
			$this->bs->addAlert(BLang::_('ADMIN_ERROR_DB'),'danger');
			unset($errors['database']);
			}
		//Fields errors
		$this->bs->errors=$errors;
		//Detect active tab...
		$this->activetab='ru';
		foreach($errors as $k=>$er){
			$lang=substr($k,-2);
			if(($lang=='ru')||($lang=='ua')){
				$this->activetab=$lang;
				break;
				}
			}
		return true;
		}
	//====================================================
	// Processing warnings...
	//====================================================
	public function process_warnings($warnings){
		if(!is_array($warnings)){
			return false;
			}
		$this->warnings=$warnings;
		//Fields errors
		$this->bs->warnings=$warnings;
		//Detect active tab...
		if(empty($this->activetab)){
			$this->activetab='ru';
			foreach($warnings as $k=>$er){
				$lang=substr($k,-2);
				if(($lang=='ru')||($lang=='ua')){
					$this->activetab=$lang;
					break;
					}
				}
			}
		//
		foreach($warnings as $k=>$warn){
			$this->bs->addAlert($warn,'warning');
			}
		return true;
		}
	//====================================================
	// Processing redirect...
	//====================================================
	public function process_redirect($redirect){
		if(!empty($redirect)){
			$this->setLocation($redirect,0);
			return true;
			}
		return false;
		}
	}
