<?php
//namespace Application\TestItems;

/**
 * Basic class to control Items Item
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
class TestRTreeItem extends \Brilliant\Items\BItemsItemRTree{
	protected $collectionName='\Application\TestItems\TestRTreeItems';
	protected $tableName='rtree_items';
	/**
	 * Constructor - init fields...
	 */
	function __construct() {
		parent::__construct();
		$this->fieldAddRaw('name','string');
		$this->fieldAddRaw('created','dt',array('readonly'=>true));
		$this->fieldAddRaw('modified','dt',array('readonly'=>true));
		}
	}
