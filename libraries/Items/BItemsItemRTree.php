<?php
/**
 * Sets of functions and classes to work with grouped tree item.
 *
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant\Items;

use Brilliant\Log\BLog;

abstract class BItemsItemRTree extends BItemsItem {
	protected $parentKeyName = 'parent';
	protected $leftKeyName = 'lft';
	protected $rightKeyName = 'rgt';
	protected $levelKeyName = 'level';
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
		$this->{$this->parentKeyName} = (int)$obj[$this->parentKeyName];
		$this->{$this->leftKeyName} = (int)$obj[$this->leftKeyName];
		$this->{$this->rightKeyName} = (int)$obj[$this->rightKeyName];
		$this->{$this->levelKeyName} = (int)$obj[$this->levelKeyName];
		$this->{$this->groupName} = (int)$obj[$this->groupName];
		return true;
	}

	/**
	 * Get collection of such elements
	 *
	 * @return BItemsRTree
	 */
	public function getCollection() {
		$collectionName = $this->collectionName;
		$collection = $collectionName::getInstance();
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

		if (empty($this->{$this->parentKeyName})) {
			$qr_fields[] = '`' . $this->parentKeyName . '`';
			$qr_values[] = 'NULL';
		} else {
			$qr_fields[] = '`' . $this->parentKeyName . '`';
			$qr_values[] = $this->{$this->parentKeyName};
		}

		$qr_fields[] = '`' . $this->leftKeyName . '`';
		$qr_values[] = $this->{$this->leftKeyName};
		$qr_fields[] = '`' . $this->rightKeyName . '`';
		$qr_values[] = $this->{$this->rightKeyName};
		$qr_fields[] = '`' . $this->levelKeyName . '`';
		$qr_values[] = $this->{$this->levelKeyName};
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
		$parentid = $this->{$this->parentKeyName};
		if (empty($parentid)) {
			return NULL;
		}
		$collname = $this->collectionName;
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
	public function updateCache() {
		parent::updateCache();
		$bcache = BFactory::getCache();
		if (empty($bcache)) {
			return false;
			}
		$parentid = $this->{$this->parentKeyName};
		if (empty($parentid)) {
			return true;
			}
		$cachekey = $this->tableName . ':itemid:' . $parentid;
		$bcache->delete($cachekey);
		//Update some lists
		$params = array();
		$params['group'] = $this->getGroupId();
		$params['parentisnull'] = true;
		$collname = $this->collectionName;
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
	public function dbInsert() {
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
			$this->{$this->leftKeyName} = $parent->{$this->rightKeyName};
			$this->{$this->rightKeyName} = $parent->{$this->rightKeyName} + 1;
			$this->{$this->levelKeyName} = $parent->{$this->levelKeyName} + 1;
			$qr = 'UPDATE `' . $this->tableName . '` SET ';
			$qr .= '`' . $this->rightKeyName . '`=`' . $this->rightKeyName . '`+2, ';
			$qr .= '`' . $this->leftKeyName . '` = IF(`' . $this->leftKeyName . '` > ' . $parent->{$this->rightKeyName} . ', `' . $this->leftKeyName . '`+2, `' . $this->leftKeyName . '`)';
			$qr .= ' WHERE ((`' . $this->groupName . '` = ' . $this->{$this->groupName} . ') AND (`' . $this->rightKeyName . '`>=' . $parent->{$this->rightKeyName} . '))';

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
		$qr = $this->dbInsertQuery();
		//Running query...
		$q = $db->query($qr);
		if (empty($q)) {
			if ($closeTransaction) {
				$db->rollback();
			}
			return false;
		}
		$this->{$this->primarykey} = $db->insertId();
		if ($closeTransaction) {
			$db->commit();
		}
		//Updating cache...
		$this->updateCache();
		//Return result
		return true;
	}
}