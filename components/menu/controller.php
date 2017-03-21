<?php
/**
 * Menu component
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Controller_menu extends \Brilliant\mvc\BController{
	public function run($segments){
		switch($segments['view']){
			//Additional rules
			//case '...':
			//	$model=$this->LoadModel('...');
			//	$view=$this->LoadView('...');
			//	return($view->generate($model->get_data($segments)));
			//	break;
			default:
				$model=$this->LoadModel($segments['view']);
				if(empty($model))
					return 'Menu: could not load model!';
				$view=$this->LoadView($segments['view']);
				if(empty($view))
					return 'Menu: could not load view!';
				return($view->generate($model->get_data($segments)));
				break;
			}
		return 'Menu: unknown params';
		}
	}
