<?php
/**
 * Component to work with users
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Controller_users extends \Brilliant\MVC\BController{
	/**
	 *
	 */
	public function run($segments){
		switch($segments['view']){
			//Additional rules
			//case 'blog':
			//	$model=$this->LoadModel('category');
			//	$view=$this->LoadView('blog');
			//	return($view->generate($model->getData($segments)));
			//	break;
			default:
				$model=$this->LoadModel($segments['view']);
				if(empty($model))
					return 'Users: could not load model!';
				$view=$this->LoadView($segments['view']);
				if(empty($view))
					return 'Users: could not load view!';
				return($view->generate($model->getData($segments)));
				break;
			}
		return 'Users: unknown params';
		}
	}
