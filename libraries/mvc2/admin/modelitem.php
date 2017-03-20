<?php
/**
 * Model class for admin panel.
 *
 */
bimport('mvc.model');

abstract class BModelAdminItem extends BModel{
	protected $errors=array();
	protected $warnings=array();
	protected $url_exit;
	protected $fields=array();
	protected $itemclass='';
	protected $itemcollection='';
	protected $accessgroup='';
	protected $access_edit='';
	protected $access_view='';
	//
	public function __construct(){
		$item=new $this->itemclass();
		$fieldslist = $item->getfields();
		foreach($fieldslist as $field){
			//Skip readonly fields.
			if($field->readonly===true){
				continue;
				}
			$this->fields[$field->name]=(object)array(
				'name'=>$field->name,
				'required'=>false,
				'type'=>$field->type,
				'multilang'=>$field->multilang
				);
			}
		unset($item);
		return true;
		}

	/**
	 * Get additional data.
	 *
	 * @param $data
	 * @return bool
	 */
	protected function getfilterdata(&$data){
		return true;
		}
	//
	protected function fieldadd($name,$required=false){
		$this->fields[]=(object)array(
			'name'=>$name,
			'required'=>$required
			);
		}
	/**
	 *
	 */
	protected function getFieldVal($name,$type){
		switch($type){
			case 'string':
			case 'str':
				return BRequest::getString($name);
			case 'enum':
				return BRequest::getString($name);
			case 'image':
				return BRequest::getString($name);
			case 'dt':
				return BRequest::getString($name);
			case 'int':
			case 'integer':
				return BRequest::getInt($name);
			case 'item':
				return BRequest::getInt($name);
			case 'json':
				return BRequest::getVar($name,array());
			}
		}
	/**
	 *
	 */
	protected function itemsetfields(&$item){
		//
		$languages=BLang::langlist();
		//
		foreach($this->fields as $fld){
			if($fld->multilang===2){
				$this->fieldsval[$fld->name]=$this->getFieldVal($fld->name,$fld->type);
				foreach($languages as $lng){
					$this->fieldsval[$fld->name.'_'.$lng]=$this->getFieldVal($fld->name.'_'.$lng,$fld->type);
					}
				}
			elseif($fld->multilang){
				foreach($languages as $lng){
					$this->fieldsval[$fld->name.'_'.$lng]=$this->getFieldVal($fld->name.'_'.$lng,$fld->type);
					}
				}
			else{
				$this->fieldsval[$fld->name]=$this->getFieldVal($fld->name,$fld->type);
				}
			}
		//Validate & set values
		foreach($this->fields as $fld){
			if($fld->multilang===2){
				//Model validation.
				//Additional validation...
				if(!$item->setvarval($fld->name,$this->fieldsval[$fld->name],$fld->required)){
					$this->errors[$fld->name]=1;
					}
				foreach($languages as $lng){
					if(!$item->setvarval_lang($fld->name,$this->fieldsval[$fld->name.'_'.$lng],$lng,$fld->required)){
						$this->errors[$fld->name.'_'.$lng]=1;
						}
					}
				}
			elseif($fld->multilang){
				foreach($languages as $lng){
					if(!$item->setvarval_lang($fld->name,$this->fieldsval[$fld->name.'_'.$lng],$lng,$fld->required)){
						$this->errors[$fld->name.'_'.$lng]=1;
						}
					}
				}
			else{
				if(!$item->setvarval($fld->name,$this->fieldsval[$fld->name],$fld->required)){
					$this->errors[$fld->name]=1;
					}
				}
			}

		if(!empty($this->errors)){
			return false;
			}
		return true;
		}
	}