<?php
/**
 * Base component field class for divider.
 * 
 * @author Andrii Biriev <a@konservs.com>
 */
bimport('mvc.field');
class BControllerField_divider extends BControllerField{
	public $items=array();
	/**
	 * Generate html input
	 * 
	 * @return string HTML formated string
	 */
	public function adminhtml(){
		echo('<hr class="divider"/>');
		}
	/**
	 * Init control with start value
	 */
	public function initialize($val){
		$this->value=$val;
		}

	}
