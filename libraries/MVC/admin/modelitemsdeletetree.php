<?php
bimport('mvc.model');
bimport('http.request');
class BModelItemsDeleteTree extends BModel{
	protected $itemclass='';
	protected $itemcollection='';
	protected $relatedtable;
	protected $accessgroup='';
	protected $access_edit='';
	protected $access_view='';
	protected $param='';
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
	 * @param $segments
	 * @return stdClass
	 */
	public function getData($segments){
		$data=new stdClass();
		$data->error=-1;
		//Check access rights
		$bau=BAdminUsers::getInstance();
		$data->can_view=$bau->can($this->accessgroup,$this->access_view);
		$data->can_edit=$bau->can($this->accessgroup,$this->access_edit);
		if((!$data->can_view)&&(!$data->can_edit)){
			$data->error=ERR_LOW_PRIVS;
			return $data;
		}
		$class=$this->itemcollection;
		$collection=$class::getInstance();

		$ids=BRequest::getString('ids');
		$data->arrayids=explode(',',$ids);

		//Всі id які будемо видаляти
		$data->allids=array();
		foreach($data->arrayids as $id){
			$params=array();
			$params['parenttree']=(int)$id;
			$children=$collection->items_filter($params);
			foreach($children as $cc){
				$data->allids[$cc->id]=$cc->id;
			}
		}
		// Список категорій без тих які хочемо видалити
		$data->items_move=$collection->items_filter(array('exclude'=>$data->allids));

		//Кількість item які привязані до категорій які ми хочемо видаляти
		$class2=$this->relatedtable;
		$item=$class2::getInstance();
		if(!empty($this->param)){
			$params[$this->param]=$data->allids;
			$data->count=$item->items_filter_count($params);
		}

		$data->do=BRequest::getString('do');
		if(($data->can_edit) && $data->do=='delete'){
			//Пререносим item в іншу категорію
			$category=BRequest::getInt('category');
			$updatecategory=$item->update_item_tree($category,$data->allids);
			if(!$updatecategory){
				$data->error=1;
				return $data;
			}

			//Видаляємо категорію
			$data->deleting=true;
			$delete=$collection->items_delete_tree($data->allids);
			if(!$delete){
				$data->error=1;
				return $data;
			}
			$data->redirect=$this->generateitemredirect($data->item);
		}
		return $data;
	}
}