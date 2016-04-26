<?php
/**
 * Component to work with users
 * 
 * @author Andrii Biriev
 * 
 * @copyright © Andrii Biriev, <a@konservs.com>
 */
defined('BEXEC') or die('No direct access!');

bimport('mvc.component');

class Controller_users extends BController{
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
					return 'Users: could not load model!';
					}
				$view=$this->LoadView($segments['view']);
				if(empty($view)){
					return 'Users: could not load view!';
					}
				return($view->generate($model->get_data($segments)));
				//break;
			}
		return 'Users: unknown params';
		}
	}
