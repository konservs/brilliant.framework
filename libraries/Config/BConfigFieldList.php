<?php
/**
 * List field (for example, cache type)
 * 
 * @author Andrii Biriev
 */
class BConfigFieldList extends BConfigField{
	public $values=array();
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param array $values
	 * @param string $default default value
	 */
	public function __construct($alias='',$name='',$values=array(),$default=''){
		parent::__construct($alias,$name,$default);
		$this->values=$values;
		}
	/**
	 * Print 
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_select($this->name,$this->alias,$this->values,$this->getVal());
		}
	}
