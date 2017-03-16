<?php
//============================================================
// Abstract class for collections
//
// Author: Andrii Biriev
//============================================================
bimport('items.item');
bimport('log.general');

abstract class BItems{
	protected $tablename='';
	protected $searchtablename='';
	protected $itemclassname='';
	protected $primarykey='id';
	protected $orderingkey='ordering';
	protected $hitskey='';
	protected $hits_daily_table='';
	protected $linkedtables=array();
	protected $cache_items=array();
	protected $cachetime=3600;
	/**
	 * Detect necessary language, if $lang is not set.
	 * If the $lang is set - return it;
	 *
	 * @param string $lang
	 * @return string detected language
	 */
	protected function detectlang($lang){
		if(empty($lang)){
			bimport('cms.language');
			$lang=BLang::$langcode;
			}
		return $lang;
		}
	/**
	 * Get sphinx / mysql database.
	 */
	public function getDBO(){
		$db=BFactory::getDBO();
		return $db;
		//bimport('search.sphinx.sphinxql');
		}
	/**
	 *
	 */
	public function setSearchTableName($tbl){
		$this->searchtablename=$tbl;
		}
	/**
	 * Load data from db/cache array.
	 */
	public function items_get($ids){
		if(!is_array($ids)){
			return array();
			}
		if(DEBUG_LOG_BITEMS){
			BLog::addtolog('[BItems.'.$this->tablename.'] items_get('.implode(',',$ids).')');
			}
		$items=array();
		//-------------------------------------------------
		//Trying to get items from internal cache
		//-------------------------------------------------
		$ids_c=array(); //IDs as integer
		$ids_k=array(); //IDs as external cache key
		foreach($ids as $id){
			$idd=is_array($id)?implode(':',$id):$id;
			if(isset($this->cache_items[$idd])){
				$items[$idd]=$this->cache_items[$idd];
				}else{
				if(!empty($idd)){
					$items[$idd]=NULL;
					$ids_c[$idd]=$id;
					$ids_k[$idd]=$this->tablename.':itemid:'.$idd;
					}
				}
			}
		if(empty($ids_c)){
			return $items;
			}

		//-------------------------------------------------
		//Trying to get left items from external cache
		//-------------------------------------------------
		$cache=BFactory::getCache();
		if(!empty($cache)){
			$ids_m=array();
			$ids_q=array();
			$items_c=$cache->mget($ids_k);
			foreach($ids_c as $id){
				$idd=is_array($id)?implode(':',$id):$id;
				$key=$this->tablename.':itemid:'.$idd;
				if((isset($items_c[$key]))&&(!empty($items_c[$key]))){
					$classname=$this->itemclassname;
					$items[$idd]=new $classname();
					$items[$idd]->load($items_c[$key]);
					$this->cache_items[$idd]=$items[$idd];
					}else{
					array_push($ids_m,$id);
					array_push($ids_q,$id);
					}
				}
			}else{
			$ids_m=$ids_c;
			$ids_q=$ids_c;
			}
		if(empty($ids_m)){
			return $items;
			}
		//-------------------------------------------------
		// Trying to get left items from database
		//-------------------------------------------------
		$db=BFactory::getDBO();
		if(empty($db)){
			return $items;
			}
		if(is_array($this->primarykey)){
			$whi=array();
			foreach($ids_q as $id){
				$s=array();
				foreach($id as $idk=>$idv){
					$s[]='(`'.$idk.'`='.$db->escape_string($idv).')';
					}
				$whi[]='('.implode(' AND ',$s).')';
				}
			$wh=implode(' OR ',$whi);
			$qr='SELECT * from `'.$this->tablename.'` WHERE ('.$wh.')';
			}else{
			$qr='SELECT * from `'.$this->tablename.'` WHERE (`'.$this->primarykey.'` in ('.implode(',',$ids_q).'))';
			}
		$q=$db->Query($qr);
		if(empty($q)){
			BLog::addtolog('[items]: items_get(): Could not execute query! MySQL error: '.$db->lasterror(),LL_ERROR);
			return $items;
			}
		$tocache=array();
		$item_obj=array();
		while($l=$db->fetch($q)){
			if(is_array($this->primarykey)){
				$id=array();
				foreach($this->primarykey as $pk){
					$id[$pk]=$l[$pk];
					}
				$idd=implode(':',$id);
				$item_obj[$idd]=$l;
				}else{
				$idd=(int)$l[$this->primarykey];
				$item_obj[$idd]=$l;
				}
			foreach($this->linkedtables as $tbl){
				$item_obj[$idd][$tbl['field']]=array();
				}
			}
		//-------------------------------------------------
		// Loading data from external tables...
		//-------------------------------------------------
		foreach($this->linkedtables as $tbl){
			$wh=array();
			$wh[]='(`'.$tbl['extkey'].'` in ('.implode(',',$ids_q).'))';
			if(!empty($tbl['filter'])){
				$wh[]=$tbl['filter'];
				}
			$qrl='SELECT * from `'.$tbl['name'].'` WHERE ('.implode(' AND ',$wh).')';
			$ql=$db->Query($qrl);
			if(empty($ql)){
				BLog::addtolog('[items]: items_get(): Could not execute external tables query! MySQL error: '.$db->lasterror(),LL_ERROR);
				return false;
				}
			$datal=array();
			while($ll=$db->fetch($ql)){
				$itemid=(int)$ll[$tbl['extkey']];
				$item_obj[$itemid][$tbl['field']][]=$ll;
				}
			}
		//-------------------------------------------------
		// Creating item object...
		//-------------------------------------------------
		foreach($item_obj as $k=>$l){
			$classname=$this->itemclassname;
			if(!class_exists($classname)){
				$msg='Class "'.$classname.'" does not exist!';
				BLog::addtolog($msg,LL_ERROR);
				die($msg);
				}
			$items[$k]=new $classname();
			$items[$k]->load($l);
			$this->cache_items[$k]=$items[$k];
			if(CACHE_TYPE){
				$tocache[$this->tablename.':itemid:'.$k]=$l;
				}
			}
		//if($this->tablename=='news_articles'){
		//	var_dump($item_obj); die('k2');
		//	}
		//-------------------------------------------------
		// Cache storing, if necessary.
		//-------------------------------------------------
		if(CACHE_TYPE&&count($tocache)!=0){
			$cache->mset($tocache,$this->cachetime);//1 hour
			}
		return $items;
		}
	/**
	 * Get single item
	 *
	 * @param $id
	 * @return BItemsItem
	 */
	public function item_get($id){
		$list=$this->items_get(array($id));
		$item=reset($list);
		return $item;
		}

	/**
	 * Get Items by params
	 *
	 * @param $params array
	 * @return BItemsItem[]
	 */
	public function items_filter($params){
		if(DEBUG_LOG_BITEMS){
			BLog::addtolog('[BItems.'.$this->tablename.'] items_filter('.var_export($params,true).')');
			}
		$ids=$this->items_filter_ids($params);
		if(DEBUG_LOG_BITEMS){
			BLog::addtolog('[BItems.'.$this->tablename.'] items_filter got IDs: '.var_export($ids,true));
			}
		return $this->items_get($ids);
		}
	/**
	 * Get all items
	 *
	 * @return BItemsItem[]
	 */
	public function items_get_all(){
		$params=array();
		return $this->items_filter($params);
		}

	/**
	 * Get items filter
	 * @param $params
	 * @param $wh
	 * @param $jn
	 * @return bool
	 */
	public function items_filter_sql($params,&$wh,&$jn){
		$wh=array();
		$jn=array();
		if(isset($params['exclude'])&&(is_array($params['exclude']))){
			$wh[]= '(`'.$this->primarykey.'` not in ('.implode(',', $params['exclude']).'))';
			}
		return true;
		}

	/**
	 * Items list cache hash.
	 *
	 * @param $params array
	 * @return string
	 */
	public function items_filter_hash($params){
		$itemshash=$this->tablename.':list';
		if(isset($params['exclude'])&&(is_array($params['exclude']))){
			$itemshash.=':exclude-'.implode('-',$params['exclude']);
			}
		if(isset($params['orderby'])&&(is_array($params['orderby']))){
			$orderdir=isset($params['orderdir'])?'-'.$params['orderdir']:'';
			$itemshash.=':orderby-'.$params['orderby'].$orderdir;
			}
		if(!empty($params['limit'])){
			$limit=(int)$params['limit'];
			$offset=(int)$params['offset'];
			$itemshash.=':limit-'.$limit;
			if($offset){
				$itemshash.=':offset-'.$offset;
				}
			}
		return $itemshash;
		}

	/**
	 * Get IDS filter
	 *
	 * @param $params
	 * @return array|null
	 */
	public function items_filter_ids($params){
		//
		$cacheenabled=(!empty($params['cacheenabled']));
		if($cacheenabled){
			$bcache=BFactory::GetCache();
			}
		if($bcache){
			$hash=$this->items_filter_hash($params);
			$ids=$bcache->get($hash);
			if(($ids!==false)&&($ids!==NULL)){
				return $ids;
				}
			}
		//
		if(!$db=$this->getDBO()){
			return false;
			}
		if(is_array($this->primarykey)){
			$flds=array();
			foreach($this->primarykey as $pk){
				$flds[]='`'.$this->tablename.'`.`'.$pk.'`';
				}
			$qr='select '.implode(',',$flds).' from `'.$this->tablename.'`';
			}else{
			$qr='select `'.$this->tablename.'`.`'.$this->primarykey.'` from `'.$this->tablename.'`';
			}
		$this->items_filter_sql($params,$wh,$jn);
		if(!empty($jn)){
			$qr.=' '.implode(' ',$jn);
			}
		if(!empty($wh)){
			$qr.=' WHERE ('.implode(' AND ',$wh).')';
			}
		if(isset($params['orderby'])){
			$orderasc=isset($params['orderdir'])?' '.$params['orderdir']:'';
			if($params['orderby']=="RAND()"){
				$qr.=' ORDER BY '.$params['orderby'].''.$orderasc;
				}else{
				$qr.=' ORDER BY `'.$params['orderby'].'`'.$orderasc;
				}
			}
		//Limit & offset
		if(!empty($params['limit'])){
			$limit=(int)$params['limit'];
			$offset=(int)$params['offset'];
			$qr.=' LIMIT '.$limit;
			if($offset){
				$qr.=' OFFSET '.$offset;
				}
			}
		//Execute query
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$ids=array();
		while($l=$db->fetch($q)){
			if(is_array($this->primarykey)){
				$id=array();
				foreach($this->primarykey as $pk){
					$id[$pk]=$l[$pk];
					}
				}else{
				$id=(int)$l[$this->primarykey];
				}
			$ids[]=$id;
			}
		//
		if($bcache){
			$bcache->set($hash,$ids,$this->cachetime);
			}
		//
		return $ids;
		}

	/**
	 * @param $params
	 * @return bool|int
	 */
	public function items_filter_count($params){
		if(!$db=$this->getDBO()){
			return false;
			}
		$qr='select count(*) as cnt from `'.$this->tablename.'`';
		$this->items_filter_sql($params,$wh,$jn);
		if(!empty($jn)){
			$qr.=' '.implode(' ',$jn);
			}
		if(!empty($wh)){
			$qr.=' WHERE ('.implode(' AND ',$wh).')';
			}
		//Execute query
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		$l=$db->fetch($q);
		return (int)$l['cnt'];
		}
	//========================================================
	// Search - internal cache variable
	// Keys - necessary IDs
	// notexists - IDs to load from database or external cache
	// 
	// returns values by keys
	//========================================================
	protected function split_intcache($intcache,$keys,&$notexist){
		if(!is_array($intcache)){
			$notexist=$keys;
			return array();
			}
		$notexist=array();
		$res=array();
		foreach($keys as $key){
			if(isset($intcache[$key])){
				$res[$key]=$intcache[$key];
				}else{
				$notexist[$key]=$key;
				}
			}
		return $res;
		}
	//========================================================
	// $extcache - array
	// 
	// returns associative array of loaded data
	//========================================================
	protected function split_extcache($data,$keys,&$notexist){
		$extcache=array();
		foreach($keys as $id=>$key){
			if(!empty($data[$key])){
				$extcache[$id]=$data[$key];
				}else{
				$notexist[$id]=$id;
				}
			}
		return $extcache;
		}
	//========================================================
	//
	//========================================================
	protected function generatekeys($ids,$prefix){
		$keys=array();
		foreach($ids as $id){
			$keys[$id]=$prefix.$id;
			}
		return $keys;
		}
	/**
	 * Delete item from database
	 *
	 * @param $ids
	 * @return bool
	 */
	public function items_delete($ids){
		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$qr='DELETE FROM `'.$this->tablename.'` WHERE (`'.$this->primarykey.'` in ('.implode(',', $ids).'))';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		//TODO delete cache
		return true;
		}
	/**
	 * @param $ids
	 * @param $order
	 * @return bool
	 */
	public function items_update_ordering($ids,$order){
		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$values=array();
		foreach($ids as $i=>$id){
			$values[]='('.$id.','.$i.')';
			}
		$qr='INSERT INTO `'.$this->tablename.'` (`'.$this->primarykey.'`,`'.$this->orderingkey.'`)';
		$qr.=' VALUES '.implode(',',$values);
		$qr.=' ON DUPLICATE KEY UPDATE '.$this->orderingkey.'=VALUES('.$this->orderingkey.')';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		return true;
		}
	/**
	 * Flush internal cache.
	 */
	public function flushinternalcache(){
		foreach($this->cache_items as &$itm){
			unset($itm);
			}
		$this->cache_items=array();
		}
	/**
	 *
	 */
	public function search_sql($params,&$wh){
		$wh=array();
		if(!empty($params['q'])){
			$q=$params['q'];
			$q=str_replace("\'","",$q);
			$q=str_replace("\"","",$q);
			$q='\''.$q.'\'';
			$wh[]='(MATCH('.$q.'))';
			}
		return true;
		}
	/**
	 *
	 */
	public function search($params){
		$itemsids=$this->search_ids($params);
		$items=$this->items_get($itemsids);
		return $items;
		}
	/**
	 *
	 */
	public function search_ids($params){
		bimport('search.sphinx.sphinxql');
		$spx=BSearchSphinxQl::getInstanceAndConnect();
		if(empty($spx)){
			return false;
			}
		$this->search_sql($params,$wh);
		$qr='SELECT '.$this->primarykey.' from `'.$this->searchtablename.'`';
		if(!empty($wh)){
			$qr.=' where ('.implode('AND',$wh).')';
			}
		//ordering
		if(!empty($params['orderby'])) {
			$orderasc = isset($params['orderdir']) ? ' ' . $params['orderdir'] : '';
			$qr .= ' ORDER BY ' . (empty($params['orderby']) ? '' : $params['orderby'] . $orderasc);
			}
		//Limit & offset
		if(!empty($params['limit'])){
			$limit=(int)$params['limit'];
			$offset=(int)$params['offset'];
			$qr.=' LIMIT '.(empty($offset)?0:$offset).','.(!isset($limit)?10:(int)$limit);
			}
		$q=$spx->Query($qr);
		if(empty($q)){
			return false;
			}
		$itemsids=array();
		while($l=$spx->fetch($q)){
			$id=(int)$l['id'];
			$itemsids[$id]=$id;
			}
		return $itemsids;
		}
	/**
	 * Hit the item.
	 */
	public function hititem($id){
		if(empty($this->hitskey)){
			return false;
			}
		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$qr='UPDATE `'.$this->tablename.'` SET hits=hits+1 WHERE (`'.$this->primarykey.'`='.$id.')';
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		return true;
		}
	/**
	 *
	 */
	public function search_count($params){
		bimport('search.sphinx.sphinxql');
		$spx=BSearchSphinxQl::getInstanceAndConnect();
		if(empty($spx)){
			return false;
			}
		$this->search_sql($params,$wh);
		$qr='SELECT count(*) as cnt from `'.$this->searchtablename.'`';
		if(!empty($wh)){
			$qr.=' where ('.implode('AND',$wh).')';
			}
		$q=$spx->Query($qr);
		if(empty($q)){
			return false;
			}
		$l=$spx->fetch($q);
		return $l['cnt'];
		}
	}