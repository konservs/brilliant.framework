<?php
/**
 * Folder selector class
 * 
 * @author Andrii Biriev
 */
class BConfigFieldPath extends BConfigField{
	/**
	 * Create object and fill fields
	 * 
	 * @param string $alias alias of field. Storing in the database.
	 * @param string $name name of the field. Showing into control panel
	 * @param string $default default value
	 */
	public function __construct($alias='',$name='',$default=''){
		parent::__construct($alias,$name,$default);
		}
	/**
	 * Print Bootstrap HTML
	 * 
	 * @return string HYML string
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_input($this->name,$this->alias,$this->getVal(),'');
		}
	}
	
