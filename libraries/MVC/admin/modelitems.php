<?php
/**
 * Model class for admin panel.
 *
 */
bimport('mvc.model');

abstract class BModelAdminItems extends BModel{
	protected $itemclass='';
	protected $itemcollection='';
	protected $accessgroup='';
	protected $access_edit='';
	protected $access_view='';
	protected $itemslimit=10;
	/**
	 *
	 */
	public function __construct(){
		return true;
		}
	/**
	 *
	 */
	public function get_filters(&$data,&$params){
		return true;
		}

	/***
	 *
	 */
	protected function items_delete(&$data){
		return true;
		}
	/**
	 * Get data
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
		$collclass=$this->itemcollection;
		$collection=$collclass::getInstance();

		$pagination=BPagination::getInstance();
		$pagination->type=PGTYPE_GET;
		$pagination->setLimit(BRequest::getString('itemsperpage')?BRequest::getString('itemsperpage'):$this->itemslimit);
		$params=array();
		$this->get_filters($data,$params);
		$this->items_delete($data);
		$params['limit']=$pagination->getLimit();
		$params['offset']=$pagination->getOffset();
		$pagination->items_count=$collection->items_filter_count($params);
		$data->items=$collection->items_filter($params);
		$data->pagination=$pagination;
		$data->error=0;
		return $data;
		}
	}
