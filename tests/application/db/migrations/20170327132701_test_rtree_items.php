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
			->addColumn('parent', 'integer')
			->addColumn('name', 'string')
			->addColumn('created', 'datetime')
			->addColumn('modified', 'datetime')
			->create();
		}
	}
