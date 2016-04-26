<?php
/**
 * Component field class to select the image.
 * 
 * @author Andrii Biriev <a@konservs.com>
 * @author Andrii Karepin <karepinandrei@gmail.com>
 * @author Yuriy Galin <ygalin21@gmail.com>
 * @copyright © 2014 Brilliant IT corporation, www.it.brilliant.ua
 */
bimport('mvc.field');
bimport('html.bootstrap-helper');
class BControllerField_image extends BControllerField{
	/**
	 * Generate html input
	 * 
	 * @return string HTML formated string
	 */
	public function adminhtml(){
		$bsh=BBoostrapHelper::getInstance();
		return $bsh->input_img(0,$this->getid($this->id),$this->value);
		}
	/**
	 * Init control with start value
	 */
	public function initialize($val){
		$this->value=$val;
		}

	}
