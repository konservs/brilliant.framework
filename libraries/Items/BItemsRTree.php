<?php
/**
 * Class to work with Tree with Groups
 *
 * @author Andrii Biriev
 */
namespace Brilliant\Items;

use Brilliant\Log\BLog;

abstract class BItemsRTree extends BItems {
	/**
	 * Group key
	 *
	 * @var string
	 */
	protected $groupKeyName = 'group';
	protected $leftKeyName = 'lft';
	protected $rightKeyName = 'rgt';
	protected $levelKeyName = 'level';
	protected $parentKeyName = 'parent';

	/**
	 * @param $groupId int
	 * @return BItemsItemRTree|null
	 */
	public function getGroupRoot($groupId) {
		BLog::addToLog('[Items.ItemsRTree.'.$this->tableName.']: getGroupRoot('.var_export($groupId,true).').');
		$params = array();
		$params['group'] = $groupId;
		$params['parentisnull'] = true;
		$list = $this->itemsFilter($params);
		if(empty($list)){
			return NULL;
			}
		$item = reset($list);
		return $item;
	}

	/**
	 * Filters items and return array of IDs
	 *
	 * @param $params
	 * @return array|null
	 */
	public function itemsFilterIds($params) {
		if (empty($params['orderby'])) {
			$params['orderby'] = $this->leftKeyName;
		}
		return parent::itemsFilterIds($params);
	}

	/**
	 * Get filter for SQL query.
	 *
	 * @param $params
	 * @param $wh
	 * @param $jn
	 * @return bool
	 */
	public function itemsFilterSql($params, &$wh, &$jn) {
		//Call parent method.
		parent::itemsFilterSql($params, $wh, $jn);

		//Select items only with some group.
		if (isset($params['group'])) {
			$wh[] = '(`' . $this->groupKeyName . '`=' . (int)$params['group'] . ')';
		}
		//Select categories only with some level.
		if (isset($params['level'])) {
			$wh[] = '(`' . $this->levelKeyName . '`=' . (int)$params['level'] . ')';
		}
		//Select categories only with parentid=$params['parent'].
		if (isset($params['parent'])) {
			$wh[] = '(`' . $this->parentKeyName . '`=' . (int)$params['parent'] . ')';
		}
		//Select categories only with parentid=NULL
		if (!empty($params['parentisnull'])) {
			$wh[] = '(`' . $this->parentKeyName . '` is NULL)';
		}
		//Entire parents tree. 
		if (isset($params['parenttree'])) {
			$itemid = (int)$params['parenttree'];
			$item = $this->itemGet($itemid);
			if (empty($item)) {
				return false;
			}
			$wh[] = '(`' . $this->leftKeyName . '`>=' . $item->lft . ')';
			$wh[] = '(`' . $this->rightKeyName . '`<=' . $item->rgt . ')';
		}
		//Entire parents tree, second version. 
		if ((isset($params['parenttree_lft'])) && (isset($params['parenttree_rgt']))) {
			$lft = $params['parenttree_lft'];
			$rgt = $params['parenttree_rgt'];
			if (($lft < 1) || ($rgt < 1) || ($lft >= $rgt)) {
				return false;
			}
			$wh[] = '(`' . $this->leftKeyName . '`>=' . $lft . ')';
			$wh[] = '(`' . $this->rightKeyName . '`<=' . $rgt . ')';
		}
		//Entire parents chain. 
		if (isset($params['parentchain'])) {
			$itemid = (int)$params['parentchain'];
			$item = $this->itemGet($itemid);
			if (empty($item)) {
				return false;
			}
			$wh[] = '(`' . $this->leftKeyName . '`<=' . $item->lft . ')';
			$wh[] = '(`' . $this->rightKeyName . '`>=' . $item->rgt . ')';
		}
		//Entire parents chain. 
		if ((isset($params['parentchain_lft'])) && (isset($params['parentchain_rgt']))) {
			$wh[] = '(`' . $this->leftKeyName . '`<=' . (int)$params['parentchain_lft'] . ')';
			$wh[] = '(`' . $this->rightKeyName . '`>=' . (int)$params['parentchain_rgt'] . ')';
		}
		return true;
	}

	/**
	 * Get items hash for caching
	 *
	 * @param $params
	 * @return string
	 */
	public function itemsFilterHash($params) {
		$itemshash = parent::itemsFilterHash($params);

		//Select items only with some group
		if (isset($params['group'])) {
			$itemshash .= ':group=' . $params['group'];
		}
		//Select categories only with some level
		if (isset($params['level'])) {
			$itemshash .= ':level=' . $params['level'];
		}
		//Select categories only with parentid=$params['parent'].
		if (isset($params['parent'])) {
			$itemshash .= ':parent=' . $params['parent'];
		}
		//Select categories only with parentid=$params['parent'].
		if (isset($params['parentisnull'])) {
			$itemshash .= ':parentisnull=' . empty($params['parentisnull'])?'N':'Y';
		}
		//Entire parents tree. 
		if (isset($params['parenttree'])) {
			$itemid = (int)$params['parenttree'];
			$item = $this->itemGet($itemid);
			if (empty($item)) {
				return false;
			}
			$itemshash .= ':parenttree-' . $item->lft . '-' . $item->rgt;
		}
		//Entire parents tree, second version. 
		if ((isset($params['parenttree_lft'])) && (isset($params['parenttree_rgt']))) {
			$lft = $params['parenttree_lft'];
			$rgt = $params['parenttree_rgt'];
			if (($lft < 1) || ($rgt < 1) || ($lft >= $rgt)) {
				return false;
			}
			$itemshash .= ':parenttree-' . $lft . '-' . $rgt;
		}
		//Entire parents chain. 
		if (isset($params['parentchain'])) {
			$itemid = (int)$params['parentchain'];
			$item = $this->itemGet($itemid);
			if (empty($item)) {
				return false;
			}
			$itemshash .= ':parentchain-' . $item->lft . '-' . $item->rgt;
		}
		//Entire parents chain, second version. 
		if ((isset($params['parentchain_lft'])) && (isset($params['parentchain_rgt']))) {
			$lft = $params['parentchain_lft'];
			$rgt = $params['parentchain_rgt'];
			if (($lft < 1) || ($rgt < 1) || ($lft >= $rgt)) {
				return false;
			}
			$itemshash .= ':parentchain-' . $lft . '-' . $rgt;
		}
		return $itemshash;
	}

	/**
	 * Recursive.
	 */
	protected function rebuildtree_recursive($cat, &$lft, &$rgt) {
		BLog::addToLog('[Items] Processing item [' . $cat->id . ']');
		$ch = $this->itemsFilter(array('parent' => $cat->id));
		BLog::addToLog('[Items] Fill children array and sort them by "ordering". count=' . count($ch));
		//
		$children = array();
		foreach ($ch as $c) {
			$children[] = $c;
		}
		//
		$n = count($children);
		for ($i = 0; $i < $n; $i++) {
			$m = $i;
			for ($j = $i + 1; $j < $n; $j++) {
				if ($children[$j]->ordering < $children[$m]->ordering) {
					$m = $j;
				}
			}
			if ($m != $i) {
				$t = $children[$i];
				$children[$i] = $children[$m];
				$children[$m] = $t;
			}
		}
		//
		if (empty($children)) {
			return true;
		}
		foreach ($children as $ccat) {
			$ccat->level = $cat->level + 1;

			$lftrec = $lft + 1;
			$rgtrec = $rgt + 1;
			$this->rebuildtree_recursive($ccat, $lftrec, $rgtrec);
			$ccat->lft = $lft + 1;
			$ccat->rgt = $rgtrec;

			$lft = $lftrec + 1;
			$rgt = $rgtrec + 1;
		}
		return true;
	}

	/**
	 * Refresh categories tree nested set.
	 */
	public function rebuildtree() {
		//Rebuild nested set - get cat
		BLog::addToLog('[Items] rebuilding nested sets...');


		$db = \Brilliant\BFactory::getDBO();
		if (empty($db)) {
			return false;
			}
		$r = $db->query('LOCK TABLES `'.$this->tableName.'` WRITE');
		if(!$r){
			BLog::addToLog('[Items] Could not lock table!',LL_ERROR);
			}
		//
		BLog::addToLog('[Items] Invalidating cache...');
		$bcache = \Brilliant\BFactory::getCache();
		if ($bcache) {
			$bcache->invalidate();
			}
		//
		BLog::addToLog('[Items] Get root cats...');
		//
		$catsCount = $this->itemsFilterCount(array());
		BLog::addToLog('[Items] Total categories count:' . $catsCount . '...');
		//
		$rootCatsCount = $this->itemsFilterCount(array('parentisnull'=>true));
		BLog::addToLog('[Items] Root categories count:' . $rootCatsCount. '...');
		//Go th
		$rootCatsOffset = 0;
		while($rootCatsOffset < $rootCatsCount) {
			$paramsRoot = array();
			$paramsRoot['parentisnull']=true;
			$paramsRoot['orderby']='created';
			$paramsRoot['orderdir']='asc';
			$paramsRoot['offset']=$rootCatsOffset;
			$paramsRoot['limit']=1;
			$rootcats = $this->itemsFilter($paramsRoot);
			if(count($rootcats)!=1){
				BLog::addToLog('[Items] Root Category error!',LL_ERROR);
				}
			$rcat = reset($rootcats);
			$groupId = $rcat->getGroupId();
			//
			BLog::addToLog('[Items] Processing group #' . $groupId . ', root = ' . $rcat->id . '...');
			//
			$paramsAll=array();
			$paramsAll['group']=$groupId;
			//$paramsAll['parenttree_lft']
			//$paramsAll['parenttree_rgt']
			$catslist = $this->itemsFilter($paramsAll);


			//Foreach by root categories...
			$rcat->level = 0;
			$lft = 1;
			$rgt = 2;
			$this->rebuildtree_recursive($rcat, $lft, $rgt);
			$rcat->lft = 1;
			$rcat->rgt = $rgt;
			BLog::addToLog('[Items] Updating nested set...');
			foreach ($catslist as $ct) {
				$qr = 'UPDATE `' . $this->tableName . '` set `' . $this->leftKeyName . '`=' . $ct->lft . ', `' . $this->rightKeyName . '`=' . $ct->rgt . ', `' . $this->levelKeyName . '`=' . $ct->level . ' WHERE `' . $this->primaryKeyName . '`=' . $ct->id;
				$q = $db->query($qr);
				if (empty($q)) {
					return false;
					}
				}
			$rootCatsOffset+=1;
			//Invalidate cache after each group...
			$bcache = \Brilliant\BFactory::getCache();
			if ($bcache) {
				$bcache->invalidate();
				}
			}
		return true;
		}
	/**
	 *
	 */
	public function getsimpletree_filteritem(&$itm) {
		return true;
	}

	/**
	 * Get simple tree as list.
	 *
	 * @param array $fields
	 * @param array $transfields
	 * @param string $lang
	 * @param array $wh
	 * @return array|null
	 */
	public function getsimpletree($fields = array(), $transfields = array(), $lang = '', $wh = array()) {
		$lang = $this->detectLanguage($lang);
		//
		$cachekey = $this->tableName . ':simpletree:' . $lang;
		if (!empty($wh)) {
			$cachekey .= ':wh(' . implode(';', $wh) . ')';
		}
		//Try to get simple tree from internal cache...
		static $cache_simpletree = array();
		if (isset($cache_simpletree[$cachekey])) {
			return $cache_simpletree[$cachekey];
		}
		//
		$bcache = \Brilliant\BFactory::getCache();
		if ($bcache) {
			$res = $bcache->get($cachekey);
			if (($res !== false) && ($res !== NULL)) {
				$this->cache_simpletree[$lang] = $res;
				return $res;
			}
		}
		//Load simle cities names.
		if (!$db = \Brilliant\BFactory::getDBO()) {
			return NULL;
		}
		$qr = 'SELECT `' . $this->primaryKeyName . '`';
		foreach ($fields as $fld) {
			$qr .= ', `' . $fld . '`';
		}
		foreach ($transfields as $fld) {
			$qr .= ', `' . $fld . '_' . $lang . '` as `' . $fld . '`';
		}
		$qr .= ' FROM `' . $this->tableName . '`';
		if (!empty($wh)) {
			$qr .= ' WHERE (' . implode(' AND ', $wh) . ')';
		}
		$qr .= ' ORDER BY `' . $this->leftKeyName . '`;';
		$q = $db->Query($qr);
		if (empty($q)) {
			return $res;
		}
		//
		$res = array();
		while ($l = $db->fetch($q)) {
			$id = (int)$l[$this->primaryKeyName];
			$val = array();
			$val[$this->primaryKeyName] = $id;
			foreach ($fields as $fld) {
				$val[$fld] = $l[$fld];
			}
			foreach ($transfields as $fld) {
				$val[$fld] = $l[$fld];
			}
			$xval = (object)$val;
			$this->getsimpletree_filteritem($xval);
			$res[$id] = $xval;
		}
		$this->cache_simpletree[$cachekey] = $res;
		if ($bcache) {
			$bcache->set($cachekey, $res, 3600);//1 hour
		}
		return $res;
	}

	/**
	 * Get recursive tree
	 */
	public function getsimpletree_recursive($fields = array(), $transfields = array(), $lang = '', $wh = array()) {
		$list = $this->getsimpletree($fields, $transfields, $lang, $wh);
		if (!is_array($list)) {
			return array();
		}
		$tree = array();
		foreach ($list as $li) {
			$id = (int)$li->{$this->primaryKeyName};
			$ti = $li;
			//TODO: finish children
			$ti->children = array();
			$tree[] = $ti;
		}
		return $tree;
	}

	/**
	 * @param $ids
	 * @return bool
	 */
	public function itemsDelete($ids) {
		parent::itemsDelete($ids);
		$this->rebuildtree();
		return true;
	}

}
