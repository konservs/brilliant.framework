<?php
namespace \Application\TestItems;

/**
 * Basic class to control companies
 *
 * @method \Application\TestItems\TestRTreeItem itemGet(integer $id)
 * @method \Application\TestItems\TestRTreeItem[] itemsGet(integer[] $ids)
 * @method \Application\TestItems\TestRTreeItem[] itemsFilter($params)
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
class TestRTreeItems extends \Brilliant\Items\ItemsRTree{
	use \Brilliant\BSingleton;
	protected $tableName='rtree_items';
	protected $itemClassName='\Application\TestItems\TestRTreeItem';

	}
