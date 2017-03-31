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
		//First Element
		$itemOne = new TestRTreeItem();
		$itemOne->group = 1;
		$itemOne->name='Element 1.1';
		$itemOne->saveToDB();
		$this->assertFalse(empty($itemOne->id),'ID of 1st element is empty!');
		//Second Element
		$itemTwo = new TestRTreeItem();
		$itemTwo->group = 1;
		$itemTwo->name='Element 2.1';
		$itemTwo->saveToDB();
		$this->assertFalse(empty($itemTwo->id),'ID of 2nd element is empty!');

		}
	}

