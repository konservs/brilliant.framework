<?php
bimport('mvc.model');
bimport('http.request');
class BModelAdminItemsTreeOrdering extends BModel{
	protected $itemclass='';
	protected $itemcollection='';
	protected $accessgroup='';
	protected $access_edit='';
	protected $access_view='';

	/**
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
		$collclass=$this->itemcollection;
		$collection=$collclass::getInstance();
		$ids=BRequest::getIntArray('id');
		$order = BRequest::getIntArray('order');
		$collection->items_update_ordering($ids,$order);
		$collection->rebuildtree();
		$data->error=0;
		return $data;
	}
}