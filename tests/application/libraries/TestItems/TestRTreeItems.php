<?php
namespace Application\TestItems;

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
class TestRTreeItems extends \Brilliant\Items\BItemsRTree{
	use \Brilliant\BSingleton;
	protected $tableName='rtree_items';
	protected $itemClassName='\Application\TestItems\TestRTreeItem';

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
		$db = \Brilliant\BFactory::getDBO();
		//Select items only with some group.
		if (isset($params['name'])) {
			$wh[] = '(`' . $this->groupKeyName . '`=' . $db->escapeString($params['name']) . ')';
			}
		return true;
		}

	}
