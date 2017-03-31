<?php
//============================================================
// Sets of functions and classes to work with SoftModules
// group.
//
//
// Author: Andrii Biriev
//============================================================
class BSoftModulesGroup{
	public $id;
	public $alias;
	/**
	 * Load soft modules group from database / cache object.
	 */
	public function load($l){
		$this->id=(int)$l['id'];
		$this->alias=$l['alias'];
		$this->modulescount=(int)$l['modulescount'];
		}
	//===================================================
	//
	//===================================================
	public function db_update(){
		if(!$db=BFactory::getDBO()){
			return false;
			}

		}
	//===================================================
	//
	//===================================================
	public function db_insert(){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='INSERT INTO `soft_modules_alias` (alias) VALUES ('.$db->escape_string($this->alias).')';
		if(!$db->query($qr)){
			return false;
			}
		$this->id=$db->insertId();
		return true;
		}
	//===================================================
	//
	//===================================================
	public function saveToDB(){
		//Validate...
		if(empty($this->alias)){
			return false;
			}
		//Save to db...
		if(empty($this->id)){
			return $this->db_insert();
			}
		return $this->db_update();
		}
	//===================================================
	//
	//===================================================
	public function getModules(){
		$modules=array();
		$db=BFactory::getDBO();
		if(empty($db)){
			return $modules; //False?
			}
		$qr='SELECT * from `soft_modules_rules` where pageid='.$this->id;
		$q=$db->Query($qr);
		if(empty($q)){
			return $modules; //False?
			}
		while($l=$db->fetch($q)){
			$id=(int)$l['id'];
			$modules[$id]=new BSoftModule();
			$modules[$id]->group=$this;
			$modules[$id]->load($l);
			}
		return $modules;
		}
	}
