<?php
abstract class BItemsTree extends BItems{
	protected $leftkey='lft';
	protected $rightkey='rgt';
	protected $levelkey='level';
	protected $parentkey='parent';
	/**
	 *
	 */
	public function items_filter_ids($params){
		if(empty($params['orderby'])){
			$params['orderby']=$this->leftkey;
			}
		return parent::items_filter_ids($params);
		}
	/**
	 *
	 */
	public function items_filter_sql($params,&$wh,&$jn){
		//
		$db=BFactory::getDBO();
		//Call parent method.
		parent::items_filter_sql($params,$wh,$jn);

		//Select categories only with some level.
		if(isset($params['level'])){
			$wh[]='(`'.$this->levelkey.'`='.(int)$params['level'].')';
			}
		//Select categories only with parentid=$params['parent'].
		if(isset($params['parent'])){
			$wh[]='(`'.$this->parentkey.'`='.(int)$params['parent'].')';
			}
		//Entire parents tree. 
		if(isset($params['parenttree'])){
			$itemid=(int)$params['parenttree'];
			$item=$this->item_get($itemid);
			if(empty($item)){
				return false;
				}
			$wh[]='(`'.$this->leftkey.'`>='.$item->lft.')';
			$wh[]='(`'.$this->rightkey.'`<='.$item->rgt.')';
			}
		//Entire parents tree, second version. 
		if((isset($params['parenttree_lft']))&&(isset($params['parenttree_rgt']))){
			$lft=$params['parenttree_lft'];
			$rgt=$params['parenttree_rgt'];
			if(($lft<1)||($rgt<1)||($lft>=$rgt)){
				return false;
				}
			$wh[]='(`'.$this->leftkey.'`>='.$lft.')';
			$wh[]='(`'.$this->rightkey.'`<='.$rgt.')';
			}
		//Entire parents chain. 
		if(isset($params['parentchain'])){
			$itemid=(int)$params['parentchain'];
			$item=$this->item_get($itemid);
			if(empty($item)){
				return false;
				}
			$wh[]='(`'.$this->leftkey.'`<='.$item->lft.')';
			$wh[]='(`'.$this->rightkey.'`>='.$item->rgt.')';
			}
		//Entire parents chain. 
		if((isset($params['parentchain_lft']))&&(isset($params['parentchain_rgt']))){
			$wh[]='(`'.$this->leftkey.'`<='.(int)$params['parentchain_lft'].')';
			$wh[]='(`'.$this->rightkey.'`>='.(int)$params['parentchain_rgt'].')';
			}

		return true;
		}
	/**
	 * News categories tree cache hash.
	 *
	 */
	public function items_filter_hash($params){
		$db=BFactory::getDBO();
		$itemshash=parent::items_filter_hash($params);

		//Select categories only with some level
		if(isset($params['level'])){
			$itemshash.=':level='.$params['level'];
			}
		//Select categories only with parentid=$params['parent'].
		if(isset($params['parent'])){
			$itemshash.=':parent='.$params['parent'];
			}
		//Entire parents tree. 
		if(isset($params['parenttree'])){
			$itemid=(int)$params['parenttree'];
			$item=$this->item_get($itemid);
			if(empty($item)){
				return false;
				}
			$itemshash.=':parenttree-'.$item->lft.'-'.$item->rgt;
			}
		//Entire parents tree, second version. 
		if((isset($params['parenttree_lft']))&&(isset($params['parenttree_rgt']))){
			$lft=$params['parenttree_lft'];
			$rgt=$params['parenttree_rgt'];
			if(($lft<1)||($rgt<1)||($lft>=$rgt)){
				return false;
				}
			$itemshash.=':parenttree-'.$lft.'-'.$rgt;
			}
		//Entire parents chain. 
		if(isset($params['parentchain'])){
			$itemid=(int)$params['parentchain'];
			$item=$this->item_get($itemid);
			if(empty($item)){
				return false;
				}
			$itemshash.=':parentchain-'.$item->lft.'-'.$item->rgt;
			}
		//Entire parents chain, second version. 
		if((isset($params['parentchain_lft']))&&(isset($params['parentchain_rgt']))){
			$lft=$params['parentchain_lft'];
			$rgt=$params['parentchain_rgt'];
			if(($lft<1)||($rgt<1)||($lft>=$rgt)){
				return false;
				}
			$itemshash.=':parentchain-'.$lft.'-'.$rgt;
			}
		return $itemshash;
		}
	/**
	 *
	 */
	public function getitembyaliaschain($aliases,$lang=''){
		$hash='';
		foreach($aliases as $alias){
			$hash.=(empty($hash)?'':':').$alias;
			}
		$key=$this->tablename.':chain:'.$hash;
		//External cache... 

		//items tree.
		$item1=$this->item_get(1);
		if(empty($item1)){
			BLog::addtolog('[BItemsTree] getitembyaliaschain(): Could not get root items!',LL_ERROR);
			return NULL;
			}
		//Aliases
		foreach($aliases as $alias){
			$item1=$item1->children($lang,$alias);
			if(empty($item1)){
				BLog::addtolog('[BItemsTree]: getitembyaliaschain() Could not get news item children!',LL_ERROR);
				return NULL;
				}
			}
		return $item1;
		}
	/**
	 * Recursive.
	 */
	protected function rebuildtree_recursive($cat,&$lft,&$rgt){
		BLog::addtolog('[Items] Processing item ['.$cat->id.']');
		$ch=$this->items_filter(array('parent'=>$cat->id));
		BLog::addtolog('[Items] Fill children array and sort them by "ordering". count='.count($ch));
		//
		$children=array();
		foreach($ch as $c){
			$children[]=$c;
			}
		//
		$n=count($children);
		for($i=0; $i<$n; $i++){
			$m=$i;
			for($j=$i+1; $j<$n; $j++){
				if($children[$j]->ordering < $children[$m]->ordering){
					$m=$j;
					}
				}
			if($m!=$i){
				$t=$children[$i];
				$children[$i]=$children[$m];
				$children[$m]=$t;
				}
			}
		//
		if(empty($children)){
			return true;
			}
		foreach($children as $ccat){
			$ccat->level=$cat->level+1;

			$lftrec=$lft+1;
			$rgtrec=$rgt+1;
			$this->rebuildtree_recursive($ccat,$lftrec,$rgtrec);
			$ccat->lft=$lft+1;
			$ccat->rgt=$rgtrec;

			$lft=$lftrec+1; $rgt=$rgtrec+1;
			}
		return true;
		}
	/**
	 * Refresh categories tree nested set.
	 */
	public function rebuildtree(){
		//Rebuild categories ads trigger count.

		//Rebuild nested set - get cat
		BLog::addtolog('[Items] rebuilding nested sets...');
		//
		$bcache=BFactory::getCache();
		if($bcache){
			$bcache->invalidate();
			}
		//
		$catslist=$this->items_filter(array());
		BLog::addtolog('[Items] Total categories count:'.count($catslist).'...');

		$rootcats=array();
		//
		foreach($catslist as $cat){
			$cat->level=0;
			$cat->lft=0;
			$cat->rgt=0;
			if(empty($cat->{$this->parentkey})){
				$rootcats[]=$cat;
				}
			}
		//Sort root categories.
		$n=count($rootcats);
		BLog::addtolog('[Items] Root categories count:'.$n.'...');
		for($i=0; $i<$n; $i++){
			$m=$i;
			for($j=$i+1; $j<$n; $j++){
				if($rootcats[$j]->ordering < $rootcats[$m]->ordering){
					$m=$j;
					}
				}
			if($m!=$i){
				$t=$rootcats[$i];
				$rootcats[$i]=$rootcats[$m];
				$rootcats[$m]=$t;
				}
			}

		//Foreach by root categories...
		foreach($rootcats as $rcat){
			BLog::addtolog('[Items] Processing root category ['.$rcat->id.']');

			$rcat->level=1;
			$lft=1; $rgt=2;
			$this->rebuildtree_recursive($rcat,$lft,$rgt);
			$rcat->lft=1;
			$rcat->rgt=$rgt;
			}
		$db=BFactory::getDBO();
		if(empty($db)){
			return false;
			}
		BLog::addtolog('[Items] Updating nested set...');
		foreach($catslist as $ct){
			$qr='UPDATE `'.$this->tablename.'` set `'.$this->leftkey.'`='.$ct->lft.', `'.$this->rightkey.'`='.$ct->rgt.', `'.$this->levelkey.'`='.$ct->level.' WHERE `'.$this->primarykey.'`='.$ct->id;
			$q=$db->query($qr);
			if(empty($q)){
				return false;
				}
			}
		//Invalidate all cache.
		$bcache=BFactory::getCache();
		if($bcache){
			$bcache->invalidate();
			}
		return true;
		}
	/**
	 *
	 */
	public function getsimpletree_filteritem(&$itm){
		return true;
		}
	/**
	 * Get simple tree as list.
	 */
	public function getsimpletree($fields=array(),$transfields=array(),$lang='',$wh=array()){
		$lang=$this->detectlang($lang);
		//
		$cachekey=$this->tablename.':simpletree:'.$lang;
		if(!empty($wh)){
			$cachekey.=':wh('.implode(';',$wh).')';
		}
		//Try to get simple tree from internal cache...
		static $cache_simpletree=array();
		if(isset($cache_simpletree[$cachekey])){
			return $cache_simpletree[$cachekey];
			}
		//
		$bcache=BFactory::getCache();
		if($bcache){
			$res=$bcache->get($cachekey);
			if(($res!==false)&&($res!==NULL)){
				$this->cache_simpletree[$lang]=$res;
				return $res;
				}
			}
		//Load simle cities names.
		if(!$db=BFactory::getDBO()){return NULL;}
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
		$qr.=' ORDER BY `'.$this->leftkey.'`;';
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
			$this->getsimpletree_filteritem($xval);
			$res[$id]=$xval;
			}
		$this->cache_simpletree[$cachekey]=$res;
		if($bcache){
			$bcache->set($cachekey,$res,3600);//1 hour
			}
		return $res;
		}
	/**
	 * Get recursive tree
	 */
	public function getsimpletree_recursive($fields=array(),$transfields=array(),$lang='',$wh=array()){
		$list=$this->getsimpletree($fields,$transfields,$lang,$wh);
		$tree=array();
		foreach($list as $li){
			$id=(int)$li->{$this->primarykey};
		        $ti=$li;
			//TODO: finish children
			$ti->children=array();
			$tree[]=$ti;
			}
		return $tree;
		}
	/**
	 * @param $ids
	 * @return bool
	 */
	public function items_delete($ids){
		parent::items_delete($ids);
		$this->rebuildtree();
		return true;
		}
	}
