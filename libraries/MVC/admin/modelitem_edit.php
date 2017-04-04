<?php
/**
 * Model class for admin panel.
 *
 */
bimport('mvc.model');
bimport('mvc.admin.modelitem');


abstract class BModelAdminItemEdit extends BModelAdminItem{

	//
	public function __construct(){
		parent::__construct();
		}
	/**
	 * Get data.
	 *
	 * @param $segments
	 * @return stdClass
	 */
	public function getData($segments){
		$data=new stdClass;
		$data->error=-1;
		//Check privileges.
		$bau=BAdminUsers::getInstance();
		$data->can_view=$bau->can($this->accessgroup,$this->access_view);
		$data->can_edit=$bau->can($this->accessgroup,$this->access_edit);
		if((!$data->can_view)&&(!$data->can_edit)){
			$data->error=ERR_LOW_PRIVS;
			return $data;
			}

		//Get item
		$classname=$this->itemcollection;
		$itmcoll=$classname::getInstance();
		$data->item=$itmcoll->item_get($segments['id']);
		if(empty($data->item)){
			BLog::addToLog('[MVC.Admin.ModelItemEdit] Could not get item!',LL_ERROR);
			$data->error=1;
			return $data;
			}
		$data->do=BRequest::getString('do');
		BLog::addToLog('[MVC.Admin.ModelItemEdit] do='.$data->do);

		$data->saving=(($data->do=='save')||($data->do=='save_exit'));
		$this->fieldsval=array();
		//TODO: set default fields...

		//
		$this->getfilterdata($data);
		if(($data->saving)&&($data->can_edit)){
			BLog::addToLog('[MVC.Admin.ModelItemEdit] setting fields...');
			if(!$this->itemsetfields($data->item)){
				BLog::addToLog('[MVC.Admin.ModelItemEdit] We have some errors during setting fields.',LL_ERROR);
				BLog::addToLog('[MVC.Admin.ModelItemEdit] errors: '.var_export($this->errors,true),LL_ERROR);
				BLog::addToLog('[MVC.Admin.ModelItemEdit] warnings: '.var_export($this->warnings,true),LL_ERROR);
				$data->errors=$this->errors;
				$data->warnings=$this->warnings;
				$data->error=2;
				return $data;
				}
			if(empty($data->errors)){
				BLog::addToLog('[MVC.Admin.ModelItemEdit] Saving to database...');
				$data->item->saveToDB();
				}
			if($data->do=='save_exit'){
				$data->redirect=$this->url_exit;;
				}
			}
		//

		//
		$data->error=0;
		$data->fieldsval=$this->fieldsval;
		$data->errors=$this->errors;
		$data->warnings=$this->warnings;
		return $data;
		}
	}