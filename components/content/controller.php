<?php
/**
 * Component to work with content - articles, categories, etc.
 * 
 * @author Andrii Biriev
 * 
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
defined('BEXEC') or die('No direct access!');

bimport('mvc.component');

class Controller_content extends BController{
	/**
	 *
	 */
	public function run($segments){
		switch($segments['view']){
			//Additional rules
			//case '...':
			default:
				$model=$this->LoadModel($segments['view']);
				if(empty($model)){
					return 'Content: could not load model!';
					}
				$view=$this->LoadView($segments['view']);
				if(empty($view)){
					return 'Content: could not load view!';
					}
				return($view->generate($model->get_data($segments)));
				//break;
			}
		return 'Content: unknown params';
		}
	}
