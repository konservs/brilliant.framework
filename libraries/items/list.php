<?php

bimport('items.general');

abstract class BItemsList extends BItems{
	/**
	 *
	 */
	public function getSimpleListFilterItem(&$itm){
		return true;
		}

	/**
	 * Get simple list.
	 */
	public function getSimpleList($fields=array(),$transfields=array(),$lang='',$wh=array(),$order=''){
		$lang=$this->detectlang($lang);
		//
		$cachekey=$this->tablename.':simplelist:'.$lang;
		if(!empty($wh)){
			$cachekey.=':wh('.implode(';',$wh).')';
			}
		if(!empty($order)){
			$cachekey.=':order-'.$order;
			}
		//Try to get simple list from internal cache...
		static $cacheSimpleList=array();
		if(isset($cacheSimpleList[$cachekey])){
			return $cacheSimpleList[$cachekey];
			}
		//
		$bcache=BFactory::getCache();
		if($bcache){
			$res=$bcache->get($cachekey);
			if(($res!==false)&&($res!==NULL)){
				$this->cacheSimpleList[$lang]=$res;
				return $res;
				}
			}
		//Load simle cities names.
		if(!$db=BFactory::getDBO()){
			return NULL;
			}
		$qr='SELECT `'.$this->primarykey.'`';
		foreach($fields as $fld){
			$qr.=', `'.$fld.'`';
			}
		foreach($transfields as $fld){
			$qr.=', `'.$fld.'_'.$lang.'` as `'.$fld.'`';
			}
		$qr.=' FROM `'.$this->tablename.'`';
		if(!empty($wh)){
			$qr.=' WHERE ('.implode(' AND ',$wh).')';
			}
		//
		if(!empty($order)){
			$qr.=' ORDER BY '.$order.'';
			}
		$q=$db->Query($qr);
		if(empty($q)){
			return $res;
			}
		//
		$res=array();
		while($l=$db->fetch($q)){
			$id=(int)$l[$this->primarykey];
			$val=array();
			$val[$this->primarykey]=$id;
			foreach($fields as $fld){
				$val[$fld]=$l[$fld];
				}
			foreach($transfields as $fld){
				$val[$fld]=$l[$fld];
				}
			$xval=(object)$val;
			$this->getSimpleListFilterItem($xval);
			$res[$id]=$xval;
			}
		$cacheSimpleList[$cachekey]=$res;
		if($bcache){
			$bcache->set($cachekey,$res,3600);//1 hour
			}
		return $res;
		}
	}
