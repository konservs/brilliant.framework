<?php
//============================================================
// Sets of functions and classes to work with 
//
//
// Author: Andrii Biriev
//============================================================
bimport('softmodules.single');
bimport('softmodules.group');
//============================================================
// General class
//============================================================
class BSoftModules{
	protected static $instance=NULL;
	protected $groups=array();
	//====================================================
	//
	//====================================================
	public static function getInstance(){
		if (!is_object(self::$instance))
			self::$instance=new BSoftModules();
		return self::$instance;
		}
	//====================================================
	// Get site structure from router, update necessary
        // fields (group IDs) and create db records, if
        // necessary.
	//====================================================
	public function get_tree(){
		if(DEBUG_MODE){
			BLog::addtolog('[SoftModules]: get_tree()');
			}
		$brouter=BRouter::getInstance();
		$pages=$brouter->getsoftmodules();
		if(DEBUG_MODE){
			BLog::addtolog('[SoftModules]: Pages: '.var_export($pages,true));
			}
		$this->get_tree_node($pages);
		return $pages;
		}
	//====================================================
	//
	//====================================================
	public function get_tree_node(&$itms){
		if(DEBUG_MODE){
			BLog::addtolog('[SoftModules]: get_tree_node()');
			}
		foreach($itms as &$itm){
			if($itm->active){
				$group=$this->get_group_byalias($itm->alias);
				if(empty($group)&&(!empty($itm->alias))){
					$group=new BSoftModulesGroup;
					$group->alias=$itm->alias;
					$group->saveToDB();
					}
				if(isset($group))
					$itm->id=$group->id;else
					$itm->id=0;
				}
			//Children 
			if($itm->subalias){
				$subgroup=$this->get_group_byalias($itm->subalias);
				if(empty($subgroup)&&(!empty($itm->subalias))){
					$subgroup=new BSoftModulesGroup;
					$subgroup->alias=$itm->subalias;
					$subgroup->saveToDB();
					}
				if(isset($group))
					$itm->subid=$subgroup->id;else
					$itm->subid=0;
				}
			if(!empty($itm->children))
				$this->get_tree_node($itm->children);
			}
		return true;
		}
	/**
	 *
	 *
	 */
	public function get_group_byid($id){
		//Trying to get Soft Modules groups from internal cache
		if(isset($this->groups[$id]))
			return $this->groups[$id];
		//Trying to get Soft Modules groups from external cache...
		if(CACHE_TYPE){
			bimport('cache.general');
			$bcache=BCache::getInstance();
			$data=$bcache->get('softmodules:groups:id:'.$id);
			if($data!==false){
				$group=new BSoftModulesGroup();
				$group->load($data);
				return $group;
				}
			}
		//
		bimport('sql.mysql');		
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)){
			BLog::addtolog('BSoftModules: Could not connect to the database!',LL_ERROR);
			return NULL;
			}
		$qr='SELECT * from `soft_modules_alias` where id='.$id;
		$q=$db->Query($qr);
		if(empty($q)){
			BLog::addtolog('BSoftModules: Could not execute query!',LL_ERROR);
			return NULL;
			}
		if(!$l=$db->fetch($q)){
			//Error or not error?
			//('BSoftModules: not such id!');
			return NULL;
			}
		//
		$group=new BSoftModulesGroup();
		$group->load($l);
		if(CACHE_TYPE){
			$id=(int)$l['id'];
			$alias=$l['alias'];
			bimport('cache.general');
			$bcache=BCache::getInstance();
			$data=$bcache->set('softmodules:groups:id:'.$id,$l,3600);
			$data=$bcache->set('softmodules:groups:alias:'.$alias,$l,3600);
			$this->groups[$id]=$group;
			}
		return $group;
		}
	/**
	 *
	 *
	 */
	public function get_group_byalias($alias){
		//Trying to get Soft Modules groups from internal cache
		foreach($this->groups as $g)
			if($g->alias==$alias)
				return $g;
		//Trying to get Soft Modules groups from external cache...
		if(CACHE_TYPE){
			bimport('cache.general');
			$bcache=BCache::getInstance();
			$data=$bcache->get('softmodules:groups:alias:'.$alias);
			if($data!==false){
				$group=new BSoftModulesGroup();
				$group->load($data);
				return $group;
				}
			}
		//
		bimport('sql.mysql');		
		$db=BMySQL::getInstanceAndConnect();
		if(empty($db)){
			BLog::addtolog('BSoftModules: Could not connect to the database!',LL_ERROR);
			return NULL;
			}
		$qr='SELECT * from `soft_modules_alias` where alias='.$db->escape_string($alias);
		$q=$db->Query($qr);
		if(empty($q)){
			BLog::addtolog('BSoftModules: Could not execute query!',LL_ERROR);
			return NULL;
			}
		if(!$l=$db->fetch($q)){
			//Error or not error?
			//BLog::addtolog('BSoftModules: not such alias!',LL_ERROR);
			return NULL;
			}
		//
		$group=new BSoftModulesGroup();
		$group->load($l);
		if(CACHE_TYPE){
			$id=(int)$l['id'];
			$alias=$l['alias'];
			bimport('cache.general');
			$bcache=BCache::getInstance();
			$data=$bcache->set('softmodules:groups:id:'.$id,$l,3600);
			$data=$bcache->set('softmodules:groups:alias:'.$alias,$l,3600);
			$this->groups[$id]=$group;
			}
		return $group;
		}
	//====================================================
	// 
	//====================================================
	public function getmodule($id){
		if(!$db=BFactory::getDBO()){
			return false;
			}
		$qr='SELECT * from `soft_modules_rules` where id='.$id;
		$q=$db->Query($qr);
		if(empty($q)){
			return $modules;
			}
		while($l=$db->fetch($q)){
			$id=(int)$l['id'];
			$module=new BSoftModule();
			$module->load($l);
			}
		return $module;
		}
	//====================================================
	// Get soft modules list
	//====================================================
	public function get_frompageid($id){
		$group=$this->get_group_byid($id);
		return $group->getModules();
		}
	//====================================================
	//
	//====================================================
	private function build_sorter($key){
		return function ($a, $b) use ($key) {
			return $a->$key>$b->$key;
			};
		}
	public function get_group($group){
		$brouter=BRouter::getInstance();
		$pages=$brouter->getsoftmodules($group);
		$this->get_tree_node($pages);
		return $pages;

		}
	//====================================================
	// Get soft modules rules for router (com, segments &
	// position by alias
	//====================================================	
	public function get($alias){
		if($alias==''){
			return array();
			}
		$id=false;
		$external_cache=NULL;
		bimport('http.useragent');
		$suffix=BBrowserUseragent::getDeviceSuffix();
		if(CACHE_TYPE){
			bimport('cache.general');
			$bcache=BCache::getInstance();
			$rules=$bcache->get('softmodules:rules:'.$alias.$suffix);
			}
		if(($rules===false)||($rules===NULL)){
			$group=$this->get_group_byalias($alias);
			if(empty($group)){
				//Create group???
				return array();
				}
			$modules=$group->getModules();
			$rules=array();
			if(($suffix=='.d')||($suffix=='.t')){
				usort($modules,$this->build_sorter('ordering_desktop'));
				//sort modules
				foreach($modules as $mod)
					if(($mod->enable_desktop==1))
						$rules[]=(object)array(

							'com'=>$mod->com,
							'segments'=>$mod->segments,
							'position'=>$mod->position_desktop,
							);
				}
			if($suffix=='.m'){
				usort($modules,$this->build_sorter('ordering_mobile'));
				//sort modules
				foreach($modules as $mod)
					if(($mod->enable_mobile==1))
						$rules[]=(object)array(
							'com'=>$mod->com,
							'segments'=>$mod->segments,
							'position'=>$mod->position_mobile,
							);
				}
			$bcache->set('softmodules:rules:'.$alias.$suffix,$rules,9999);
			}
		return $rules;
		}//end of get
	public function getmodulescounts_forgroups($groupids){
		if(!$db=BFactory::getDBO()){
			return array();
			}
		$qr='select count(pageid)as cnt,pageid from soft_modules_rules ';
		$qr.=' where pageid in ('.implode(',',$groupids).')';
		$qr.='group by pageid';
		$q=$db->Query($qr);
		$counts=array();
		while($l=$db->fetch($q)){
			$counts[$l['pageid']]=$l['cnt'];
			}
		return $counts;
		}
	}//end of BSoftModules
