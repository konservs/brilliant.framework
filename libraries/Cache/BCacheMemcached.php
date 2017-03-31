<?php
//============================================================
// Sets of functions and classes to work with cache as
// MemCached
//
// Author: Andrii Biriev, b@brilliant.ua
//============================================================
namespace Brilliant\cache;
use Brilliant\log\BLog;

class BCacheMemcached extends \Brilliant\cache\BCache{
	protected $memcached=NULL;
	protected $memcached_ver='';
	//================================================================================
	// Returns the global Cache object, creating only if it doesn't already exist.
	//================================================================================
	public function getMemcached(){
		if(!is_object($this->memcached)){
			if(!class_exists('Memcached')){
				if(DEBUG_MODE){
					BLog::addToLog('Memcached class not found!',LL_ERROR);
					}
				return NULL;
				}
			if(DEBUG_MODE){
				BDebug::message('Memcached]: Connecting to memcached server...');
				}
			$this->memcached=new Memcached();
			$mc=$this->memcached;
			$mc->addServer("127.0.0.1",11211);
			$mc->setOption(Memcached::OPT_BINARY_PROTOCOL,true);
			$this->memcached_ver=$mc->getVersion();
			if(DEBUG_MODE){
				BDebug::message('[Memcached]: ver='.var_export($this->memcached_ver,true));
				}
			}
		return $this->memcached;
		}
	//================================================================================
	// Get the data from cache...
	//================================================================================
	public function get($key){
		if(DEBUG_MODE){
			BDebug::message('[Memcached]: Get key('.$key.')');
			}
		$mc=$this->getMemcached();
		if(empty($mc))return NULL;
		$this->queries_get_count++;
		return $mc->get($key);
		}
	//================================================================================
	// Get array of the data from cache by array of keys...
	//================================================================================
	public function mget($keys){
		if(DEBUG_MODE){
			BDebug::message('[Memcached]: Mget keys('.implode(' | ',$keys).')');
			}
		$mc=$this->getMemcached();
		if(empty($mc))return NULL;
		$this->queries_mget_count++;
		return $mc->getMulti($keys);
		}
	//================================================================================
	// Set the data to the cache...
	//================================================================================
	public function set($key,$value,$expired){
		if(DEBUG_MODE){
			BDebug::message('[Memcached]: Set key('.$key.')');
			}
		$mc=$this->getMemcached();
		if(empty($mc))return false;
		$this->queries_set_count++;
		return $mc->set($key,$value,$expired);
		}
	//================================================================================
	// Multi set the data to the cache...
	//================================================================================
	public function mset($values,$expired){
		if(DEBUG_MODE){
			BDebug::message('[Memcached]: MSet'.var_export($values,true));
			}
		$mc=$this->getMemcached();
		if(empty($mc))return false;
		$this->queries_mset_count++;
		return $mc->setMulti($values,$expired);
		}
	//================================================================================
	// Delete the data in the cache...
	//================================================================================
	public function delete($key){
		if(DEBUG_MODE){
			BDebug::message('[Memcached]: Delete key('.$key.')');
			}
		$mc=$this->getMemcached();
		if(empty($mc))return false;
		$this->queries_delete_count++;
		return $mc->delete($key);
		}
	//================================================================================
	// Multi delete
	//================================================================================
	public function mdelete($keys){
		/*if(DEBUG_MODE){
			BDebug::message('[Memcached]: MSet'.var_export($values,true));
			}
		$mc=$this->getMemcached();
		if(empty($mc)){
			return false;
			}
		//$this->queries_mset_count++;
		return $mc->deleteMulti($keys);*/
		}
	//================================================================================
	// Clear all data.
	//================================================================================
	public function invalidate(){
		if(DEBUG_MODE){
			BDebug::message('[Memcached]: Delete key('.$key.')');
			}
		$mc=$this->getMemcached();
		if(empty($mc)){
			return false;
			}
		$mc->flush();
		}
	}
