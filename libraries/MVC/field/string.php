<?php
/**
 * Component field class to edit simple string.
 * 
 * @author Andrii Biriev <a@konservs.com>
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
