<?php
/**
 * Base component field class to select item from the list
 * 
 * @author Andrii Biriev <a@konservs.com>
 */
bimport('mvc.field');
class BControllerField_list extends BControllerField{
	public $items=array();
	/**
	 * Generate html input
	 * 
	 * @return string HTML formated string
	 */
	public function adminhtml(){
		echo('<select class="form-control" name="'.$this->getid($this->id).'">');
		foreach($this->items as $k=>$v){
			echo('<option value="'.$k.'"'.($this->value==$k?' selected':'').'>'.$v.'</option>');
			}                               
		echo('</select>');
		}
	/**
	 * Init control with start value
	 */
	public function initialize($val){
		$this->value=$val;
		}
	/**
	 * Init control with start value
	 */
	public function prepare(){
		if(is_array($this->params['items'])){
			$this->items=array();
			foreach($this->params['items'] as $k=>$v){
				$this->items[$k]=$v;
				}
			}
		}

	}
