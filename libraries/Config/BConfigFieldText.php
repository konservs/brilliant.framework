<?php

/**
 * Full-text field (for meta description, for example)
 * 
 * @author Andrii Biriev
 */
class BConfigFieldText extends BConfigField{
	/**
	 * Print bootstrap HTML data
	 * 
	 * @return string HTML data
	 */
	public function printbshtml(){
		$bs=BBoostrapHelper::getInstance();
		return $bs->formgroup_textarea($this->name,$this->alias,$this->getVal(),'');
		}
	}

