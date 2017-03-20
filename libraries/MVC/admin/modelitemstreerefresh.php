<?php
/**
 *
 */
bimport('mvc.model');
class BModelAdminItemsTreeRefresh extends BModel{
	protected $itemclass='';
	protected $itemcollection='';
	protected $accessgroup='';
	protected $access_edit='';
	protected $access_view='';

	/**
	 * Add text into log.
	 */
	public function logadd(&$log,$text){
		$log[]=(object)array(
			'text'=>$text
		);
	}
	/**
	 * Main data & actions function.
	 */
	public function get_data($segments){
		$data=new stdClass;
		$data->triglog=array();
		$data->error=-1;
		$bau=BAdminUsers::getInstance();
		$data->can_view=$bau->can($this->accessgroup,$this->access_view);
		$data->can_edit=$bau->can($this->accessgroup,$this->access_edit);
		if((!$data->can_view)&&(!$data->can_edit)){
			$data->error=ERR_LOW_PRIVS;
			return $data;
		}
		//
		$this->logadd($data->triglog,'Обновление Nested Set.');
		$collclass=$this->itemcollection;
		$collection=$collclass::getInstance();
		$res1=$collection->rebuildtree();
		if(!$res1){
			$this->logadd($data->triglog,'Произошла ошибка при обновлении Nested Set.');
		}else{
			$this->logadd($data->triglog,'Дерево Nested Set успешно обновлено!');
		}
		$data->error=0;
		return $data;
	}
}