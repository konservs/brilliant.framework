<?php
/**
 * Password field class
 * 
 * @author Andrii Biriev
 */
class BConfigFieldPassword extends BConfigField{
	/**
	 * Print bootstrap HTML data
	 * 
	 * @return string HTML data
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_password($this->name,$this->alias,$this->getVal(),'');
		}
	}

