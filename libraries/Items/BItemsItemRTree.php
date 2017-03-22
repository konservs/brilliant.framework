<?php
/**
 * Sets of functions and classes to work with grouped tree item.
 *
 * @author Andrii Biriev
 */
bimport('items.item_tree');

abstract class BItemsItemRTree extends BItemsItem {
	protected $parentkeyname = 'parent';
	protected $leftkeyname = 'lft';
	protected $rightkeyname = 'rgt';
	protected $levelkeyname = 'level';
	protected $groupName = 'group';

	/**
	 *
	 */
	public function getGroupId(){
		return $this->{$this->groupName};
		}


	/**
	 * Load data
	 *
	 * @param $obj
	 * @return bool|void
	 */
	public function load($obj) {
		parent::load($obj);
		$this->{$this->parentkeyname} = (int)$obj[$this->parentkeyname];
		$this->{$this->leftkeyname} = (int)$obj[$this->leftkeyname];
		$this->{$this->rightkeyname} = (int)$obj[$this->rightkeyname];
		$this->{$this->levelkeyname} = (int)$obj[$this->levelkeyname];
		$this->{$this->groupName} = (int)$obj[$this->groupName];
		return true;
	}

	/**
	 * Get collection of such elements
	 *
	 * @return BItemsRTree
	 */
	public function getCollection() {
		$collectionname = $this->collectionname;
		$collection = $collectionname::getInstance();
		return $collection;
	}

	/**
	 * Get values of fields.
	 *
	 * @param $qr_fields
	 * @param $qr_values
	 * @return bool
	 */
	protected function getfieldsvalues(&$qr_fields, &$qr_values) {
		$qr_fields = array();
		$qr_values = array();
		parent::getfieldsvalues($qr_fields, $qr_values);

		if (empty($this->{$this->parentkeyname})) {
			$qr_fields[] = '`' . $this->parentkeyname . '`';
			$qr_values[] = 'NULL';
		} else {
			$qr_fields[] = '`' . $this->parentkeyname . '`';
			$qr_values[] = $this->{$this->parentkeyname};
		}

		$qr_fields[] = '`' . $this->leftkeyname . '`';
		$qr_values[] = $this->{$this->leftkeyname};
		$qr_fields[] = '`' . $this->rightkeyname . '`';
		$qr_values[] = $this->{$this->rightkeyname};
		$qr_fields[] = '`' . $this->levelkeyname . '`';
		$qr_values[] = $this->{$this->levelkeyname};
		$qr_fields[] = '`' . $this->groupName . '`';
		$qr_values[] = $this->{$this->groupName};
		return true;
	}

	/**
	 * Get paremt id
	 *
	 * @return BItemsRTree
	 */
	public function getParent() {
		$parentid = $this->{$this->parentkeyname};
		if (empty($parentid)) {
			return NULL;
		}
		$collname = $this->collectionname;
		$bitems = $collname::getInstance();
		$fparent = $bitems->itemGet($parentid);
		return $fparent;
	}

	/**
	 * Get paremt id
	 *
	 * @return BItemsRTree
	 */
	public function getParentOrRoot() {
		$parent = $this->getParent();
		if(!empty($parent)){
			return $parent;
			}
		//return $fparent;
		$collection = $this->getCollection();
		$groupRoot = $collection->getGroupRoot($this->getGroupId());
		return $groupRoot;
		}


	/**
	 * Update cache. Need to clear parent also (its right key)
	 *
	 * @return bool
	 */
	public function updatecache() {
		parent::updatecache();
		$bcache = BFactory::getCache();
		if (empty($bcache)) {
			return false;
			}
		$parentid = $this->{$this->parentkeyname};
		if (empty($parentid)) {
			return true;
			}
		$cachekey = $this->tableName . ':itemid:' . $parentid;
		$bcache->delete($cachekey);
		//Update some lists
		$params = array();
		$params['group'] = $this->getGroupId();
		$params['parentisnull'] = true;
		$collname = $this->collectionname;
		$bitems = $collname::getInstance();
		$cachekey = $bitems->itemsFilterHash($params);
		$bcache->delete($cachekey);
		//All is ok
		return true;
	}

	/**
	 * Insert element into database...
	 *
	 * @return bool
	 */
	public function dbinsert() {
		BLog::addtolog('[Items.ItemTree]: Inserting data...');
		if (!$db = BFactory::getDBO()) {
			return false;
		}
		//
		$parent = $this->getParentOrRoot();
		$closeTransaction = false;
		if (!empty($parent)) {
			$closeTransaction = true;
			$db->start_transaction();
			$this->{$this->groupName} = $parent->{$this->groupName};
			$this->{$this->leftkeyname} = $parent->{$this->rightkeyname};
			$this->{$this->rightkeyname} = $parent->{$this->rightkeyname} + 1;
			$this->{$this->levelkeyname} = $parent->{$this->levelkeyname} + 1;
			$qr = 'UPDATE `' . $this->tableName . '` SET ';
			$qr .= '`' . $this->rightkeyname . '`=`' . $this->rightkeyname . '`+2, ';
			$qr .= '`' . $this->leftkeyname . '` = IF(`' . $this->leftkeyname . '` > ' . $parent->{$this->rightkeyname} . ', `' . $this->leftkeyname . '`+2, `' . $this->leftkeyname . '`)';
			$qr .= ' WHERE ((`' . $this->groupName . '` = ' . $this->{$this->groupName} . ') AND (`' . $this->rightkeyname . '`>=' . $parent->{$this->rightkeyname} . '))';

			$q = $db->query($qr);
			if (empty($q)) {
				$db->rollback();
				return false;
				}
			//Because some elements in our internal cache was changed - need to flush it.
			$collection=$this->getCollection();
			$collection->flushInternalCache();
			}
		//Forming query...
		$this->modified = new DateTime();
		if(empty($this->created)){
			$this->created=new DateTime();
			}
		$qr = $this->dbinsertquery();
		//Running query...
		$q = $db->query($qr);
		if (empty($q)) {
			if ($closeTransaction) {
				$db->rollback();
			}
			return false;
		}
		$this->{$this->primarykey} = $db->insert_id();
		if ($closeTransaction) {
			$db->commit();
		}
		//Updating cache...
		$this->updatecache();
		//Return result
		return true;
	}
}