<?php
use \PHPUnit\Framework\TestCase;
use \Application\TestItems\TestRTreeItems;
use \Application\TestItems\TestRTreeItem;

class ufItemsRTreeTest extends TestCase{
	/**
	 *
	 */
	public function testRTree1(){
		$bItemsTree = new TestRTreeItems();
		$bItemsTree->truncateAll();

		$itemOne = new TestRTreeItem();
		$itemOne->group = 1;
		$itemOne->name='Element';
		$itemOne->saveToDB();

		$this->assertFalse(empty($itemOne->id),'ID of newly created element is empty!');


		//$itemOne->
		}
	}

