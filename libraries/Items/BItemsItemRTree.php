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

class BItemsItemRTree extends BItemsItem {
	protected $parentKeyName = 'parent';
	protected $leftKeyName = 'lft';
	protected $rightKeyName = 'rgt';
	protected $levelKeyName = 'level';
	protected $groupKeyName = 'group';
	public $isRoot = false;
	/**
	 *
	 */
	public function getGroupId(){
		return $this->{$this->groupKeyName};
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
		$this->{$this->groupKeyName} = (int)$obj[$this->groupKeyName];
		return true;
	}

	/**
	 * Get collection of such elements
	 *
	 * @return BItemsRTree
	 */
	public function getCollection() {
		$collectionName = $this->collectionName;
		if(empty($collectionName)){
			return NULL;
			}
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
		$qr_fields[] = '`' . $this->groupKeyName . '`';
		$qr_values[] = $this->{$this->groupKeyName};
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
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Try to get parent / root element.');
			}
		$parent = $this->getParent();
		if(!empty($parent)){
			return $parent;
			}
		//return $fparent;
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Parent is empty. Trying to get root from collection.');
			}
		$collection = $this->getCollection();
		if(empty($collection)){
			return NULL;
			}
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Parent is empty. Collection is not empty. Calling getGroupRoot.');
			}
		$groupRoot = $collection->getGroupRoot($this->getGroupId());
		if(DEBUG_LOG_BITEMS){
			BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Got getGroupRoot!');
			}
		return $groupRoot;
		}


	/**
	 * Update cache. Need to clear parent also (its right key)
	 *
	 * @return bool
	 */
	public function updateCache() {
		parent::updateCache();
		$bcache = \Brilliant\BFactory::getCache();
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
		BLog::addToLog('[Items.ItemTree]: Inserting data...');
		if (!$db = \Brilliant\BFactory::getDBO()) {
			return false;
			}
		$closeTransaction=false;

		if(!$this->isRoot){
			$db->start_transaction();
			$closeTransaction = true;
			//---------------------------------------------------
			// Try to get parent / root element, create root
			// if we does not have root element.
			//---------------------------------------------------
			BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Try to get parent / root element.');
			$parent = $this->getParentOrRoot();
			if(DEBUG_LOG_BITEMS){
				BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Got parent / root ('.var_export($parent,true).')!');
				}
			if (empty($parent)) {
				$className = get_class($this);
				BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Parent is empty. Need to insert parent element ('.$className.')');
				$parent = new $className();
				$parent->{$this->groupKeyName} = $this->{$this->groupKeyName};
				$parent->{$this->leftKeyName} = 1;
				$parent->{$this->rightKeyName} = 4;
				$parent->{$this->levelKeyName} = 1;
				$parent->isRoot = true;
				$r = $parent->saveToDB();
				if(empty($r)){
					return false;
					}
				//
				$this->{$this->groupKeyName} = $parent->{$this->groupKeyName};
				$this->{$this->leftKeyName} = 2;
				$this->{$this->rightKeyName} = 3;
				$this->{$this->levelKeyName} = 2;
				} else {
				BLog::addToLog('[Items.ItemTree.'.$this->tableName.']: Parent is not empty.');
				$this->{$this->parentKeyName} = $parent->{$this->groupKeyName};

				$this->{$this->groupKeyName} = $parent->{$this->$primaryKeyName};
				$this->{$this->leftKeyName} = (int)$parent->{$this->rightKeyName};
				$this->{$this->rightKeyName} = (int)$parent->{$this->rightKeyName} + 1;
				$this->{$this->levelKeyName} = (int)$parent->{$this->levelKeyName} + 1;
				$qr = 'UPDATE `' . $this->tableName . '` SET ';
				$qr .= '`' . $this->rightKeyName . '`=`' . $this->rightKeyName . '`+2, ';
				$qr .= '`' . $this->leftKeyName . '` = IF(`' . $this->leftKeyName . '` > ' . $parent->{$this->rightKeyName} . ', `' . $this->leftKeyName . '`+2, `' . $this->leftKeyName . '`)';
				$qr .= ' WHERE ((`' . $this->groupKeyName . '` = ' . $this->{$this->groupKeyName} . ') AND (`' . $this->rightKeyName . '`>=' . $parent->{$this->rightKeyName} . '))';
				$q = $db->query($qr);
				if (empty($q)) {
					$db->rollback();
					return false;
					}
				//Because some elements in our internal cache was changed - need to flush it.
				$collection=$this->getCollection();
				$collection->flushInternalCache();
				}
			}
		//---------------------------------------------------
		//Forming query...
		//---------------------------------------------------
		$this->modified = new \DateTime();
		if(empty($this->created)){
			$this->created=new \DateTime();
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
		$this->{$this->primaryKeyName} = $db->insertId();
		if ($closeTransaction) {
			$db->commit();
			}
		//Updating cache...
		$this->updateCache();
		//Return result
		return true;
	}
}
