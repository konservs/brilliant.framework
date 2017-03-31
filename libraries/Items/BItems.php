<?php
/**
 * Abstract class for items collections
 *
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant\Items;

use Brilliant\Log\BLog;

abstract class BItems{
	protected $tableName='';
	protected $itemClassName='';

	protected $searchtablename='';
	protected $primaryKeyName='id';
	protected $orderingkey='ordering';
	protected $hitskey='';
	protected $hits_daily_table='';
	protected $linkedTables=array();
	protected $cache_items=array();
	protected $cachetime=3600;
	/**
	 * Detect necessary language, if $lang is not set.
	 * If the $lang is set - return it;
	 *
	 * @param string $lang
	 * @return string detected language
	 */
	protected function detectLanguage($lang){
		if(empty($lang)){
			bimport('cms.language');
			$lang=\Brilliant\CMS\BLang::$langcode;
			}
		return $lang;
		}
	/**
	 * Get sphinx / mysql database.
	 */
	public function getDBO(){
		$db=\Brilliant\BFactory::getDBO();
		return $db;
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
	public function itemsGet($ids){
		if((!is_array($ids))||(empty($ids))){
			BLog::addToLog('[BItems.'.$this->tableName.'] itemsGet() $ids empty='.var_export($ids,true).')',LL_ERROR);
			return array();
			}
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[BItems.'.$this->tableName.'] itemsGet('.implode(',',$ids).')');
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
					$ids_k[$idd]=$this->tableName.':itemid:'.$idd;
					}
				}
			}
		if(empty($ids_c)){
			return $items;
			}

		//-------------------------------------------------
		//Trying to get left items from external cache
		//-------------------------------------------------
		$cache=\Brilliant\BFactory::getCache();
		if(!empty($cache)){
			$ids_m=array();
			$ids_q=array();
			$items_c=$cache->mget($ids_k);
			foreach($ids_c as $id){
				$idd=is_array($id)?implode(':',$id):$id;
				$key=$this->tableName.':itemid:'.$idd;
				if((isset($items_c[$key]))&&(!empty($items_c[$key]))){
					$classname=$this->itemClassName;
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
		$db=\Brilliant\BFactory::getDBO();
		if(empty($db)){
			return $items;
			}
		if(is_array($this->primaryKeyName)){
			$whi=array();
			foreach($ids_q as $id){
				$s=array();
				foreach($id as $idk=>$idv){
					$s[]='(`'.$idk.'`='.$db->escapeString($idv).')';
					}
				$whi[]='('.implode(' AND ',$s).')';
				}
			$wh=implode(' OR ',$whi);
			$qr='SELECT * from `'.$this->tableName.'` WHERE ('.$wh.')';
			}else{
			$qr='SELECT * from `'.$this->tableName.'` WHERE (`'.$this->primaryKeyName.'` in ('.implode(',',$ids_q).'))';
			}
		$q=$db->Query($qr);
		if(empty($q)){
			BLog::addToLog('[items]: itemsGet(): Could not execute query! MySQL error: '.$db->lasterror(),LL_ERROR);
			return $items;
			}
		$tocache=array();
		$item_obj=array();
		while($l=$db->fetch($q)){
			if(is_array($this->primaryKeyName)){
				$id=array();
				foreach($this->primaryKeyName as $pk){
					$id[$pk]=$l[$pk];
					}
				$idd=implode(':',$id);
				$item_obj[$idd]=$l;
				}else{
				$idd=(int)$l[$this->primaryKeyName];
				$item_obj[$idd]=$l;
				}
			foreach($this->linkedTables as $tbl){
				$item_obj[$idd][$tbl['field']]=array();
				}
			}
		//-------------------------------------------------
		// Loading data from external tables...
		//-------------------------------------------------
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[BItems.'.$this->tableName.'] itemsGet() Loading data from external tables...');
			}
		foreach($this->linkedTables as $tbl){
			$wh=array();
			$wh[]='(`'.$tbl['extkey'].'` in ('.implode(',',$ids_q).'))';
			if(!empty($tbl['filter'])){
				$wh[]=$tbl['filter'];
				}
			$qrl='SELECT * from `'.$tbl['name'].'` WHERE ('.implode(' AND ',$wh).')';
			$ql=$db->Query($qrl);
			if(empty($ql)){
				BLog::addToLog('[items]: itemsGet(): Could not execute external tables query! MySQL error: '.$db->lasterror(),LL_ERROR);
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
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[BItems.'.$this->tableName.'] itemsGet() Creating item objects...');
			}
		foreach($item_obj as $k=>$l){
			$classname=$this->itemClassName;
			if(!class_exists($classname)){
				$msg='Class "'.$classname.'" does not exist!';
				BLog::addToLog($msg,LL_ERROR);
				die($msg);
				}
			$items[$k]=new $classname();
			$items[$k]->load($l);
			$this->cache_items[$k]=$items[$k];
			if(CACHE_TYPE){
				$tocache[$this->tableName.':itemid:'.$k]=$l;
				}
			}
		//if($this->tableName=='news_articles'){
		//	var_dump($item_obj); die('k2');
		//	}
		//-------------------------------------------------
		// Cache storing, if necessary.
		//-------------------------------------------------
		if((!empty($cache))&&(count($tocache)!=0)){
			$cache->mset($tocache,$this->cachetime);//1 hour
			}
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[BItems.'.$this->tableName.'] itemsGet() All done! Returning items.');
			}
		return $items;
		}
	/**
	 * Get single item
	 *
	 * @param $id
	 * @return BItemsItem
	 */
	public function itemGet($id){
		$list=$this->itemsGet(array($id));
		$item=reset($list);
		return $item;
		}

	/**
	 * Get Items by params
	 *
	 * @param $params array
	 * @return BItemsItem[]
	 */
	public function itemsFilter($params){
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[BItems.'.$this->tableName.'] itemsFilter('.var_export($params,true).')');
			}
		$ids=$this->itemsFilterIds($params);
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[BItems.'.$this->tableName.'] itemsFilter got IDs: '.var_export($ids,true));
			}
		$result = $this->itemsGet($ids);
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[BItems.'.$this->tableName.'] Got items!');
			}
		return $result;
		}
	/**
	 * Get First Item by params
	 *
	 * @param $params array
	 * @return BItemsItem
	 */
	public function itemsFilterFirst($params){
		$params2 = $params;
		$params2['limit']=1;
		$list = $this->itemsFilter($params2);
		if(empty($list)){
			return NULL;
			}
		$item = reset($list);
		return $item;
		}
	/**
	 * Get all items
	 *
	 * @return BItemsItem[]
	 */
	public function itemsGetAll(){
		$params=array();
		return $this->itemsFilter($params);
		}

	/**
	 * Get items filter
	 * @param $params
	 * @param $wh
	 * @param $jn
	 * @return bool
	 */
	public function itemsFilterSql($params,&$wh,&$jn){
		$wh=array();
		$jn=array();
		if(isset($params['exclude'])&&(is_array($params['exclude']))){
			$wh[]= '(`'.$this->primaryKeyName.'` not in ('.implode(',', $params['exclude']).'))';
			}
		return true;
		}

	/**
	 * Items list cache hash.
	 *
	 * @param $params array
	 * @return string
	 */
	public function itemsFilterHash($params){
		$itemshash=$this->tableName.':list';
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
	public function itemsFilterIds($params){
		//
		$cacheenabled=(!empty($params['cacheenabled']));
		if($cacheenabled){
			$bcache=\Brilliant\BFactory::GetCache();
			}
		if($bcache){
			$hash=$this->itemsFilterHash($params);
			$ids=$bcache->get($hash);
			if(($ids!==false)&&($ids!==NULL)){
				return $ids;
				}
			}
		//
		if(!$db=$this->getDBO()){
			return false;
			}
		if(is_array($this->primaryKeyName)){
			$flds=array();
			foreach($this->primaryKeyName as $pk){
				$flds[]='`'.$this->tableName.'`.`'.$pk.'`';
				}
			$qr='select '.implode(',',$flds).' from `'.$this->tableName.'`';
			}else{
			$qr='select `'.$this->tableName.'`.`'.$this->primaryKeyName.'` from `'.$this->tableName.'`';
			}
		$this->itemsFilterSql($params,$wh,$jn);
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
			if(is_array($this->primaryKeyName)){
				$id=array();
				foreach($this->primaryKeyName as $pk){
					$id[$pk]=$l[$pk];
					}
				}else{
				$id=(int)$l[$this->primaryKeyName];
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
	public function itemsFilterCount($params){
		if(!$db=$this->getDBO()){
			return false;
			}
		$qr='select count(*) as cnt from `'.$this->tableName.'`';
		$this->itemsFilterSql($params,$wh,$jn);
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
	public function itemsDelete($ids){
		$db=\Brilliant\BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$qr='DELETE FROM `'.$this->tableName.'` WHERE (`'.$this->primaryKeyName.'` in ('.implode(',', $ids).'))';
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
	public function itemsUpdateOrdering($ids,$order){
		$db=\Brilliant\BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$values=array();
		foreach($ids as $i=>$id){
			$values[]='('.$id.','.$i.')';
			}
		$qr='INSERT INTO `'.$this->tableName.'` (`'.$this->primaryKeyName.'`,`'.$this->orderingkey.'`)';
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
	public function flushInternalCache(){
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
		$itemsids=$this->searchIds($params);
		$items=$this->itemsGet($itemsids);
		return $items;
		}
	/**
	 *
	 */
	public function searchIds($params){
		bimport('search.sphinx.sphinxql');
		$spx=BSearchSphinxQl::getInstanceAndConnect();
		if(empty($spx)){
			return false;
			}
		$this->search_sql($params,$wh);
		$qr='SELECT '.$this->primaryKeyName.' from `'.$this->searchtablename.'`';
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
	public function hitItem($id){
		if(empty($this->hitskey)){
			return false;
			}
		$db=\Brilliant\BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$qr='UPDATE `'.$this->tableName.'` SET `hits`=`hits`+1 WHERE (`'.$this->primaryKeyName.'`='.$id.')';
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
	/**
	 *
	 */
	public function truncateAll(){
		$qr='truncate `'.$this->tableName.'`';
		$db=\Brilliant\BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		$q=$db->Query($qr);
		if(empty($q)){
			return false;
			}
		return true;
		}
	}
