<?php
//============================================================
// Sets of functions and classes to work with single
// SoftModule
//
//
// Author: Andrii Biriev
// Author: Andrii Karepin
// Copyright © Brilliant IT corporation, www.it.brilliant.ua
//============================================================

class BSoftModule{
	public $group=NULL;
	//===================================================
	//
	//===================================================
	public function load($l){
		$this->id=$l['id'];
		$this->pageid=$l['pageid'];
		$this->name=$l['name'];
		$this->com=$l['com'];
		$this->position=$l['position'];
		$this->position_desktop=$l['position_desktop'];
		$this->position_mobile=$l['position_mobile'];
		$this->segments=json_decode($l['segments'],true);

		$this->enable_desktop=(int)$l['enable_desktop'];
		$this->enable_mobile=$l['enable_mobile'];
		$this->enable_children_desktop=$l['enable_children_desktop'];
		$this->enable_children_mobile=$l['enable_children_mobile'];
		$this->ordering_desktop=(int)$l['ordering_desktop'];
		$this->ordering_mobile=(int)$l['ordering_mobile'];
		}
	//===================================================
	//
	//===================================================
	public function updatecache(){
		bimport('cache.general');
		$bcache=BCache::getInstance();
		if(empty($this->group)){
			$sm=BSoftModules::getInstance();
			$this->group=$sm->get_group_byid($this->pageid);
			}
		$bcache->delete('softmodules:rules:'.$this->group->alias.'.d');
		$bcache->delete('softmodules:rules:'.$this->group->alias.'.m');
		$key='';
		foreach($this->segments as $k=>$v){
			$key.=':'.$k.'='.$v;
			}
		$bcache->delete('url:'.$this->com.':ru.d'.$key);
		$bcache->delete('url:'.$this->com.':ru.m'.$key);
		$bcache->delete('url:'.$this->com.':ua.m'.$key);
		$bcache->delete('url:'.$this->com.':ua.d'.$key);

		//
		}
	//===================================================
	//
	//===================================================
	public function validate(){
		$r=array();
		if(empty($this->name)){
			$r['name']=1;
			}
		return $r;
		}
	
	public function savetodb($validate=true){
		if($validate)
			$err=$this->validate();
		if(!empty($err)){
			return $err;
			}
		bimport('sql.mysql');
		$db=BMySQL::getInstanceAndConnect();
		if(!empty($this->id)){
			$qr='update soft_modules_rules set';
			$qr.=' pageid='.$this->pageid;
			$qr.=',name='.$db->escape_string($this->name);
			$qr.=',com='.$db->escape_string($this->com);
			$qr.=',segments='.$db->escape_string(json_encode($this->segments));
			$qr.=',position_desktop='.$db->escape_string($this->position_desktop);
			$qr.=',position_mobile='.$db->escape_string($this->position_mobile);
			$qr.=',enable_desktop='.$this->enable_desktop;
			$qr.=',enable_mobile='.$this->enable_mobile;
			if(isset($this->enable_children_desktop))
				$qr.=',enable_children_desktop='.(int)$this->enable_children_desktop;
			if(isset($this->enable_children_mobile))
				$qr.=',enable_children_mobile='.(int)$this->enable_children_mobile;
			if(isset($this->ordering_desktop))
				$qr.=',ordering_desktop='.$this->ordering_desktop;
			if(isset($this->ordering_mobile))
				$qr.=',ordering_mobile='.$this->ordering_mobile;
			$qr.=' where id='.$this->id;
			$q=$db->Query($qr);
			}else{
			$qr.='insert into soft_modules_rules (com,segments,pageid,enable_mobile,enable_desktop,position_desktop,position_mobile,ordering_desktop,ordering_mobile) values('.
			$db->escape_string($this->com).','.
			$db->escape_string(json_encode($this->segments)).','.
			$this->pageid.','.
			(!empty($this->position_mobile)?'1':'0').','.
			(!empty($this->position_desktop)?'1':'0').','.
			$db->escape_string($this->position_desktop).','.
			$db->escape_string($this->position_mobile).','.
			'-1,-1'.')';
			
			$q=$db->Query($qr);
			$this->id=$db->insert_id();
			}
		$this->updatecache();
		return $this;
		}
	public function delete(){
		bimport('sql.mysql');
		$db=BMySQL::getInstanceAndConnect();
		$qr='delete from `soft_modules_rules` where id='.$this->id;
		$q=$db->Query($qr);
		$this->updatecache();
		}
	}

