<?php
/**
 * General abstract class for config field
 * 
 * @author Andrii Biriev
 */
class BConfigField{
	public $value=NULL;//re-defined value
	public $alias;
	public $name;
	public $default;
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param untypes $default default value
	 */
	public function __construct($alias='',$name='',$default=NULL){
		//parent::__construct();
		$this->name=$name;
		$this->alias=$alias;
		$this->default=$default;
		}
	/**
	 * Get field value.
	 * Check, if the variable is defined and return value,
	 * or return default data, if field not defined
	 * 
	 * @return mixed data
	 */
	public function getVal(){
		//Check for re-defined value (for example, by POST query)
		if(isset($this->value)){
			return $this->value;
			}
		//Check for define
		if(defined($this->alias)){
			return constant($this->alias);
			}
		//If nothing found - return default value
		return $this->default;
		}
	/**
	 * Print bootstrap HTML data
	 * 
	 * @return string HTML data
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_input($this->name,$this->alias,$this->getVal(),'');
		}
	/**
	 * Get config strings.
	 * 
	 * @return string config file strings.
	 */
	public function getcfg(){
		$str='//'.$this->name.PHP_EOL;
		$str.='define(\''.$this->alias.'\',\''.addslashes($this->getVal()).'\');'.PHP_EOL;
		return $str;
		}
	}
