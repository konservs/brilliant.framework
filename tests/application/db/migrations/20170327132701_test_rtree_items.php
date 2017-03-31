<?php

use Phinx\Migration\AbstractMigration;

class TestRtreeItems extends AbstractMigration{
	/**
	 *
	 */
	public function change(){
		$table = $this->table('rtree_items');
		$table->addColumn('lft', 'integer')
			->addColumn('rgt', 'integer')
			->addColumn('level', 'integer')
			->addColumn('parent', 'integer', ['null'=>true])
			->addColumn('group', 'integer')
			->addColumn('name', 'string')
			->addColumn('created', 'datetime')
			->addColumn('modified', 'datetime')
			->addIndex(array('lft'))
			->create();
		}
	}
