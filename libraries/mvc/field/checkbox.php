<?php
/**
 * Base component field class to check item (on/off)
 * 
 * @author Andrii Biriev <a@konservs.com>
 * @author Andrii Karepin <karepinandrei@gmail.com>
 * @author Yuriy Galin <ygalin21@gmail.com>
 * @copyright © 2014 Brilliant IT corporation, www.it.brilliant.ua
 */
bimport('mvc.field');
class BControllerField_checkbox extends BControllerField{
	public $items=array();
	/**
	 * Generate html input
	 * 
	 * @return string HTML formated string
	 */
	public function adminhtml(){
		echo('<input type="checkbox" name="'.$this->getid($this->id).'"'.($this->value?' checked':'').'/>');
		}
	/**
	 * Init control with start value
	 */
	public function initialize($val){
		$this->value=$val;
		}

	}
