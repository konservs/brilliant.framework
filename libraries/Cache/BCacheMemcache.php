<?php
//============================================================
// Sets of functions and classes to work with cache as
// MemCache
//
// Author: Andrii Biriev, b@brilliant.ua
//============================================================
namespace Brilliant\Cache;

use Brilliant\Log\BLog;

class BCacheMemcache extends \Brilliant\Cache\BCache{
	protected $memcache=NULL;
	protected $memcache_ver='';
	//================================================================================
	// Returns the global Cache object, creating only if it doesn't already exist.
	//================================================================================
	public function getMemcache(){
		if (!is_object($this->memcache)){
			if(!class_exists('Memcache')){
				if(DEBUG_MODE){
					BLog::addToLog('Memcache class not found!',LL_ERROR);
					}
				return NULL;
				}
			$this->memcache=new \Memcache();
			$mc=$this->memcache;
			$host = '127.0.0.1';
			if(defined('BCACHE_MEMCACHE_HOST')){
				$host = BCACHE_MEMCACHE_HOST;
			}
			$port = 11211;
			if(defined('BCACHE_MEMCACHE_PORT')){
				$port = BCACHE_MEMCACHE_PORT;
			}
			$mc->connect($host, $port);

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
