<?php
/**
 * Integer field class
 * 
 * @author Andrii Biriev
 */
class BConfigFieldInt extends BConfigField{
	public $min;
	public $max;
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param int $default default value
	 * @param int $min min value
	 * @param int $max max value
	 */
	public function __construct($alias='',$name='',$default=0,$min=NULL,$max=NULL){
		parent::__construct($alias,$name,$default);
		$this->min=$min;
		$this->max=$max;
		}
	/**
	 * Get config strings.
	 * 
	 * @return string config file strings.
	 */
	public function getcfg(){
		$str='//'.$this->name.PHP_EOL;
		$str.='define(\''.$this->alias.'\','.(int)$this->getVal().');'.PHP_EOL;
		return $str;
		}
	}
