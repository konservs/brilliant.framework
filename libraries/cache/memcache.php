<?php
//============================================================
// Sets of functions and classes to work with cache as
// MemCache
//
// Author: Andrii Biriev, b@brilliant.ua
// Copyright © Brilliant IT corporation, www.it.brilliant.ua
//============================================================
if(DEBUG_MODE){
	bimport('debug.general');
	}
class BCacheMemcache extends BCache{
	protected $memcache=NULL;
	protected $memcache_ver='';
	//================================================================================
	// Returns the global Cache object, creating only if it doesn't already exist.
	//================================================================================
	public function getMemcache(){
		if (!is_object($this->memcache)){
			if(!class_exists('Memcache')){
				if(DEBUG_MODE){
					BDebug::error('Memcache class not found!');
					}
				return NULL;
				}
			$this->memcache=new Memcache();
			$mc=$this->memcache;
			$mc->connect("127.0.0.1",11211);
			$this->memcache_ver=$mc->getVersion();
			if(empty($this->memcache_ver)){
				unset($this->memcache);
				$this->memcache=NULL;
				}
			}
		return $this->memcache;
		}
	//================================================================================
	// Get the data from cache...
	//================================================================================
	public function get($key){
		$mc=$this->getMemcache();
		if(empty($mc))return NULL;
		$this->queries_get_count++;
		return $mc->get($key);
		}
	//================================================================================
	// Set the data to the cache...
	//================================================================================
	public function set($key,$value,$expired){
		$mc=$this->getMemcache();
		if(empty($mc))return false;
		$this->queries_set_count++;
		return $mc->set($key,$value,false,$expired);
		}
	//================================================================================
	// Delete the data in the cache...
	//================================================================================
	public function delete($key){
		$mc=$this->getMemcache();
		if(empty($mc))return false;
		$this->queries_delete_count++;
		return $mc->delete($key);
		}
	}
