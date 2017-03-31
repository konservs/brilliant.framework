<?php
/**
 * Sets of functions and classes to work with tree item.
 *
 * @author Andrii Biriev
 *
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
namespace Brilliant\Items;

use Brilliant\Log\BLog;

abstract class BItemsItemTree extends BItemsItem{
	protected $parentKeyName='parent';
	protected $leftKeyName='lft';
	protected $rightKeyName='rgt';
	protected $levelKeyName='level';
	/**
	 * Constructor - init fields...
	 */
	function __construct() {
		parent::__construct();
		}

	/**
	 * @param $obj
	 * @return bool|void
	 */
	public function load($obj){
		parent::load($obj);
		$this->{$this->parentKeyName}=(int)$obj[$this->parentKeyName];
		$this->{$this->leftKeyName}=(int)$obj[$this->leftKeyName];
		$this->{$this->rightKeyName}=(int)$obj[$this->rightKeyName];
		$this->{$this->levelKeyName}=(int)$obj[$this->levelKeyName];
		return true;
		}
	/**
	 *
	 */
	public function getparentchain(){
		$collname=$this->collectionName;
		$bitems=$collname::GetInstance();
		$fchain=$bitems->itemsFilter(array('parentchain_lft'=>$this->{$this->leftKeyName},'parentchain_rgt'=>$this->{$this->rightKeyName},'cacheenabled'=>true));
		return $fchain;
		}
	/**
	 * Get 
	 */
	public function getparentchain_ids(){
		$collname=$this->collectionName;
		$bitems=$collname::GetInstance();
		$chain=$bitems->itemsFilterIds(array('parentchain_lft'=>$this->{$this->leftKeyName},'parentchain_rgt'=>$this->{$this->rightKeyName},'cacheenabled'=>true));
		return $chain;
		}
	/**
	 *
	 */
	public function getparent(){
		$parentid=$this->{$this->parentKeyName};
		if(empty($parentid)){
			return NULL;
			}
		$collname=$this->collectionName;
		$bitems=$collname::GetInstance();
		$fparent=$bitems->itemGet($parentid);
		return $fparent;
		}
	/**
	 * Get children items by alias.
	 */
	public function children($lang='',$alias=''){
		$collname=$this->collectionName;
		$bitems=$collname::GetInstance();
		$children=$bitems->itemsFilter(array('parent'=>$this->id));
		if(empty($alias)){
			return $children;
			}
		foreach($children as $ch){
			$chalias=$ch->getalias($lang);
			if($chalias==$alias){
				return $ch;
				}
			}
		return NULL;
		}

	/**
	 * Get fields values
	 * @param $qr_fields
	 * @param $qr_values
	 * @return bool
	 */
	protected function getfieldsvalues(&$qr_fields,&$qr_values){
		$qr_fields=array();
		$qr_values=array();
		parent::getfieldsvalues($qr_fields,$qr_values);
		$parent=$this->getparent();
		if(empty($parent)){
			$collectionName=$this->collectionName;
			$collection=$collectionName::getInstance();
			$parent=$collection->itemGet(1);
			$this->{$this->parentKeyName}=1;
			}
		$qr_fields[]=$this->parentKeyName;
		$qr_values[]=$this->{$this->parentKeyName};
		$qr_fields[]=$this->leftKeyName;
		$qr_values[]=$this->{$this->leftKeyName};
		$qr_fields[]=$this->rightKeyName;
		$qr_values[]=$this->{$this->rightKeyName};
		$qr_fields[]=$this->levelKeyName;
		$qr_values[]=$this->{$this->levelKeyName};
		return true;
		}
	/**
	 *
	 * @return bool
	 */
	public function dbInsert(){
		BLog::addToLog('[Items.ItemTree]: Inserting data...');
		if(!$db=\Brilliant\BFactory::getDBO()){
			return false;
			}
		//
		$parent=$this->getparent();
		if(empty($parent)){
			$collectionName=$this->collectionName;
			$collection=$collectionName::getInstance();
			$parent=$collection->itemGet(1);
			$this->{$this->parentKeyName}=1;
			}
		if(empty($parent)){
			return false;
			}
		$db->start_transaction();
		$this->{$this->leftKeyName}=$parent->{$this->rightKeyName};
		$this->{$this->rightKeyName}=$parent->{$this->rightKeyName}+1;
		$this->{$this->levelKeyName}=$parent->{$this->levelKeyName}+1;
		$qr='UPDATE `'.$this->tableName.'` '.
			'SET `'.$this->rightKeyName.'`=`'.$this->rightKeyName.'`+2, '.
			'`'.$this->leftKeyName.'` = IF(`'.$this->leftKeyName.'` > '.$parent->{$this->rightKeyName}.', `'.$this->leftKeyName.'`+2, `'.$this->leftKeyName.'`)'.
			' WHERE (`'.$this->rightKeyName.'`>='.$parent->{$this->rightKeyName}.')';
		$q=$db->Query($qr);
		if(empty($q)){
			$db->rollback();
			return false;
			}
		//Forming query...
		$this->modified=new \Brilliant\BDateTime();
		if(empty($this->created)){
			$this->created=new \Brilliant\BDateTime();
			}
		$qr=$this->dbInsertQuery();


		//Running query...
		$q=$db->query($qr);
		if(empty($q)){
			$db->rollback();
			return false;
			}
		$this->{$this->primarykey}=$db->insertId();
		$db->commit();
		//Updating cache...
		$this->updateCache();
		//Return result
		return true;
		}
	}
