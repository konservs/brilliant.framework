<?php
/**
 * Configuration library class
 * 
 * @author Andrii Biriev
 */
class BConfig{
	protected static $instance=NULL;
	public $categories=array();
	/**
	 * Singleton
	 * 
	 * @return \BConfig|NULL created object
	 */
	public static function getInstance(){
		if (!is_object(self::$instance)){
			self::$instance=new BConfig();
			}
		return self::$instance;
		}
	/**
	 * Register category
	 * 
	 * @return boolean result
	 */
	public function registerCategory($classname){
		if(!class_exists($classname)){
			return false;
			}
		$obj=new $classname();
		if(empty($obj->alias)){
			return false;
			}
		$this->categories[$obj->alias]=$obj;
		return true;
		}
	/**
	 * Get all fields objects
	 * 
	 * @return array array of fields objects
	 */
	public function getallfields(){
		$res=array();
		foreach($this->categories as $cat){
			foreach($cat->groups as $group){
				foreach($group->fields as $fld){
					$res[$fld->alias]=$fld;
					}
				}
			}
		return $res;
		}
	/**
	 * Get field value by alias.
	 * 
	 * @return mixed data
	 */
	public function getVal($alias,$default=0){
		//Check for define
		if(defined($alias)){
			return constant($alias);
			}
		//If nothing found - return default value
		return $default;
		}
	/**
	 * Save configuration file
	 * 
	 * @return boolean result
	 */
	public function saveconfig(){
		$fields=$this->getallfields();
		foreach($fields as $k=>&$v){
			$v->value=BRequest::getVar($k,$v->default);
			}
		$cfgfile='<?php'.PHP_EOL;
		foreach($fields as $fld){
			$cfgfile.=$fld->getcfg();
			}
		$fn_config=BROOTPATH.'config'.DIRECTORY_SEPARATOR.'config.php';
		return file_put_contents($fn_config,$cfgfile);
		}
	}

