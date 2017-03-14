<?php
/**
 * Sets of functions and classes to work with tree item.
 *
 * @author Andrii Biriev
 */
bimport('items.item');

abstract class BItemsItemTree extends BItemsItem{
	protected $parentkeyname='parent';
	protected $leftkeyname='lft';
	protected $rightkeyname='rgt';
	protected $levelkeyname='level';
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
		$this->{$this->parentkeyname}=(int)$obj[$this->parentkeyname];
		$this->{$this->leftkeyname}=(int)$obj[$this->leftkeyname];
		$this->{$this->rightkeyname}=(int)$obj[$this->rightkeyname];
		$this->{$this->levelkeyname}=(int)$obj[$this->levelkeyname];
		return true;
		}
	/**
	 *
	 */
	public function getparentchain(){
		$collname=$this->collectionname;
		$bitems=$collname::GetInstance();
		//$fchain=$bitems->items_filter(array('parentchain'=>$this->id));
		$fchain=$bitems->items_filter(array('parentchain_lft'=>$this->{$this->leftkeyname},'parentchain_rgt'=>$this->{$this->rightkeyname},'cacheenabled'=>true));
		return $fchain;
		}
	/**
	 * Get 
	 */
	public function getparentchain_ids(){
		$collname=$this->collectionname;
		$bitems=$collname::GetInstance();
		$chain=$bitems->items_filter_ids(array('parentchain_lft'=>$this->{$this->leftkeyname},'parentchain_rgt'=>$this->{$this->rightkeyname},'cacheenabled'=>true));
		return $chain;
		}
	/**
	 *
	 */
	public function getparent(){
		$parentid=$this->{$this->parentkeyname};
		if(empty($parentid)){
			return NULL;
			}
		$collname=$this->collectionname;
		$bitems=$collname::GetInstance();
		$fparent=$bitems->item_get($parentid);
		return $fparent;
		}
	/**
	 * Get children items by alias.
	 */
	public function children($lang='',$alias=''){
		$collname=$this->collectionname;
		$bitems=$collname::GetInstance();
		$children=$bitems->items_filter(array('parent'=>$this->id));
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
			$collectionname=$this->collectionname;
			$collection=$collectionname::getInstance();
			$parent=$collection->item_get(1);
			$this->{$this->parentkeyname}=1;
			}
		$qr_fields[]=$this->parentkeyname;
		$qr_values[]=$this->{$this->parentkeyname};
		$qr_fields[]=$this->leftkeyname;
		$qr_values[]=$this->{$this->leftkeyname};
		$qr_fields[]=$this->rightkeyname;
		$qr_values[]=$this->{$this->rightkeyname};
		$qr_fields[]=$this->levelkeyname;
		$qr_values[]=$this->{$this->levelkeyname};
		return true;
		}
	/**
	 *
	 * @return bool
	 */
	public function dbinsert(){
		BLog::addtolog('[Items.ItemTree]: Inserting data...');
		if(!$db=BFactory::getDBO()){
			return false;
			}
		//
		$parent=$this->getparent();
		if(empty($parent)){
			$collectionname=$this->collectionname;
			$collection=$collectionname::getInstance();
			$parent=$collection->item_get(1);
			$this->{$this->parentkeyname}=1;
			}
		if(empty($parent)){
			return false;
			}
		$db->start_transaction();
		$this->{$this->leftkeyname}=$parent->{$this->rightkeyname};
		$this->{$this->rightkeyname}=$parent->{$this->rightkeyname}+1;
		$this->{$this->levelkeyname}=$parent->{$this->levelkeyname}+1;
		$qr='UPDATE `'.$this->tablename.'` '.
			'SET `'.$this->rightkeyname.'`=`'.$this->rightkeyname.'`+2, '.
			'`'.$this->leftkeyname.'` = IF(`'.$this->leftkeyname.'` > '.$parent->{$this->rightkeyname}.', `'.$this->leftkeyname.'`+2, `'.$this->leftkeyname.'`)'.
			' WHERE (`'.$this->rightkeyname.'`>='.$parent->{$this->rightkeyname}.')';
		$q=$db->Query($qr);
		if(empty($q)){
			$db->rollback();
			return false;
			}
		//Forming query...
		$this->modified=new DateTime();
		if(empty($this->created)){
			$this->created=new DateTime();
			}
		$qr=$this->dbinsertquery();


		//Running query...
		$q=$db->query($qr);
		if(empty($q)){
			$db->rollback();
			return false;
			}
		$this->{$this->primarykey}=$db->insert_id();
		$db->commit();
		//Updating cache...
		$this->updatecache();
		//Return result
		return true;
	}

	}