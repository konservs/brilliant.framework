<?php
/**
 * Base component field class for divider.
 * 
 * @author Andrii Biriev <a@konservs.com>
 * @author Andrii Karepin <karepinandrei@gmail.com>
 * @author Yuriy Galin <ygalin21@gmail.com>
 * @copyright © 2014 Brilliant IT corporation, www.it.brilliant.ua
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
