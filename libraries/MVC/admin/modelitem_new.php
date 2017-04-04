<?php
/**
 * Model class for admin panel.
 *
 */
bimport('mvc.model');
bimport('mvc.admin.modelitem');

abstract class BModelAdminItemNew extends BModelAdminItem{
	//
	public function __construct(){
		parent::__construct();
		}
	/**
	 * Generate redirect URL, if the saving was success.
	 *
	 * @param $item
	 * @return string
	 */
	public function generateitemredirect($item){
		return '/'; //Go to admin home, if the programmer does not written this function
		}
	/**
	 *
	 */
	public function itemaftersave($item,&$data){
		return true;
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
		//Create item
		$data->item=new $this->itemclass();
		//Get Additional data.
		$this->getfilterdata($data);
		//
		$data->do=BRequest::getString('do');
		$data->saving=(($data->do=='save')||($data->do=='save_exit'));
		$this->fieldsval=array();
		if(($data->saving)&&($data->can_edit)){
			if(!$this->itemsetfields($data->item)){
				$data->errors=$this->errors;
				$data->warnings=$this->warnings;
				$data->error=2;
				return $data;
				}
			$data->fieldsval=$this->fieldsval;
			if(empty($this->errors)){
				$r=$data->item->saveToDB();
				if($r){
					$this->itemaftersave($data->item,$data);
					$data->redirect=$this->generateitemredirect($data->item);
					if($data->do=='save_exit'){
						$data->redirect=$this->url_exit;
						}
					}
				}
			}
		//
		$data->error=0;
		$data->errors=$this->errors;
		$data->warnings=$this->warnings;
		return $data;
		}
	}