<?php
/**
 * Component field class to edit simple string.
 * 
 * @author Andrii Biriev <a@konservs.com>
 * @author Andrii Karepin <karepinandrei@gmail.com>
 * @author Yuriy Galin <ygalin21@gmail.com>
 * @copyright © 2014 Brilliant IT corporation, www.it.brilliant.ua
 */
bimport('mvc.field');
class BControllerField_string extends BControllerField{
	/**
	 * Generate html input
	 * 
	 * @return string HTML formated string
	 */
	public function adminhtml(){
		return '<input class="form-control" type="text" name="'.$this->getid($this->id).'" value="'.htmlspecialchars($this->value).'">';
		}
	/**
	 * Init control with start value
	 */
	public function initialize($val){
		$this->value=$val;
		}

	}
