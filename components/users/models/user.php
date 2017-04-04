<?php
/**
 * User page
 *
 * @author Andrii Biriev <a@konservs.com>
 * @copyright © Andrii Biriev, a@konservs.com, www.konservs.com
 */
defined('BEXEC') or die('No direct access!');

class Model_users_user extends \Brilliant\mvc\BModel{
	public function getData($segments){
		$id=isset($segments['id'])?(int)$segments['id']:0;
		if($id<=0)return NULL;

		$users=BUsers::getInstance();
		$data=new stdClass;
		$data->user=$users->get_single_user($id);
		return $data;
		}
	}
