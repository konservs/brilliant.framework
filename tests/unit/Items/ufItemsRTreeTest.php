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

		//Third Element
		$itemThree = new TestRTreeItem();
		$itemThree->group = 1;
		$itemThree->name='Element 3.1';
		$itemThree->saveToDB();
		$this->assertFalse(empty($itemThree->id),'ID of 3nd element is empty!');
		//
		$this->assertTrue(($itemOne->lft == 2),'[1.1]->lft should be 2');
		$this->assertTrue(($itemOne->rgt == 3),'[1.1]->rgt should be 3');
		$this->assertTrue(($itemOne->level == 2),'[1.1]->level should be 2');
		//
		$this->assertTrue(($itemTwo->lft == 4),'[2.1]->lft should be 4');
		$this->assertTrue(($itemTwo->rgt == 5),'[2.1]->rgt should be 5');
		$this->assertTrue(($itemTwo->level == 2),'[2.1]->level should be 2');
		//
		$this->assertTrue(($itemThree->lft == 6),'[2.1]->lft should be 6');
		$this->assertTrue(($itemThree->rgt == 7),'[2.1]->rgt should be 7');
		$this->assertTrue(($itemThree->level == 2),'[2.1]->level should be 2');
		}
	/**
	 *
	 */
	public function testRTree2(){
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
		$this->assertFalse(empty($itemTwo->id),'ID of 2.1 element is empty!');
		//Second.Second Element
		$itemTwoTwo = new TestRTreeItem();
		$itemTwoTwo->group = 1;
		$itemTwoTwo->parent = $itemTwo->id;
		$itemTwoTwo->name='Element 2.2';
		$itemTwoTwo->saveToDB();
		$this->assertFalse(empty($itemTwoTwo->id),'ID of 2.2 element is empty!');
		//Third Element
		$itemThree = new TestRTreeItem();
		$itemThree->group = 1;
		$itemThree->name='Element 3.1';
		$itemThree->saveToDB();
		$this->assertFalse(empty($itemThree->id),'ID of 3.1 element is empty!');

		//
		$this->assertTrue(($itemOne->lft == 2),'[1.1]->lft should be 2');
		$this->assertTrue(($itemOne->rgt == 3),'[1.1]->rgt should be 3');
		$this->assertTrue(($itemOne->level == 2),'[1.1]->level should be 2');
		//
		$this->assertTrue(($itemTwo->lft == 4),'[2.1]->lft should be 4 (except '.$itemTwo->lft.')');
		$this->assertTrue(($itemTwo->rgt == 7),'[2.1]->rgt should be 7 (except '.$itemTwo->rgt.')');
		$this->assertTrue(($itemTwo->level == 2),'[2.1]->level should be 2');
		//
		$this->assertTrue(($itemTwoTwo->lft == 5),'[2.2]->lft should be 5');
		$this->assertTrue(($itemTwoTwo->rgt == 6),'[2.2]->rgt should be 6');
		$this->assertTrue(($itemTwoTwo->level == 2),'[2.2]->level should be 2');
		//
		$this->assertTrue(($itemThree->lft == 8),'[2.1]->lft should be 8');
		$this->assertTrue(($itemThree->rgt == 9),'[2.1]->rgt should be 9');
		$this->assertTrue(($itemThree->level == 2),'[2.1]->level should be 2');
		}

	}

